<?php

namespace App\Services;

use App\Models\Grado;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;

/**
 * Genera el Cuadro de Orden de Mérito como .docx programático.
 */
class OrdenWordService
{
    /**
     * Filas con puesto para el orden de mérito (compartido PDF/Word).
     *
     * Promedio vigesimal equivalente: AD=4/A=3/B=2/C=1 → ×5.
     * Se excluyen los estudiantes sin niveles registrados.
     */
    public function filas(Grado $grado, Periodo $periodo): array
    {
        $num = ['AD' => 4, 'A' => 3, 'B' => 2, 'C' => 1];
        $seccionIds = Seccion::where('grado_id', $grado->id)->pluck('id');
        $matriculas = Matricula::with(['estudiante', 'seccion'])
            ->whereIn('seccion_id', $seccionIds)->where('periodo_id', $periodo->id)
            ->orderBy('id')->get();
        $filas = [];
        foreach ($matriculas as $m) {
            $informe = EvaluacionService::generarDatosInformeProgreso($m->estudiante_id, $periodo->id);
            $vals = [];
            foreach ($informe['areas'] ?? [] as $a) {
                if (! empty($a['nivel_logro_area']) && isset($num[$a['nivel_logro_area']])) {
                    $vals[] = $num[$a['nivel_logro_area']];
                }
            }
            if ($vals === []) {
                continue;
            }
            $filas[] = ['matricula' => $m, 'promedio' => round(array_sum($vals) / count($vals) * 5, 1)];
        }
        usort($filas, fn ($a, $b) => $b['promedio'] <=> $a['promedio']);
        $puesto = 0;
        $prev = null;
        foreach ($filas as $i => &$f) {
            if ($prev === null || $f['promedio'] < $prev) {
                $puesto = $i + 1;
                $prev = $f['promedio'];
            }
            $f['puesto'] = $puesto;
        }
        unset($f);

        return $filas;
    }

    public function generarDocx(Grado $grado, Periodo $periodo): string
    {
        $grado->loadMissing('nivel');
        $builder = app(WordBuilderService::class, ['orientacion' => 'portrait']);

        $builder->addMembrete(
            'CUADRO DE ORDEN DE MÉRITO',
            $grado->nivel->nombre.' · '.$grado->nombre.' · '.$periodo->nombre
        );

        $filas = $this->filas($grado, $periodo);

        $headers = ['Puesto', 'Estudiante', 'Sección', 'Promedio'];

        $rows = [];
        foreach ($filas as $f) {
            $e = $f['matricula']->estudiante;
            $rows[] = [
                (string) $f['puesto'],
                trim(($e->apellidos ?? '').', '.($e->nombres ?? '')),
                $f['matricula']->seccion->nombre ?? '—',
                number_format($f['promedio'], 1),
            ];
        }

        // 700+7506+1600+1100 = 10906 (ancho útil portrait)
        $builder->addTabla($headers, $rows, [
            'anchos' => [700, 7506, 1600, 1100],
            'colorear_celdas' => false,
        ]);

        $builder->addTexto('');
        $builder->addTexto('Total de registros: '.count($filas), ['bold' => true, 'size' => 9]);

        $builder->addFirmas([
            ['nombre' => 'Dirección', 'detalle' => ''],
        ]);

        $builder->addPie(
            'SIGEL-EGA — Generado '.now()->format('d/m/Y H:i')
        );

        $ruta = storage_path('app/private/tmp_'.uniqid().'.docx');

        return $builder->guardar($ruta);
    }
}
