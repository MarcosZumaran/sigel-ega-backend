<?php

namespace App\Services;

use App\Exceptions\NotFoundException;
use App\Models\Auditoria;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

class AuditoriaService
{
    public function paginate(Request $request): LengthAwarePaginator
    {
        $q = Auditoria::with('usuario:id,name,email')->orderByDesc('created_at');
        if ($request->filled('tabla')) $q->where('tabla', $request->input('tabla'));
        if ($request->filled('accion')) $q->where('accion', $request->input('accion'));
        if ($request->filled('usuario_id')) $q->where('usuario_id', $request->input('usuario_id'));
        if ($request->filled('registro_id')) $q->where('registro_id', $request->input('registro_id'));
        if ($request->filled('desde')) $q->where('created_at', '>=', $request->input('desde'));
        if ($request->filled('hasta')) $q->where('created_at', '<=', $request->input('hasta'));

        $perPage = (int) ($request->input('per_page', 20));
        $perPage = $perPage > 100 ? 100 : $perPage;

        return $q->paginate($perPage);
    }

    public function getById(int $id): Auditoria
    {
        $m = Auditoria::with('usuario')->find($id);
        if (! $m) throw new NotFoundException('Auditoría', $id);
        return $m;
    }
}
