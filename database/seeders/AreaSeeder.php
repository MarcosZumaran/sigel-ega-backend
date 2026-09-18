<?php

namespace Database\Seeders;

use App\Models\Area;
use Illuminate\Database\Seeder;

class AreaSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            'Comunicación' => [
                'Se comunica oralmente en su lengua materna',
                'Lee diversos tipos de textos escritos en su lengua materna',
                'Escribe diversos tipos de textos en su lengua materna',
            ],
            'Matemática' => [
                'Resuelve problemas de cantidad',
                'Resuelve problemas de regularidad, equivalencia y cambio',
                'Resuelve problemas de forma, movimiento y localización',
                'Resuelve problemas de gestión de datos e incertidumbre',
            ],
            'Ciencia y Tecnología' => [
                'Explica el mundo físico basándose en conocimientos sobre los seres vivos, materia y energía, biodiversidad, Tierra y universo',
                'Diseña y construye soluciones tecnológicas para resolver problemas de su entorno',
            ],
            'Personal Social' => [
                'Construye su identidad',
                'Convive y participa democráticamente en la búsqueda del bien común',
                'Construye interpretaciones históricas',
                'Gestiona responsablemente el espacio y el ambiente',
                'Gestiona responsablemente los recursos económicos',
            ],
            'Ciencias Sociales' => [
                'Construye interpretaciones históricas',
                'Gestiona responsablemente el espacio y el ambiente',
                'Gestiona responsablemente los recursos económicos',
            ],
            'Desarrollo Personal, Ciudadanía y Cívica' => [
                'Construye su identidad',
                'Convive y participa democráticamente en la búsqueda del bien común',
            ],
            'Educación Física' => [
                'Se desenvuelve de manera autónoma a través de su motricidad',
                'Asume una vida saludable',
                'Interactúa a través de sus habilidades sociomotrices',
            ],
            'Arte y Cultura' => [
                'Aprecia de manera crítica manifestaciones artístico-culturales',
                'Crea proyectos desde los lenguajes artísticos',
            ],
            'Educación Religiosa' => [
                'Construye su identidad como persona humana, amada por Dios, digna, libre y trascendente',
                'Asume la experiencia del encuentro personal y comunitario con Dios en su proyecto de vida',
            ],
            'Educación para el Trabajo' => [
                'Gestiona proyectos de emprendimiento económico y social',
            ],
            'Inglés' => [
                'Se comunica oralmente en inglés como lengua extranjera',
                'Lee diversos tipos de textos escritos en inglés como lengua extranjera',
                'Escribe diversos tipos de textos en inglés como lengua extranjera',
            ],
            'Castellano como Segunda Lengua' => [
                'Se comunica oralmente en castellano como segunda lengua',
                'Lee diversos tipos de textos escritos en castellano como segunda lengua',
            ],
        ];

        foreach ($areas as $nombre => $competencias) {
            $area = Area::firstOrCreate(
                ['nombre' => $nombre, 'area_padre_id' => null],
                ['codigo_siagie' => strtoupper(str_replace([' ', ','], ['_', ''], iconv('UTF-8', 'ASCII//TRANSLIT', $nombre)))],
            );

            foreach ($competencias as $competencia) {
                Area::firstOrCreate(
                    ['nombre' => $competencia, 'area_padre_id' => $area->id],
                    ['codigo_siagie' => null],
                );
            }
        }
    }
}