<?php

namespace App\Traits;

use App\Models\Auditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    protected static function bootAuditable(): void
    {
        static::created(fn ($model) => self::auditLog('create', $model));
        static::updated(fn ($model) => self::auditLog('update', $model));
        static::deleted(fn ($model) => self::auditLog('delete', $model));
        // NOTA: no usar static::restored() — ese helper solo existe en
        // SoftDeletes; en modelos sin SoftDeletes cae en __callStatic y rompe
        // el boot. registerModelEvent funciona en todos los casos.
        static::registerModelEvent('restored', fn ($model) => self::auditLog('restore', $model));
    }

    protected static function auditLog(string $accion, $model): void
    {
        // Evitar log recursivo de auditorias
        if ($model instanceof Auditoria) {
            return;
        }
        try {
            $request = Request::instance();
            $origen = app()->runningInConsole() ? 'console' : ($request->is('api/*') ? 'api' : 'web');
            // payload: diff para update, snapshot para create/delete
            $payload = null;
            if ($accion === 'update') {
                $payload = ['cambios' => $model->getChanges(), 'original' => $model->getOriginal()];
            } elseif (in_array($accion, ['create', 'delete', 'restore'])) {
                $payload = $model->toArray();
                // ocultar password si existe
                unset($payload['password'], $payload['remember_token']);
            }
            Auditoria::create([
                'usuario_id' => Auth::id(),
                'accion' => $accion,
                'tabla' => $model->getTable(),
                'registro_id' => $model->getKey(),
                'payload' => $payload,
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
                'origen' => $origen,
            ]);
        } catch (\Throwable $e) {
            // no romper la transacción principal por fallo de auditoría
            \Illuminate\Support\Facades\Log::warning('Auditoria fallo: ' . $e->getMessage());
        }
    }
}
