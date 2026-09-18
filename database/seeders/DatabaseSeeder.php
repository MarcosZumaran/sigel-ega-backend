<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RolSeeder::class,
            EstadoSeeder::class,
            ParentescoSeeder::class,
            NivelGradoSeeder::class,
            DocenteSeeder::class,
            AreaSeeder::class,
            SeccionSeeder::class,
            TipoMatriculaSeeder::class,
            TipoEvaluacionSeeder::class,
            TipoDocumentoSeeder::class,
            PeriodoSeeder::class,
            ConfiguracionSeeder::class,
            ApoderadoSeeder::class,
            UsuarioSeeder::class,
        ]);

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => bcrypt('password')]
        );
    }
}