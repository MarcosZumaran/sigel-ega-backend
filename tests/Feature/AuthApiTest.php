<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Tests\Traits\TransaccionalTest;

class AuthApiTest extends TestCase
{
    use TransaccionalTest;  // ← NO RefreshDatabase

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'ADMIN']);
        Role::firstOrCreate(['name' => 'DOCENTE']);
    }

    public function test_login_retorna_token_con_credenciales_validas(): void
    {
        $user = User::factory()->create([
            'email' => 'test-login-'.uniqid().'@ega.edu.pe',
            'password' => Hash::make('password123'),
        ]);
        $user->assignRole('ADMIN');

        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'token_type', 'user']);
    }

    public function test_login_rechaza_credenciales_invalidas(): void
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'noexiste-'.uniqid().'@ega.edu.pe',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
    }

    public function test_me_requiere_autenticacion(): void
    {
        $response = $this->getJson('/api/auth/me');
        $response->assertStatus(401);
    }

    public function test_me_retorna_usuario_autenticado(): void
    {
        $user = User::factory()->create();
        $user->assignRole('ADMIN');

        $response = $this->actingAs($user)->getJson('/api/auth/me');

        $response->assertStatus(200);
    }
}
