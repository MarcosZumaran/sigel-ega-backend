<?php

namespace Tests\Feature;

use App\Models\Actividad;
use App\Models\Matricula;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\TransaccionalTest;

class CalificacionActividadTest extends TestCase
{
    use TransaccionalTest;

    public function test_rechaza_nivel_logro_invalido(): void
    {
        Role::firstOrCreate(['name' => 'ADMIN']);
        $admin = User::factory()->create();
        $admin->assignRole('ADMIN');

        $actividad = Actividad::factory()->create();
        $matricula = Matricula::factory()->create();

        $response = $this->actingAs($admin)
            ->postJson("/api/actividades/{$actividad->id}/calificaciones", [
                'items' => [
                    ['matricula_id' => $matricula->id, 'nivel_logro' => 'X'],
                ],
            ]);

        $response->assertStatus(422);
    }
}
