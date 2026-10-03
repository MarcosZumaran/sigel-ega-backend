<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Acta de Evaluación</title>
    @include('reportes.partials.estilos', ['orientacion' => 'horizontal'])
</head>
<body>

@include('reportes.partials.membrete', [
    'titulo' => 'ACTA OFICIAL DE EVALUACIÓN',
    'subtitulo' => ($seccion->grado->nivel->nombre ?? '') . ' · ' . ($seccion->grado->nombre ?? '') . ' "' . ($seccion->nombre ?? '') . '" · ' . ($periodo->nombre ?? ''),
    'orientacion' => 'horizontal',
])
<div class="datos"><b>Grado:</b> {{ $seccion->grado->nombre ?? '—' }} &nbsp; <b>Sección:</b> {{ $seccion->nombre ?? '—' }} &nbsp; <b>Nivel:</b> {{ $seccion->grado->nivel->nombre ?? '—' }} &nbsp; <b>Periodo:</b> {{ $periodo->nombre ?? '—' }}</div>
<table><tr><th>N°</th><th>DNI</th><th>Apellidos y Nombres</th>@foreach($areas as $a)<th>{{ $a->nombre }}</th>@endforeach</tr>
@foreach($filas as $i => $f)<tr><td>{{ $i + 1 }}</td><td>{{ $f['matricula']->estudiante->dni ?? '—' }}</td><td>{{ $f['matricula']->estudiante->apellidos ?? '' }}, {{ $f['matricula']->estudiante->nombres ?? '' }}</td>@foreach($areas as $a)@php $d = $f['areas'][$a->id] ?? null; @endphp<td class="niv {{ $d['nivel_logro_area'] ?? '' }}">{{ $d['nivel_logro_area'] ?? '—' }}@if(!empty($d['competencias']))<br><small>@foreach($d['competencias'] as $c){{ mb_substr($c['nombre'], 0, 3) }}:{{ $c['nivel_final'] ?? '—' }} @endforeach</small>@endif</td>@endforeach</tr>@endforeach
</table>
<div class="resumen">Total de registros: {{ count($filas) }}.</div><table class="firmas"><tr><td><p class="lin">Docente tutor</p></td><td><p class="lin">Dirección</p></td></tr></table>
<p class="pie">Generado por SIGEL-EGA el {{ date('d/m/Y H:i') }}<span class="pg"></span></p></body></html>
