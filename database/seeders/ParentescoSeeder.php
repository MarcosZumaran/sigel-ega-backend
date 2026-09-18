<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ParentescoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('parentescos')->insertOrIgnore([
            ['nombre' => 'Padre'],
            ['nombre' => 'Madre'],
            ['nombre' => 'Apoderado'],
            ['nombre' => 'Tutor'],
        ]);
    }
}