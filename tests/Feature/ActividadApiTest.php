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

class ActividadApiTest extends TestCase
{
    use TransaccionalTest;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'ADMIN']);
        $this->admin = User::where('email', 'admin@ega.edu.pe')->first()
            ?? User::factory()->create();
        if (! $this->admin->hasRole('ADMIN')) {
            $this->admin->assignRole('ADMIN');
        }
    }

    /**
     * Número de bimestre libre en el periodo (unique periodo_id+numero).
     */
    private function numeroLibre(int $periodoId): int
    {
        return (int) (Bimestre::where('periodo_id', $periodoId)->max('numero') ?? 0) + 1;
    }

    public function test_lista_actividades_requiere_autenticacion(): void
    {
        $response = $this->getJson('/api/actividades');
        $response->assertStatus(401);
    }

    public function test_lista_actividades_con_admin(): void
    {
        $response = $this->actingAs($this->admin)->getJson('/api/actividades');
        $response->assertStatus(200);
    }

    public function test_crea_actividad_con_datos_validos(): void
    {
        $periodo = Periodo::factory()->create(['activo' => true]);
        $bimestre = Bimestre::factory()->create([
            'periodo_id' => $periodo->id,
            'numero' => $this->numeroLibre($periodo->id),
            'activo' => true,
        ]);
        $seccion = Seccion::factory()->create();
        $competencia = Area::factory()->create(['tipo' => 'competencia']);

        $response = $this->actingAs($this->admin)->postJson('/api/actividades', [
            'competencia_id' => $competencia->id,
            'bimestre_id' => $bimestre->id,
            'seccion_id' => $seccion->id,
            'titulo' => 'Exposición oral de prueba',
            'fecha' => '2026-10-15',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('actividades', [
            'titulo' => 'Exposición oral de prueba',
        ]);
    }

    public function test_rechaza_titulo_muy_corto(): void
    {
        $periodo = Periodo::factory()->create(['activo' => true]);
        $bimestre = Bimestre::factory()->create([
            'periodo_id' => $periodo->id,
            'numero' => $this->numeroLibre($periodo->id),
            'activo' => true,
        ]);
        $seccion = Seccion::factory()->create();
        $competencia = Area::factory()->create(['tipo' => 'competencia']);

        $response = $this->actingAs($this->admin)->postJson('/api/actividades', [
            'competencia_id' => $competencia->id,
            'bimestre_id' => $bimestre->id,
            'seccion_id' => $seccion->id,
            'titulo' => 'ab',
            'fecha' => '2026-10-15',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['titulo']);
    }
}
