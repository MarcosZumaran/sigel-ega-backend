<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TipoDocumentoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tipos_documento')->insertOrIgnore([
            ['nombre' => 'Acta', 'descripcion' => 'Acta de reunión, actas de notas, etc.'],
            ['nombre' => 'Oficio', 'descripcion' => 'Comunicación oficial'],
            ['nombre' => 'Resolución', 'descripcion' => 'Resolución de dirección'],
            ['nombre' => 'Memorando', 'descripcion' => 'Comunicación interna'],
            ['nombre' => 'Constancia', 'descripcion' => 'Constancia de vacante, de estudios, etc.'],
            ['nombre' => 'Ficha de observación', 'descripcion' => 'Ficha de observación de estudiante o docente'],
            ['nombre' => 'Informe', 'descripcion' => 'Informe técnico o pedagógico'],
        ]);

        // RF-19: formato de correlativo por tipo (un registro por tipo + año)
        $formatos = [
            'Acta' => 'ACTA N° {numero:03d}-{anio}-IE-EGA',
            'Oficio' => 'OFICIO N° {numero:03d}-{anio}-IE-EGA',
            'Resolución' => 'RESOLUCIÓN DIRECTORAL N° {numero:03d}-{anio}-IE-EGA',
            'Memorando' => 'MEMORANDO N° {numero:03d}-{anio}-IE-EGA',
            'Constancia' => 'CONSTANCIA N° {numero:03d}-{anio}-IE-EGA',
            'Ficha de observación' => 'FICHA N° {numero:03d}-{anio}-IE-EGA',
            'Informe' => 'INFORME N° {numero:03d}-{anio}-IE-EGA',
        ];

        foreach ($formatos as $nombre => $formato) {
            $tipo = \App\Models\TipoDocumento::where('nombre', $nombre)->first();
            if ($tipo) {
                \App\Models\Correlativo::updateOrCreate(
                    [
                        'tipo_documento_id' => $tipo->id,
                        'anio' => (int) date('Y'),
                    ],
                    ['formato' => $formato]
                );
            }
        }
    }
}