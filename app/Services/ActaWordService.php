<?php

namespace App\Services;

use App\Models\Area;
use App\Models\Calificacion;
use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;

/**
 * Genera el Acta Oficial de Evaluación como .docx programático.
 */
class ActaWordService
{
    public function generarDocx(Seccion $seccion, Periodo $periodo): string
    {
        $seccion->loadMissing('grado.nivel');
        $builder = app(WordBuilderService::class, ['orientacion' => 'landscape']);

        $builder->addMembrete(
            'ACTA OFICIAL DE EVALUACIÓN',
            $seccion->grado->nivel->nombre.' · '.$seccion->grado->nombre.' "'.$seccion->nombre.'" · '.$periodo->nombre
        );

        // Datos del acta
        $builder->addTexto(
            'Grado: '.$seccion->grado->nombre.' · Sección: '.$seccion->nombre
            .' · Nivel: '.$seccion->grado->nivel->nombre
            .' · Periodo: '.$periodo->nombre,
            ['size' => 9, 'bold' => true]
        );

        $builder->addTexto('');

        // Áreas raíz del nivel
        $areas = Area::whereNull('area_padre_id')
            ->where(function ($q) use ($seccion) {
                $q->where('nivel_id', $seccion->grado->nivel_id)->orWhereNull('nivel_id');
            })
            ->orderBy('id')
            ->get();

        // Matrículas con sus calificaciones
        $matriculas = Matricula::with(['estudiante'])
            ->where('seccion_id', $seccion->id)
            ->where('periodo_id', $periodo->id)
            ->get();

        // Encabezados: N° | DNI | Apellidos y Nombres | [áreas...]
        $headers = ['N°', 'DNI', 'Apellidos y Nombres'];
        foreach ($areas as $area) {
            $headers[] = $area->nombre;
        }

        // Filas
        $rows = [];
        foreach ($matriculas as $idx => $mat) {
            $fila = [
                (string) ($idx + 1),
                $mat->estudiante->dni ?? '',
                trim(($mat->estudiante->apellidos ?? '').', '.($mat->estudiante->nombres ?? '')),
            ];

            foreach ($areas as $area) {
                $cal = Calificacion::where('matricula_id', $mat->id)
                    ->where('area_id', $area->id)
                    ->whereNotNull('nivel_logro')
                    ->first();
                $fila[] = $cal?->nivel_logro ?? '—';
            }

            $rows[] = $fila;
        }

        $anchos = $this->calcularAnchosActa($builder->getAnchoUtil(), count($areas));

        $builder->addTabla($headers, $rows, [
            'anchos' => $anchos,
            'colorear_celdas' => true,
        ]);

        $builder->addTexto('');
        $builder->addTexto('Total de registros: '.count($matriculas), ['bold' => true, 'size' => 9]);

        $builder->addFirmas([
            ['nombre' => 'Docente tutor', 'detalle' => ''],
            ['nombre' => 'Dirección', 'detalle' => ''],
        ]);

        $builder->addPie(
            'SIGEL-EGA — Generado '.now()->format('d/m/Y H:i')
        );

        $ruta = storage_path('app/private/tmp_'.uniqid().'.docx');

        return $builder->guardar($ruta);
    }

    private function calcularAnchosActa(int $anchoTotal, int $numAreas): array
    {
        $anchoNum = 400;
        $anchoDni = 800;
        $anchoNombre = 2800;
        $anchoArea = $numAreas > 0
            ? (int) (($anchoTotal - $anchoNum - $anchoDni - $anchoNombre) / $numAreas)
            : 0;

        $anchos = [$anchoNum, $anchoDni, $anchoNombre];
        for ($i = 0; $i < $numAreas; $i++) {
            $anchos[] = $anchoArea;
        }

        return $anchos;
    }
}
