<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\Estudiante;
use App\Models\Matricula;
use App\Models\Periodo;

class FumService
{
    /**
     * Construye los datos completos para la FUM de un estudiante.
     */
    public function datos(Estudiante $estudiante, ?int $periodoId = null): array
    {
        $periodo = $periodoId
            ? Periodo::findOrFail($periodoId)
            : Periodo::where('activo', true)->first();

        $matricula = Matricula::with(['seccion.grado.nivel', 'tipoMatricula', 'estado'])
            ->where('estudiante_id', $estudiante->id)
            ->when($periodo, fn ($q) => $q->where('periodo_id', $periodo->id))
            ->first();

        // Datos de la IE desde configuraciones
        $ie = [
            'nombre' => Configuracion::where('clave', 'nombre_institucion')->value('valor') ?? 'I.E. Pública EGA',
            'codigo_modular' => Configuracion::where('clave', 'codigo_modular')->value('valor'),
            'resolucion_creacion' => Configuracion::where('clave', 'resolucion_creacion')->value('valor'),
            'ugel' => Configuracion::where('clave', 'ugel')->value('valor'),
            'dre' => Configuracion::where('clave', 'dre')->value('valor'),
            'direccion' => Configuracion::where('clave', 'direccion')->value('valor'),
            'telefono' => Configuracion::where('clave', 'telefono')->value('valor'),
            'correo' => Configuracion::where('clave', 'correo')->value('valor'),
            'director' => Configuracion::where('clave', 'director')->value('valor'),
        ];

        // Apoderado (vía hub)
        $apoderado = $estudiante->apoderado;
        $padre = $apoderado?->padres()->first();

        // Necesidades especiales
        $nee = $estudiante->necesidadEspecial ?? null;

        // Hermanos en la IE (estudiantes que comparten apoderado_id)
        $hermanos = collect();
        if ($estudiante->apoderado_id) {
            $hermanos = Estudiante::where('apoderado_id', $estudiante->apoderado_id)
                ->where('id', '!=', $estudiante->id)
                ->with('grado.nivel')
                ->get()
                ->map(fn ($h) => [
                    'dni' => $h->dni,
                    'nombre_completo' => $h->nombre_completo ?? trim($h->apellidos.', '.$h->nombres),
                    'grado' => $h->grado?->nombre,
                    'nivel' => $h->grado?->nivel?->nombre,
                ]);
        }

        return [
            'ie' => $ie,
            'periodo' => $periodo,
            'matricula' => $matricula,
            'estudiante' => $estudiante->load(['nivel', 'grado', 'estado']),
            'apoderado' => $apoderado,
            'padre' => $padre,
            'nee' => $nee,
            'hermanos' => $hermanos,
            'generado_en' => now(),
        ];
    }

