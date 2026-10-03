<?php

namespace App\Services;

use App\Models\Estudiante;

/**
 * Genera el Informe de Progreso del Estudiante (boleta CNEB) como .docx programático.
 */
class BoletaWordService
{
    public function generarDocx(Estudiante $estudiante, ?int $periodoId = null): string
    {
        $informe = EvaluacionService::generarDatosInformeProgreso($estudiante->id, $periodoId);
        $builder = app(WordBuilderService::class, ['orientacion' => 'portrait']);

        $builder->addMembrete(
            'INFORME DE PROGRESO DEL ESTUDIANTE (CNEB)',
            'Periodo '.($informe['periodo']->nombre ?? '')
        );

        $e = $informe['estudiante'];
        $builder->addTexto(
            'Estudiante: '.trim(($e->apellidos ?? '').', '.($e->nombres ?? ''))
            .' · DNI: '.($e->dni ?? '—'),
            ['size' => 9, 'bold' => true]
        );

        $builder->addTexto('');

        foreach ($informe['areas'] ?? [] as $area) {
            $titulo = $area['nombre'];
            if (! empty($area['nivel_logro_area'])) {
                $titulo .= ' — Nivel del área: '.$area['nivel_logro_area'];
            }
            $builder->addSeccionTitulo($titulo);

            $numBims = count($area['competencias'][0]['bimestres'] ?? []);
            $headers = ['Área / Competencia'];
            for ($b = 1; $b <= $numBims; $b++) {
                $headers[] = 'B'.$b;
            }
            $headers[] = 'Final';

            $rows = [];
            $conclusiones = [];
            foreach ($area['competencias'] ?? [] as $comp) {
                $fila = [$comp['nombre']];
                foreach ($comp['bimestres'] ?? [] as $bb) {
                    $fila[] = $bb['nivel'] ?? '—';
                }
                $fila[] = $comp['nivel_final'] ?? '—';
                $rows[] = $fila;

                $concs = [];
                foreach ($comp['bimestres'] ?? [] as $bb) {
                    if (! empty($bb['conclusion'])) {
                        $concs[] = 'B'.$bb['numero'].': '.$bb['conclusion'];
                    }
                }
                if ($concs !== []) {
                    $conclusiones[] = $comp['nombre'].' — '.implode(' | ', $concs);
                }
            }

            // Competencia usa el resto; B* y Final 700 c/u
            $anchoComp = $builder->getAnchoUtil() - ($numBims * 700) - 700;
            $anchos = array_merge([$anchoComp], array_fill(0, $numBims + 1, 700));

            $builder->addTabla($headers, $rows, [
                'anchos' => $anchos,
                'colorear_celdas' => true,
            ]);

            foreach ($conclusiones as $texto) {
                $builder->addTexto($texto, ['size' => 8]);
            }
        }

        $builder->addTexto('');
        $builder->addTexto('Equivalencia MINEDU: 18-20 AD · 14-17 A · 11-13 B · 0-10 C', ['size' => 7]);

        $builder->addFirmas([
            ['nombre' => 'Docente tutor', 'detalle' => ''],
            ['nombre' => 'Dirección', 'detalle' => ''],
            ['nombre' => 'Padre / Apoderado', 'detalle' => ''],
        ]);

        $builder->addPie(
            'SIGEL-EGA — Generado '.now()->format('d/m/Y H:i')
        );

        $ruta = storage_path('app/private/tmp_'.uniqid().'.docx');

        return $builder->guardar($ruta);
    }
}
