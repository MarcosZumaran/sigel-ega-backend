<?php

namespace App\Services;

use App\Models\Matricula;
use App\Models\Periodo;
use App\Models\Seccion;
use Illuminate\Support\Collection;

/**
 * Genera la Nómina de Matrícula como .docx programático.
 */
class NominaWordService
{
    /**
     * Matrículas con apoderado para la nómina (compartido PDF/Word).
     */
    public function matriculas(Seccion $seccion, Periodo $periodo): Collection
    {
        return Matricula::with(['estudiante.apoderado.padres'])
            ->where('seccion_id', $seccion->id)->where('periodo_id', $periodo->id)
            ->orderBy('id')->get();
    }

    public function generarDocx(Seccion $seccion, Periodo $periodo): string
    {
        $seccion->loadMissing('grado.nivel');
        $builder = app(WordBuilderService::class, ['orientacion' => 'portrait']);

        $builder->addMembrete(
            'NÓMINA DE MATRÍCULA',
            $seccion->grado->nivel->nombre.' · '.$seccion->grado->nombre.' "'.$seccion->nombre.'" · '.$periodo->nombre
        );

        $matriculas = $this->matriculas($seccion, $periodo);

        $headers = ['N°', 'DNI', 'Apellidos y Nombres', 'Fecha Nac.', 'Sexo', 'Dirección', 'Apoderado', 'Tipo Vacante'];

        $rows = [];
        foreach ($matriculas as $idx => $m) {
            $e = $m->estudiante;
            $apo = $e?->apoderado?->padres?->first();
            $rows[] = [
                (string) ($idx + 1),
                $e->dni ?? '—',
                trim(($e->apellidos ?? '').', '.($e->nombres ?? '')),
                $e->fecha_nacimiento ?? '—',
                $e->sexo ?? '—',
                $e->direccion ?? '—',
                $apo ? trim(($apo->apellidos ?? '').', '.($apo->nombres ?? '')) : '—',
                $m->tipo_vacante ?? 'Regular',
            ];
        }

        // 400+900+2300+850+500+2400+2556+1000 = 10906 (ancho útil portrait)
        $builder->addTabla($headers, $rows, [
            'anchos' => [400, 900, 2300, 850, 500, 2400, 2556, 1000],
            'colorear_celdas' => false,
        ]);

        $builder->addTexto('');
        $builder->addTexto('Total de registros: '.count($matriculas), ['bold' => true, 'size' => 9]);

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
