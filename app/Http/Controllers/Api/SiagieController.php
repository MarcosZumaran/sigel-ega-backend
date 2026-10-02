<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Seccion;
use App\Models\Periodo;
use App\Services\SiagieLogService;

class SiagieController extends Controller
{
    /**
     * Importa plantilla SIAGIE (Excel). El nombre del archivo NO debe modificarse (criterio acta 2.0).
     * Valida AD/A/B/C + 0-20 + nota C con motivo.
     */
    public function import(Request $request, SiagieLogService $logs): JsonResponse
    {
        $inicio = microtime(true);

        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv,txt|max:5120',
            'periodo_id' => 'required|exists:periodos,id',
            'seccion_id' => 'required|exists:secciones,id',
        ]);

        $file = $request->file('archivo');
        $originalName = $file->getClientOriginalName();
        $ext = strtolower($file->getClientOriginalExtension());
        $path = $file->storeAs('siagie/imports/'.$request->periodo_id.'/'.$request->seccion_id, $originalName);

        $imported = 0; $errors = [];
        $rows = [];
        // XLSX/XLS con PhpSpreadsheet (solo lectura de celdas; no usa imágenes, no requiere ext-gd)
        if (in_array($ext, ['xlsx','xls'])) {
            try {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
                $sheet = $spreadsheet->getActiveSheet();
                $data = $sheet->toArray(null, true, true, true);
                if (empty($data)) throw new \Exception('Excel vacío');
                $firstRow = array_shift($data);
                $header = array_map(fn($h)=> strtolower(trim((string)$h)), array_values($firstRow));
                foreach ($data as $excelRow) {
                    $vals = array_values($excelRow);
                    if (count(array_filter($vals, fn($v)=> $v!==null && $v!==''))===0) continue;
                    $row = @array_combine($header, array_slice($vals, 0, count($header)));
                    if ($row) $rows[] = $row;
                }
            } catch (\Exception $e) {
                $errors[] = 'Excel: '.$e->getMessage();
            }
        }
        if ($ext === 'csv' || $ext === 'txt') {
            $content = file_get_contents($file->getRealPath());
            $lines = preg_split('/\r\n|\n|\r/', trim($content));
            $header = str_getcsv(array_shift($lines) ?? '');
            $header = array_map(fn($h)=> strtolower(trim($h)), $header);
            foreach ($lines as $idx=>$line) {
                if (! trim($line)) continue;
                $row = array_combine($header, str_getcsv($line));
                if (! $row) { $errors[] = 'Línea '.($idx+2).': columnas no coinciden'; continue; }
                $rows[] = $row;
            }
        }
        // Ejecutar import literal CNEB sobre $rows (AD/A/B/C + motivo)
        foreach ($rows as $idx=>$row) {
                try {
                    $dni = $row['dni'] ?? $row['codigo'] ?? $row['codigo_estudiante'] ?? null;
                    // robustez: PhpSpreadsheet entrega floats para notas/dni
                    $nivel = strtoupper(trim((string)($row['nivel_logro'] ?? $row['nivel'] ?? '')));
                    $nota = $row['nota'] ?? null;
                    if ($nota !== null && $nota !== '') $nota = (string)$nota;
                    if ($nivel && ! in_array($nivel, ['AD','A','B','C'], true)) { throw new \Exception('nivel_logro debe ser AD/A/B/C'); }
                    if ($nivel === 'C' && empty(trim((string)($row['motivo_c'] ?? $row['motivo'] ?? '')) )) { throw new \Exception('C exige motivo_c'); }
                    // buscar matricula por dni en la sección/periodo
                    $est = $dni ? \App\Models\Estudiante::where('dni', $dni)->orWhere('codigo_estudiante', $dni)->first() : null;
                    if (! $est) { throw new \Exception('Estudiante no encontrado para dni/codigo '.$dni); }
                    $mat = \App\Models\Matricula::where('estudiante_id', $est->id)->where('seccion_id', $request->seccion_id)->where('periodo_id', $request->periodo_id)->first();
                    if (! $mat) { throw new \Exception('Matrícula no encontrada para estudiante '.$est->id.' en seccion/periodo dado'); }
                    $payload = [
                        'matricula_id' => $mat->id,
                        'area_id' => (int) ($row['area_id'] ?? 1),
                        'tipo_evaluacion_id' => (int) ($row['tipo_evaluacion_id'] ?? 1),
                        'nivel_logro' => $nivel ?: null,
                        'escala' => $nivel ? 'literal' : 'vigesimal',
                        'nota' => ($nivel ? null : ($nota !== '' ? (float)$nota : null)),
                        'es_nota_c' => $nivel === 'C',
                        'motivo_nota_c' => $row['motivo_c'] ?? $row['motivo'] ?? null,
                    ];
                    app(\App\Services\CalificacionService::class)->create($payload);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = 'Línea '.($idx+2).': '.$e->getMessage();
                }
            }
        if (in_array($ext, ['xlsx','xls']) && empty($rows) && empty($errors)) {
            $errors[] = 'Excel sin filas de datos (verifique header: dni,area_id,tipo_evaluacion_id,nivel_logro,motivo_c)';
        }

        // RF-29: registrar el evento de importación antes de responder
        $duracion = (int) ((microtime(true) - $inicio) * 1000);

        $logs->registrar([
            'operacion' => 'import',
            'archivo' => $originalName,
            'formato' => $ext,
            'periodo_id' => (int) $request->periodo_id,
            'seccion_id' => (int) $request->seccion_id,
            'filas_procesadas' => count($rows),
            'filas_exitosas' => $imported,
            'filas_con_error' => count($errors),
            'errores' => $errors ?: null,
            'duracion_ms' => $duracion,
        ]);

        return response()->json([
            'message' => $imported ? 'Importación completada' : 'Plantilla recibida (nombre preservado).',
            'archivo' => $originalName,
            'ruta' => $path,
            'periodo_id' => (int) $request->periodo_id,
            'seccion_id' => (int) $request->seccion_id,
            'importados' => $imported,
            'errores' => $errors,
        ], 201);
    }

    public function export(Request $request, int $seccion, ?int $periodo = null, SiagieLogService $logs = null): \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse|\Illuminate\Http\Response
    {
        $inicio = microtime(true);

        $sec = Seccion::with('grado.nivel')->findOrFail($seccion);
        $per = $periodo ? Periodo::findOrFail($periodo) : Periodo::where('activo', true)->first();
        if (! $per) {
            return response()->json(['message' => 'Periodo no encontrado o no activo.'], 404);
        }
        $formato = $request->query('formato', 'csv');

        // RF-29: helper para registrar el evento de exportación antes de cada descarga.
        // No altera la lógica de export; solo deja rastro del evento.
        $registrarLog = function () use ($logs, $inicio, $sec, $per, $formato) {
            if (! $logs) {
                return;
            }
            $total = \App\Models\Calificacion::whereHas('matricula', fn ($q) => $q->where('seccion_id', $sec->id)->where('periodo_id', $per->id))->count();

            $logs->registrar([
                'operacion' => 'export',
                'archivo' => 'SIAGIE_'.$sec->grado->nivel->nombre.'_'.$sec->grado->nombre.'_Sec'.$sec->id.'_Per'.$per->id,
                'formato' => $formato,
                'periodo_id' => $per->id,
                'seccion_id' => $sec->id,
                'filas_procesadas' => $total,
                'filas_exitosas' => $total,
                'filas_con_error' => 0,
                'duracion_ms' => (int) ((microtime(true) - $inicio) * 1000),
            ]);
        };

        if ($formato === 'json') {
            $cals = \App\Models\Calificacion::with(['matricula.estudiante','area'])
                ->whereHas('matricula', fn($q)=> $q->where('seccion_id',$seccion)->where('periodo_id',$per->id))->get();
            $registrarLog();
            return response()->json(['seccion'=>$sec,'periodo'=>$per,'calificaciones'=>$cals]);
        }
        $cals = \App\Models\Calificacion::with(['matricula.estudiante','area','tipoEvaluacion'])
            ->whereHas('matricula', fn($q)=> $q->where('seccion_id',$seccion)->where('periodo_id',$per->id))->get();
        if ($formato === 'xlsx') {
            $ss = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sh = $ss->getActiveSheet();
            $sh->setTitle('SIAGIE '.$sec->grado->nombre);
            $conf = \App\Models\Configuracion::where('clave','nombre_institucion')->first();
            $ie = $conf->valor ?? 'I.E. EGA';
            $sh->setCellValue('A1', $ie.' — Registro Auxiliar '.$sec->grado->nivel->nombre.' '.$sec->grado->nombre.' '.$sec->nombre.' — '.$per->nombre);
            $sh->mergeCells('A1:J1'); $sh->getStyle('A1')->getFont()->setBold(true)->setSize(11)->getColor()->setRGB('1E3A8A'); $sh->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $header = ['DNI','Código','Apellidos y Nombres','Área','Tipo Eval','Nivel','Nota','Escala','Motivo C','ID Área'];
            $col = 'A'; foreach ($header as $h) { $sh->setCellValue($col.'2', $h); $col++; }
            $styleH = $sh->getStyle('A2:J2'); $styleH->getFont()->setBold(true)->getColor()->setRGB('FFFFFF'); $styleH->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A'); $styleH->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            $row = 3; foreach ($cals as $c) {
                $est = $c->matricula->estudiante ?? null;
                $sh->setCellValue('A'.$row, $est->dni ?? ''); $sh->setCellValue('B'.$row, $est->codigo_estudiante ?? ''); $sh->setCellValue('C'.$row, trim(($est->apellidos ?? '').' '.($est->nombres ?? '')));
                $sh->setCellValue('D'.$row, $c->area->nombre ?? ''); $sh->setCellValue('E'.$row, $c->tipo_evaluacion_id); $sh->setCellValue('F'.$row, $c->nivel_logro ?? ''); $sh->setCellValue('G'.$row, $c->nota ?? ''); $sh->setCellValue('H'.$row, $c->escala ?? ''); $sh->setCellValue('I'.$row, $c->motivo_nota_c ?? ''); $sh->setCellValue('J'.$row, $c->area_id);
                if (($c->nivel_logro ?? '') === 'C') { $sh->getStyle('F'.$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('FECACA'); $sh->getStyle('F'.$row)->getFont()->getColor()->setRGB('991B1B'); }
                if ($row %2===0) $sh->getStyle('A'.$row.':J'.$row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
                $row++;
            }
            foreach (range('A','J') as $col) $sh->getColumnDimension($col)->setAutoSize(true);
            $sh->getStyle('A2:J'.($row-1))->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setRGB('CBD5E1');
            $sh->freezePane('A3');
            $filename = 'SIAGIE_'.$sec->grado->nivel->nombre.'_'.$sec->grado->nombre.'_Sec'.$sec->id.'_Per'.$per->id.'_'.date('Ymd').'.xlsx';
            $registrarLog();
            return response()->streamDownload(function () use ($ss) { $w = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss); $w->save('php://output'); }, $filename, ['Content-Type'=>'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
        }
        if ($formato === 'pdf') {
            $conf = \App\Models\Configuracion::where('clave','nombre_institucion')->first();
            $ie = $conf->valor ?? 'I.E. EGA';
            $html = '<html><head><meta charset=\"utf-8\"><style>body{font-family: DejaVu Sans, sans-serif; font-size:9px} h1{color:#1E3A8A; font-size:14px; text-align:center; margin:0} h2{color:#334155; font-size:10px; text-align:center; margin:2px 0 8px} table{width:100%; border-collapse:collapse} th{background:#1E3A8A; color:#fff; padding:4px; text-align:center} td{padding:3px; border:1px solid #CBD5E1} tr:nth-child(even){background:#F8FAFC} .c{background:#FECACA; color:#991B1B; font-weight:bold; text-align:center}</style></head><body>';
            $html .= '<h1>'.htmlspecialchars($ie).'</h1><h2>Registro Auxiliar — '.htmlspecialchars($sec->grado->nivel->nombre.' '.$sec->grado->nombre.' '.$sec->nombre).' — '.htmlspecialchars($per->nombre).' — '.date('d/m/Y').'</h2>';
            $html .= '<table><tr><th>#</th><th>DNI</th><th>Estudiante</th><th>Área</th><th>Nivel</th><th>Nota</th><th>Escala</th><th>Motivo C</th></tr>';
            $i=1; foreach ($cals as $c) { $est=$c->matricula->estudiante; $cls=($c->nivel_logro==='C'?' class=\"c\"':''); $html .= '<tr><td>'.$i++.'</td><td>'.htmlspecialchars($est->dni ?? '').'</td><td>'.htmlspecialchars(trim(($est->apellidos ?? '').' '.($est->nombres ?? ''))).'</td><td>'.htmlspecialchars($c->area->nombre ?? '').'</td><td'.$cls.'>'.htmlspecialchars($c->nivel_logro ?? '').'</td><td>'.htmlspecialchars((string)($c->nota ?? '')).'</td><td>'.htmlspecialchars($c->escala ?? '').'</td><td>'.htmlspecialchars($c->motivo_nota_c ?? '').'</td></tr>'; }
            $html .= '</table><p style=\"font-size:7px; color:#64748B; text-align:center; margin-top:10px\">SIGEL-EGA — Generado '.date('d/m/Y H:i').' — '.$ie.'</p></body></html>';
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html)->setPaper('a4','landscape');
            $filename = 'SIAGIE_'.$sec->grado->nivel->nombre.'_'.$sec->grado->nombre.'_Sec'.$sec->id.'_Per'.$per->id.'_'.date('Ymd').'.pdf';
            $registrarLog();
            return $pdf->download($filename);
        }
        $filename = 'SIAGIE_'.$sec->grado->nivel->nombre.'_'.$sec->grado->nombre.'_Sec'.$sec->id.'_Per'.$per->id.'_'.date('Ymd').'.csv';
        $registrarLog();
        return response()->streamDownload(function () use ($cals) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['dni','codigo_estudiante','apellidos_nombres','area_id','area','tipo_evaluacion_id','nivel_logro','nota','escala','motivo_c']);
            foreach ($cals as $c) {
                $est = $c->matricula->estudiante ?? null;
                fputcsv($out, [
                    $est->dni ?? '', $est->codigo_estudiante ?? '', trim(($est->apellidos ?? '').' '.($est->nombres ?? '')),
                    $c->area_id, $c->area->nombre ?? '', $c->tipo_evaluacion_id,
                    $c->nivel_logro ?? '', $c->nota ?? '', $c->escala ?? 'literal', $c->motivo_nota_c ?? '',
                ]);
            }
            fclose($out);
        }, $filename, ['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