    /**
     * Genera la FUM como .docx programático con WordBuilderService.
     */
    public function generarDocx(Estudiante $estudiante, ?int $periodoId = null): string
    {
        $datos = $this->datos($estudiante, $periodoId);
        $builder = app(WordBuilderService::class, ['orientacion' => 'portrait']);

        $builder->addMembrete(
            'FICHA ÚNICA DE MATRÍCULA',
            'Año escolar: '.($datos['periodo']?->anio ?? date('Y'))
        );

        // Sección 1: Datos del estudiante (con foto fusionada)
        $builder->addSeccionTitulo('DATOS DEL ESTUDIANTE');
        $builder->addFotoConDatos([
            ['Apellidos y Nombres', trim(($estudiante->apellidos ?? '').', '.($estudiante->nombres ?? ''))],
            ['DNI', $estudiante->dni ?? ''],
            ['Código', $estudiante->codigo_estudiante ?? ''],
            ['Fecha de nacimiento', $estudiante->fecha_nacimiento ?? ''],
            ['Sexo', $estudiante->sexo === 'M' ? 'Masculino' : ($estudiante->sexo === 'F' ? 'Femenino' : '')],
            ['Lugar de nacimiento', collect([
                $estudiante->distrito_nacimiento,
                $estudiante->provincia_nacimiento,
                $estudiante->departamento_nacimiento,
                $estudiante->pais_nacimiento,
            ])->filter()->implode(', ')],
            ['Lengua materna', $estudiante->lengua_materna ?? ''],
            ['Autoidentificación étnica', $estudiante->autoidentificacion_etnica ?? ''],
            ['Dirección', $estudiante->direccion ?? ''],
        ]);

        // Sección 2: Matrícula
        $builder->addSeccionTitulo('INFORMACIÓN DE MATRÍCULA');
        $mat = $datos['matricula'];
        $builder->addTabla([], [
            ['Nivel', $mat?->seccion?->grado?->nivel?->nombre ?? ''],
            ['Grado y Sección', ($mat?->seccion?->grado?->nombre ?? '').' "'.($mat?->seccion?->nombre ?? '').'"'],
            ['Tipo de matrícula', $mat?->tipoMatricula?->nombre ?? 'Regular'],
            ['Fecha de matrícula', $mat?->fecha ?? ''],
            ['Estado', $mat?->estado?->nombre ?? ''],
        ], ['anchos' => [3000, 6000], 'colorear_celdas' => false]);

        // Sección 3: Apoderado
        $builder->addSeccionTitulo('DATOS DEL REPRESENTANTE LEGAL');
        $padre = $datos['padre'];
        $builder->addTabla([], [
            ['Apellidos y Nombres', $padre ? trim(($padre->apellidos ?? '').', '.($padre->nombres ?? '')) : ''],
            ['DNI', $padre?->dni ?? ''],
            ['Teléfono', $padre?->telefono ?? ''],
            ['Correo', $padre?->email ?? ''],
            ['Dirección', $padre?->direccion ?? ''],
        ], ['anchos' => [3000, 6000], 'colorear_celdas' => false]);

        // NEE (si aplica)
        if ($estudiante->tiene_discapacidad || $datos['nee']) {
            $builder->addSeccionTitulo('NECESIDADES EDUCATIVAS ESPECIALES');
            $nee = $datos['nee'];
            $builder->addTabla([], [
                ['Tiene discapacidad', $estudiante->tiene_discapacidad ? 'SÍ' : 'NO'],
                ['Tipo', $estudiante->tipo_discapacidad ?? $nee?->tipo_nee ?? ''],
                ['Grado', $estudiante->grado_discapacidad ?? ''],
                ['Certificado', $estudiante->tiene_certificado_discapacidad ? 'SÍ' : 'NO'],
            ], ['anchos' => [3000, 6000], 'colorear_celdas' => false]);
        }

        // Sección HERMANOS EN LA IE (solo si hay hermanos)
        if (! empty($datos['hermanos']) && $datos['hermanos']->isNotEmpty()) {
            $builder->addSeccionTitulo('HERMANOS EN LA INSTITUCIÓN EDUCATIVA');

            $filasHermanos = [];
            foreach ($datos['hermanos'] as $h) {
                $filasHermanos[] = [
                    $h['dni'] ?? '',
                    $h['nombre_completo'] ?? '',
                    $h['nivel'] ?? '',
                    $h['grado'] ?? '',
                ];
            }

            $builder->addTabla(
                ['DNI', 'Apellidos y Nombres', 'Nivel', 'Grado'],
                $filasHermanos,
                ['anchos' => [1500, 4500, 2000, 2906], 'colorear_celdas' => false]
            );
        }

        // Firmas
        $builder->addFirmas([
            ['nombre' => 'Firma del Padre/Madre/Apoderado', 'detalle' => 'DNI: '.($padre?->dni ?? '')],
            ['nombre' => 'Firma del Director', 'detalle' => $datos['ie']['director'] ?? ''],
        ]);

        // Pie
        $builder->addPie(
            'SIGEL-EGA — Generado '.now()->format('d/m/Y H:i').' · '.$datos['ie']['nombre']
        );

        $ruta = storage_path('app/private/tmp_'.uniqid().'.docx');

        return $builder->guardar($ruta);
    }
}
