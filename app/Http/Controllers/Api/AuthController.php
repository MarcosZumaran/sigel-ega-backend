<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|string|email|max:150|unique:users,email',
            'password' => ['required', Password::min(8)],
        ]);

        // SEGURIDAD: nunca aceptar rol_id ni estado_id del request (auto-escalado).
        // Se fuerza el rol menos privilegiado (DOCENTE) y el estado Activo.
        $data['rol_id'] = \App\Models\Rol::where('nombre', 'DOCENTE')->value('id')
            ?? \App\Models\Rol::orderBy('id', 'desc')->value('id');
        $data['estado_id'] = \App\Models\Estado::where('nombre', 'Activo')
            ->where('tipo_aplica', 'usuario')
            ->value('id');

        $data['password'] = Hash::make($data['password']);

        $user = \App\Models\User::query()->create($data);

        $token = $user->createToken('sigel-ega')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load(['rol', 'estado']),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|string|email|max:150',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($data, true)) {
            return response()->json(['message' => 'Credenciales incorrectas'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('sigel-ega')->plainTextToken;
        \App\Models\Auditoria::create([
            'usuario_id' => $user->id,
            'accion' => 'login',
            'tabla' => 'users',
            'registro_id' => $user->id,
            'payload' => ['email' => $user->email],
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'origen' => 'api',
        ]);

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load(['rol', 'estado']),
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user()->load(['rol', 'estado']));
    }

    public function logout(Request $request): JsonResponse
    {
        $uid = $request->user()->id;
        $request->user()->currentAccessToken()?->delete();
        \App\Models\Auditoria::create([
            'usuario_id' => $uid,
            'accion' => 'logout',
            'tabla' => 'users',
            'registro_id' => $uid,
            'payload' => null,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
            'origen' => 'api',
        ]);

        return response()->json(['message' => 'Sesión cerrada correctamente']);
    }
}