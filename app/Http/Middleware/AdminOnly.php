<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || ! $user->hasRole('ADMIN')) {
            return response()->json(['message' => 'Solo el administrador puede realizar esta acción.'], 403);
        }

        return $next($request);
    }
}
