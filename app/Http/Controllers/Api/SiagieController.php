<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Seccion;
use App\Models\Periodo;
use App\Services\SiagieLogService;

class SiagieController extends Controller
{
    /**
     * Importa plantilla SIAGIE (Excel/CSV). Usa mapeo configurable (config/siagie.php),
     * valida reglas CNEB por nivel/grado e importa dentro de una transacción.
     * El nombre del archivo NO debe modificarse (criterio acta 2.0).
     */
    public function import(Request $request, SiagieLogService $logs): JsonResponse
    {
        $inicio = microtime(true);

        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv,txt|max:'
                . config('siagie.validaciones.max_tamano_archivo_kb'),
            'periodo_id' => 'required|exists:periodos,id',
            'seccion_id' => 'required|exists:secciones,id',
        ]);

        $file = $request->file('archivo');
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());

        // 1. VALIDAR nombre del archivo (si está activado en config)
        $mapper = app(\App\Services\Siagie\SiagieColumnMapper::class);
        if (! $mapper->validarNombreArchivo($originalName)) {
            return response()->json([
                'message' => 'El nombre del archivo no cumple el formato SIAGIE.',
                'esperado' => config('siagie.nombre_archivo.patron'),
                'recibido' => $originalName,
            ], 422);
        }

        $path = $file->storeAs(
            'siagie/imports/'.$request->periodo_id.'/'.$request->seccion_id,
            $originalName
        );

        // 2. LEER filas del archivo
        [$header, $rows] = $this->leerArchivo($file, $ext);
        if (empty($header)) {
            return response()->json([
                'message' => 'Archivo vacío o sin encabezados.',
            ], 422);
        }

        // 3. OBTENER reglas CNEB para esta sección
        $seccion = \App\Models\Seccion::with('grado.nivel')->findOrFail($request->seccion_id);
        $reglasCneb = app(\App\Services\Siagie\SiagieReglasCneb::class);
        $reglas = $reglasCneb->reglasParaSeccion($seccion);

        // 4. IMPORTAR en TRANSACCIÓN
        $imported = 0;
        $errors = [];
        $filasMapeadas = [];

        // Pre-mapear todas las filas para poder validar antes de insertar
        foreach ($rows as $idx => $row) {
            $mapeado = $mapper->mapearFila($header, array_values($row));
            $mapeado['_fila'] = $idx + 2; // +2 por header + index 0
            $filasMapeadas[] = $mapeado;
        }

        // Si la transacción hace rollback total, se captura el mensaje para
        // poder registrar el log del intento fallido (RF-29) en vez de un 500.
        try {
            DB::transaction(function () use ($filasMapeadas, $reglas, $reglasCneb, $request, &$imported, &$errors) {
                foreach ($filasMapeadas as $fila) {
                    $numFila = $fila['_fila'];
                    try {
                        // Validar CNEB ANTES de insertar
                        $erroresCneb = $reglasCneb->validarNota($fila, $reglas);
                        if (! empty($erroresCneb)) {
                            throw new \Exception(implode('. ', $erroresCneb));
                        }

                        $dni = $fila['dni'] ?? $fila['codigo_estudiante'] ?? null;
                        if (empty($dni)) {
                            throw new \Exception('DNI o código de estudiante requerido');
                        }

                        $est = \App\Models\Estudiante::where('dni', $dni)
                            ->orWhere('codigo_estudiante', $dni)
                            ->first();
                        if (! $est) {
                            throw new \Exception("Estudiante no encontrado: {$dni}");
                        }

                        $mat = \App\Models\Matricula::where('estudiante_id', $est->id)
                            ->where('seccion_id', $request->seccion_id)
                            ->where('periodo_id', $request->periodo_id)
                            ->first();
                        if (! $mat) {
                            throw new \Exception("Matrícula no encontrada para {$dni}");
                        }

                        $nivel = strtoupper(trim((string) ($fila['nivel_logro'] ?? '')));
                        $nota = $fila['nota'] ?? null;

                        // Buscar bimestre activo si no viene
                        $bimestreId = $fila['bimestre_id'] ??
                            \App\Models\Bimestre::where('periodo_id', $request->periodo_id)
                                ->where('activo', true)
                                ->value('id');

                        $payload = [
                            'matricula_id' => $mat->id,
                            'area_id' => (int) ($fila['area_id'] ?? 1),
                            'tipo_evaluacion_id' => (int) ($fila['tipo_evaluacion_id'] ?? 1),
                            'bimestre_id' => $bimestreId,
                            'nivel_logro' => $nivel ?: null,
                            'escala' => $nivel ? 'literal' : 'vigesimal',
                            'nota' => ($nivel ? null : ($nota !== '' && $nota !== null ? (float) $nota : null)),
                            'es_nota_c' => $nivel === 'C',
                            'motivo_nota_c' => $fila['motivo_c'] ?? null,
                        ];

                        app(\App\Services\CalificacionService::class)->create($payload);
                        $imported++;
                    } catch (\Exception $e) {
                        $errors[] = 'Línea '.$numFila.': '.$e->getMessage();
                    }
                }

                // Si TODAS las filas fallaron, rollback
                if ($imported === 0 && count($errors) > 0) {
                    throw new \Exception('Importación fallida: '.count($errors).' errores. Sin cambios aplicados.');
                }
            });
        } catch (\Exception $e) {
            $errors[] = $e->getMessage();
        }

        // 5. REGISTRAR LOG SIAGIE (RF-29, incluso si todo falló)
        $duracion = (int) ((microtime(true) - $inicio) * 1000);
        $logs->registrar([
            'operacion' => 'import',
            'archivo' => $originalName,
            'formato' => $ext,
            'periodo_id' => (int) $request->periodo_id,
            'seccion_id' => (int) $request->seccion_id,
            'filas_procesadas' => count($filasMapeadas),
            'filas_exitosas' => $imported,
            'filas_con_error' => count($errors),
            'errores' => $errors ?: null,
            'duracion_ms' => $duracion,
        ]);

        return response()->json([
            'message' => $imported ? 'Importación completada' : 'Sin importaciones',
            'archivo' => $originalName,
            'ruta' => $path,
            'periodo_id' => (int) $request->periodo_id,
            'seccion_id' => (int) $request->seccion_id,
            'reglas_aplicadas' => $reglas,
            'importados' => $imported,
            'errores' => $errors,
        ], 201);
    }

    /**
     * Lee el archivo Excel/CSV y retorna [header, filas].
     * El header conserva el casing original (el mapper normaliza al comparar).
     * Las filas son arrays posicionales de valores.
     */
    private function leerArchivo($file, string $ext): array
    {
        $header = [];
        $rows = [];

        if (in_array($ext, ['xlsx', 'xls'])) {
            try {
                // Solo lectura de celdas; no usa imágenes, no requiere ext-gd
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray(null, true, true, true);
                if (empty($data)) {
                    return [[], []];
                }
                $firstRow = array_shift($data);
                $header = array_values($firstRow);
                foreach ($data as $excelRow) {
                    $vals = array_values($excelRow);
                    if (count(array_filter($vals, fn ($v) => $v !== null && $v !== '')) === 0) {
                        continue;
                    }
                    $rows[] = $vals;
                }
            } catch (\Exception $e) {
                throw new \Exception('Error leyendo Excel: '.$e->getMessage());
            }
        } elseif ($ext === 'csv' || $ext === 'txt') {
            $content = file_get_contents($file->getRealPath());
            $lines = preg_split('/\r\n|\n|\r/', trim($content));
            $header = str_getcsv(array_shift($lines) ?? '');
            foreach ($lines as $line) {
                if (! trim($line)) {
                    continue;
                }
                $rows[] = str_getcsv($line);
            }
        }

        return [$header, $rows];
    }

    /**
     * Exporta calificaciones en formato SIAGIE con columnas configurables
     * (config/siagie.php → export.columnas) y registra el evento (RF-29).
     */
    public function export(Request $request, int $seccion, ?int $periodo = null, ?SiagieLogService $logs = null): \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse|\Illuminate\Http\Response
    {
        $inicio = microtime(true);
        $sec = Seccion::with('grado.nivel')->findOrFail($seccion);
        $per = $periodo ? Periodo::findOrFail($periodo) : Periodo::where('activo', true)->first();

        if (! $per) {
            return response()->json(['message' => 'Periodo no encontrado o no activo.'], 404);
        }

        $formato = $request->query('formato', 'csv');
        $mapper = app(\App\Services\Siagie\SiagieColumnMapper::class);
        $columnas = $mapper->columnasExport();

        $cals = \App\Models\Calificacion::with(['matricula.estudiante', 'area', 'tipoEvaluacion'])
            ->whereHas('matricula', fn ($q) => $q->where('seccion_id', $seccion)->where('periodo_id', $per->id))
            ->get();

        $registrarLog = function () use ($logs, $inicio, $sec, $per, $formato, $cals) {
            if (! $logs) {
                return;
            }
            $logs->registrar([
                'operacion' => 'export',
                'archivo' => 'SIAGIE_'.$sec->grado->nivel->nombre.'_'.$sec->grado->nombre.'_Sec'.$sec->id.'_Per'.$per->id,
                'formato' => $formato,
                'periodo_id' => $per->id,
                'seccion_id' => $sec->id,
                'filas_procesadas' => $cals->count(),
                'filas_exitosas' => $cals->count(),
                'filas_con_error' => 0,
                'duracion_ms' => (int) ((microtime(true) - $inicio) * 1000),
            ]);
        };

        if ($formato === 'json') {
            $registrarLog();

            return response()->json(['seccion' => $sec, 'periodo' => $per, 'calificaciones' => $cals]);
        }

        if ($formato === 'xlsx') {
            $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sh = $ss->getActiveSheet();
            $sh->setTitle('SIAGIE '.$sec->grado->nombre);

            // Header dinámico desde config
            $col = 'A';
            foreach ($columnas as $def) {
                $sh->setCellValue($col.'2', $def['header']);
                $col++;
            }
            $lastCol = chr(ord('A') + count($columnas) - 1);

            // Estilos desde config
            $estilos = config('siagie.export.estilos');
            $styleH = $sh->getStyle("A2:{$lastCol}2");
            $styleH->getFont()->setBold(true)->getColor()->setRGB($estilos['header_color']);
            $styleH->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setRGB($estilos['header_bg']);
            $styleH->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

            // Datos
            $row = 3;
            foreach ($cals as $c) {
                $col = 'A';
                foreach ($columnas as $def) {
                    $valor = $this->resolverCampo($def['campo'], $c);
                    $sh->setCellValue($col.$row, $valor);
                    if ($def['campo'] === 'calificacion.nivel_logro' && $valor === 'C') {
                        $sh->getStyle($col.$row)->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setRGB($estilos['nota_c_bg']);
                        $sh->getStyle($col.$row)->getFont()->getColor()->setRGB($estilos['nota_c_color']);
                    }
                    $col++;
                }
                $row++;
            }

            $sh->getStyle("A2:{$lastCol}".($row - 1))->getBorders()->getAllBorders()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)
                ->getColor()->setRGB($estilos['border_color']);

            foreach (range('A', $lastCol) as $col) {
                $sh->getColumnDimension($col)->setAutoSize(true);
            }
            $sh->freezePane('A3');

            $filename = 'SIAGIE_'.$sec->grado->nivel->nombre.'_'.$sec->grado->nombre
                .'_Sec'.$sec->id.'_Per'.$per->id.'_'.date('Ymd').'.xlsx';

            $registrarLog();

            return response()->streamDownload(function () use ($ss) {
                (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss))->save('php://output');
            }, $filename, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        }

        if ($formato === 'pdf') {
            $conf = \App\Models\Configuracion::where('clave', 'nombre_institucion')->first();
            $ie = $conf->valor ?? 'I.E. EGA';
            $html = '<html><head><meta charset=\"utf-8\"><style>body{font-family: DejaVu Sans, sans-serif; font-size:9px} h1{color:#1E3A8A; font-size:14px; text-align:center; margin:0} h2{color:#334155; font-size:10px; text-align:center; margin:2px 0 8px} table{width:100%; border-collapse:collapse} th{background:#1E3A8A; color:#fff; padding:4px; text-align:center} td{padding:3px; border:1px solid #CBD5E1} tr:nth-child(even){background:#F8FAFC} .c{background:#FECACA; color:#991B1B; font-weight:bold; text-align:center}</style></head><body>';
            $html .= '<h1>'.htmlspecialchars($ie).'</h1><h2>Registro Auxiliar — '.htmlspecialchars($sec->grado->nivel->nombre.' '.$sec->grado->nombre.' '.$sec->nombre).' — '.htmlspecialchars($per->nombre).' — '.date('d/m/Y').'</h2>';
            $html .= '<table><tr><th>#</th><th>DNI</th><th>Estudiante</th><th>Área</th><th>Nivel</th><th>Nota</th><th>Escala</th><th>Motivo C</th></tr>';
            $i = 1;
            foreach ($cals as $c) {
                $est = $c->matricula->estudiante;
                $cls = ($c->nivel_logro === 'C' ? ' class=\"c\"' : '');
                $html .= '<tr><td>'.$i++.'</td><td>'.htmlspecialchars($est->dni ?? '').'</td><td>'.htmlspecialchars(trim(($est->apellidos ?? '').' '.($est->nombres ?? ''))).'</td><td>'.htmlspecialchars($c->area->nombre ?? '').'</td><td'.$cls.'>'.htmlspecialchars($c->nivel_logro ?? '').'</td><td>'.htmlspecialchars((string) ($c->nota ?? '')).'</td><td>'.htmlspecialchars($c->escala ?? '').'</td><td>'.htmlspecialchars($c->motivo_nota_c ?? '').'</td></tr>';
            }
            $html .= '</table><p style=\"font-size:7px; color:#64748B; text-align:center; margin-top:10px\">SIGEL-EGA — Generado '.date('d/m/Y H:i').' — '.$ie.'</p></body></html>';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4', 'landscape');
            $filename = 'SIAGIE_'.$sec->grado->nivel->nombre.'_'.$sec->grado->nombre.'_Sec'.$sec->id.'_Per'.$per->id.'_'.date('Ymd').'.pdf';
            $registrarLog();

            return $pdf->download($filename);
        }

        // CSV (default) con columnas configurables
        $filename = 'SIAGIE_'.$sec->grado->nivel->nombre.'_'.$sec->grado->nombre
            .'_Sec'.$sec->id.'_Per'.$per->id.'_'.date('Ymd').'.csv';

        $registrarLog();

        return response()->streamDownload(function () use ($cals, $columnas) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_column($columnas, 'header'));
            foreach ($cals as $c) {
                $fila = [];
                foreach ($columnas as $def) {
                    $fila[] = $this->resolverCampo($def['campo'], $c);
                }
                fputcsv($out, $fila);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Resuelve un campo anidado tipo "matricula.estudiante.dni" en una calificación.
     * El segmento inicial "calificacion" es autorreferencia al modelo raíz y se omite.
     */
    private function resolverCampo(string $path, $calificacion)
    {
        $partes = explode('.', $path);
        if (($partes[0] ?? null) === 'calificacion') {
            array_shift($partes);
        }
        $valor = $calificacion;
        foreach ($partes as $parte) {
            if ($valor === null) {
                return '';
            }
            if (is_object($valor)) {
                $valor = $valor->{$parte} ?? null;
            } elseif (is_array($valor)) {
                $valor = $valor[$parte] ?? null;
            } else {
                return '';
            }
        }

        return $valor ?? '';
    }
}
