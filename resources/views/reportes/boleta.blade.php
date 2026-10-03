<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Informe de Progreso del Estudiante</title>
    @include('reportes.partials.estilos', ['orientacion' => 'vertical'])
</head>
<body>

@include('reportes.partials.membrete', [
    'titulo' => 'INFORME DE PROGRESO DEL ESTUDIANTE (CNEB)',
    'subtitulo' => 'Periodo ' . ($informe['periodo']['nombre'] ?? ''),
    'orientacion' => 'vertical',
])
<div class="datos"><b>Estudiante:</b> {{ $informe['estudiante']['nombres'] ?? '' }} {{ $informe['estudiante']['apellidos'] ?? '' }} &nbsp; <b>DNI:</b> {{ $informe['estudiante']['dni'] ?? '—' }} &nbsp; <b>Periodo:</b> {{ $informe['periodo']['nombre'] ?? '—' }}</div>
<table><tr><th>Área / Competencia</th><th>B1</th><th>B2</th><th>B3</th><th>B4</th><th>Final</th><th>Conclusión descriptiva</th></tr>
@foreach($informe['areas'] as $a)<tr><td colspan="7" class="area">{{ $a['nombre'] }} @if($a['nivel_logro_area']) — Nivel del área: <span class="niv {{ $a['nivel_logro_area'] }}">{{ $a['nivel_logro_area'] }}</span>@endif</td></tr>
@foreach($a['competencias'] as $c)<tr><td>{{ $c['nombre'] }}</td>@for($b=1;$b<=4;$b++)<td class="niv {{ $c['bimestres'][$b]['nivel'] ?? '' }}">{{ $c['bimestres'][$b]['nivel'] ?? '—' }}</td>@endfor<td class="niv {{ $c['nivel_final'] ?? '' }}">{{ $c['nivel_final'] ?? '—' }}</td><td>@php $concs = []; foreach(($c['bimestres'] ?? []) as $bb) { if(!empty($bb['conclusion'])) $concs[] = 'B'.$bb['numero'].': '.$bb['conclusion']; } echo implode(' | ', $concs); @endphp</td></tr>@endforeach
@endforeach
</table>
<table class="firmas"><tr><td><p class="lin">Docente tutor</p></td><td><p class="lin">Dirección</p></td><td><p class="lin">Padre / Apoderado</p></td></tr></table>
<p><small>Equivalencia MINEDU: 18-20 AD · 14-17 A · 11-13 B · 0-10 C — tildes á é í ó ú ñ</small></p></body></html>
