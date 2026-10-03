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
}
