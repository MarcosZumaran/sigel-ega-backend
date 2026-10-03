<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Orden de Mérito</title>
    @include('reportes.partials.estilos', ['orientacion' => 'vertical'])
</head>
<body>

@include('reportes.partials.membrete', [
    'titulo' => 'ORDEN DE MÉRITO — ' . mb_strtoupper($grado->nombre ?? ''),
    'subtitulo' => 'Periodo ' . ($periodo->nombre ?? '') . ' · Promedio vigesimal (AD=20 · A=15 · B=10 · C=5)',
    'orientacion' => 'vertical',
])
<div class="datos"><b>Grado:</b> {{ $grado->nombre ?? '—' }} &nbsp; <b>Periodo:</b> {{ $periodo->nombre ?? '—' }} &nbsp; <b>Estudiantes evaluados:</b> {{ count($filas) }}</div>
<table><tr><th>Puesto</th><th>DNI</th><th>Apellidos y Nombres</th><th>Promedio</th><th>Sección</th></tr>
@foreach($filas as $f)<tr class="{{ ($f['puesto'] ?? 99) <= 3 ? 'top' : '' }}"><td>{{ $f['puesto'] }}</td><td>{{ $f['matricula']->estudiante->dni ?? '—' }}</td><td>{{ $f['matricula']->estudiante->apellidos ?? '' }}, {{ $f['matricula']->estudiante->nombres ?? '' }}</td><td><b>{{ number_format($f['promedio'], 1) }}</b></td><td>{{ $f['matricula']->seccion->nombre ?? '—' }}</td></tr>@endforeach
</table>
<div class="resumen">Total de registros: {{ count($filas) }}.</div><table class="firmas"><tr><td><p class="lin">Dirección</p></td></tr></table>
<p class="pie">Generado por SIGEL-EGA el {{ date('d/m/Y H:i') }} · En caso de empate se asigna el mismo puesto</p></body></html>
