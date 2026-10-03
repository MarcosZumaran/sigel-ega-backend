<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nómina Oficial de Matrícula</title>
    @include('reportes.partials.estilos', ['orientacion' => 'vertical'])
</head>
<body>

@include('reportes.partials.membrete', [
    'titulo' => 'NÓMINA OFICIAL DE MATRÍCULA',
    'subtitulo' => ($seccion->grado->nombre ?? '') . ' "' . ($seccion->nombre ?? '') . '" · Año escolar ' . ($periodo->nombre ?? ''),
    'orientacion' => 'vertical',
])
<div class="datos"><b>Grado:</b> {{ $seccion->grado->nombre ?? '—' }} &nbsp; <b>Sección:</b> {{ $seccion->nombre ?? '—' }} &nbsp; <b>Periodo:</b> {{ $periodo->nombre ?? '—' }} &nbsp; <b>Total:</b> {{ count($matriculas) }} estudiantes</div>
<table><tr><th>N° Orden</th><th>DNI</th><th>Apellidos y Nombres</th><th>Fecha Nac.</th><th>Sexo</th><th>Dirección</th><th>Apoderado</th><th>Tipo Vacante</th></tr>
@foreach($matriculas as $i => $m)@php $e = $m->estudiante; $apo = $e->apoderado->padres->first() ?? null; @endphp<tr><td>{{ $i + 1 }}</td><td>{{ $e->dni ?? '—' }}</td><td>{{ $e->apellidos ?? '' }}, {{ $e->nombres ?? '' }}</td><td>{{ $e->fecha_nacimiento ?? '—' }}</td><td>{{ $e->sexo ?? '—' }}</td><td>{{ $e->direccion ?? '—' }}</td><td>{{ $apo ? trim(($apo->nombres ?? '').' '.($apo->apellidos ?? '')) : '—' }}</td><td>{{ $m->tipo_vacante ?? 'Regular' }}</td></tr>@endforeach
</table>
<div class="resumen">Total de registros: {{ count($matriculas) }}.</div><table class="firmas"><tr><td><p class="lin">Dirección</p></td></tr></table>
<p class="pie">Generado por SIGEL-EGA el {{ date('d/m/Y H:i') }}</p></body></html>
