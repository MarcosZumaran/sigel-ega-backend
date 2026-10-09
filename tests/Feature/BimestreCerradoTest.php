<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Bimestre;
use App\Models\Periodo;
use App\Models\Seccion;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\TransaccionalTest;

class BimestreCerradoTest extends TestCase
{
    use TransaccionalTest;

    public function test_no_se_puede_crear_actividad_en_bimestre_cerrado(): void
    {
        Role::firstOrCreate(['name' => 'ADMIN']);
        $admin = User::factory()->create();
        $admin->assignRole('ADMIN');

        $periodo = Periodo::factory()->create(['activo' => true]);
        $bimestre = Bimestre::factory()->create([
            'periodo_id' => $periodo->id,
            'numero' => (int) (Bimestre::where('periodo_id', $periodo->id)->max('numero') ?? 0) + 1,
            'activo' => false,
        ]);
        $seccion = Seccion::factory()->create();
        $competencia = Area::factory()->create(['tipo' => 'competencia']);

        $response = $this->actingAs($admin)->postJson('/api/actividades', [
            'competencia_id' => $competencia->id,
            'bimestre_id' => $bimestre->id,
            'seccion_id' => $seccion->id,
            'titulo' => 'Actividad en bimestre cerrado',
            'fecha' => '2026-10-15',
        ]);

        $response->assertStatus(403);
    }
}
