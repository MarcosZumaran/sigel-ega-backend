<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Asistencia;
use App\Models\Calificacion;
use App\Models\Docente;
use App\Models\Matricula;
use App\Models\Personal;
use App\Models\Seccion;
use App\Models\TipoEvaluacion;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\TransaccionalTest;

class PermisosDocenteTest extends TestCase
{
    use TransaccionalTest;

    private function crearDocente(string $dni): array
    {
        Role::firstOrCreate(['name' => 'DOCENTE']);
        $user = User::factory()->create();
        $user->assignRole('DOCENTE');
        Personal::create([
            'dni' => $dni,
            'nombres' => 'Docente',
            'apellidos' => 'Prueba',
            'user_id' => $user->id,
        ]);
        $docente = Docente::create([
            'dni' => $dni,
            'nombres' => 'Docente',
            'apellidos' => 'Prueba',
        ]);

        return [$user, $docente];
    }

    private function matriculaDe(Docente $docente): Matricula
    {
        $seccion = Seccion::factory()->create(['docente_id' => $docente->id]);

        return Matricula::factory()->create(['seccion_id' => $seccion->id]);
    }

    private function calificacionDe(Matricula $mat): Calificacion
    {
        return Calificacion::create([
            'matricula_id' => $mat->id,
            'area_id' => Area::factory()->create()->id,
            'tipo_evaluacion_id' => TipoEvaluacion::create(['nombre' => 'Evaluacion Permanente'])->id,
            'nivel_logro' => 'B',
        ]);
    }

    public function test_docente_duenio_actualiza_calificacion(): void
    {
        [$user, $docente] = $this->crearDocente('90000001');
        $cal = $this->calificacionDe($this->matriculaDe($docente));

        $response = $this->actingAs($user)
            ->putJson("/api/calificaciones/{$cal->id}", ['nivel_logro' => 'A']);

        $response->assertStatus(200);
        $this->assertSame('A', $cal->fresh()->nivel_logro);
    }

    public function test_docente_ajeno_no_actualiza_calificacion(): void
    {
        [, $docente] = $this->crearDocente('90000002');
        [$otro] = $this->crearDocente('90000003');
        $cal = $this->calificacionDe($this->matriculaDe($docente));

        $response = $this->actingAs($otro)
            ->putJson("/api/calificaciones/{$cal->id}", ['nivel_logro' => 'A']);

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Solo el docente de la sección o el administrador puede gestionar calificaciones de esa matrícula.']);
        $this->assertSame('B', $cal->fresh()->nivel_logro);
    }

    public function test_docente_duenio_actualiza_asistencia(): void
    {
        [$user, $docente] = $this->crearDocente('90000004');
        $asis = Asistencia::create([
            'matricula_id' => $this->matriculaDe($docente)->id,
            'fecha' => '2026-06-10',
            'estado' => 'presente',
        ]);

        $response = $this->actingAs($user)
            ->putJson("/api/asistencias/{$asis->id}", ['estado' => 'tardia']);

        $response->assertStatus(200);
        $this->assertSame('tardia', $asis->fresh()->estado);
    }

    public function test_docente_ajeno_no_actualiza_asistencia(): void
    {
        [, $docente] = $this->crearDocente('90000005');
        [$otro] = $this->crearDocente('90000006');
        $asis = Asistencia::create([
            'matricula_id' => $this->matriculaDe($docente)->id,
            'fecha' => '2026-06-10',
            'estado' => 'presente',
        ]);

        $response = $this->actingAs($otro)
            ->putJson("/api/asistencias/{$asis->id}", ['estado' => 'tardia']);

        $response->assertStatus(409);
        $response->assertJson(['message' => 'Solo el docente de la sección o el administrador puede gestionar asistencias de esa matrícula.']);
        $this->assertSame('presente', $asis->fresh()->estado);
    }
}
