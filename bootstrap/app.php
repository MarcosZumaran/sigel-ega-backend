<?php

use App\Exceptions\EnUsoException;
use App\Exceptions\NotFoundException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias(['admin' => \App\Http\Middleware\AdminOnly::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        $exceptions->render(function (NotFoundException $e) {
            return response()->json(['message' => $e->getMessage()], 404);
        });

        $exceptions->render(function (EnUsoException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        });

        $exceptions->render(function (UniqueConstraintViolationException $e) {
            return response()->json(['message' => 'Ya existe un registro con los mismos datos.'], 409);
        });

        $exceptions->render(function (QueryException $e) {
            if (str_contains($e->getMessage(), 'Integrity constraint violation')) {
                return response()->json(['message' => 'No se puede eliminar o modificar: existen datos relacionados.'], 409);
            }

            return response()->json(['message' => 'Error interno de base de datos.'], 500);
        });
    })->create();