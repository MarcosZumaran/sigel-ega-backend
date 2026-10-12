<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Acta de Evaluación</title>
    @include('reportes.partials.estilos', ['orientacion' => 'horizontal'])
    <style>
        /* BUG-17: una columna por competencia; headers compactos, niveles coloreados. */
        th.comp { font-size: 6.5px; padding: 2px; max-width: 52px; word-wrap: break-word; }
        th.area-nombre { font-size: 7.5px; }
        td.nom { font-size: 7.5px; }
        .leyenda { font-size: 7px; color: #475569; margin-top: 4px; }
    </style>
</head>
<body>

@include('reportes.partials.membrete', [
    'titulo' => 'ACTA OFICIAL DE EVALUACIÓN',
    'subtitulo' => ($seccion->grado->nivel->nombre ?? '') . ' · ' . ($seccion->grado->nombre ?? '') . ' "' . ($seccion->nombre ?? '') . '" · ' . ($periodo->nombre ?? ''),
    'orientacion' => 'horizontal',
])
<div class="datos"><b>Grado:</b> {{ $seccion->grado->nombre ?? '—' }} &nbsp; <b>Sección:</b> {{ $seccion->nombre ?? '—' }} &nbsp; <b>Nivel:</b> {{ $seccion->grado->nivel->nombre ?? '—' }} &nbsp; <b>Periodo:</b> {{ $periodo->nombre ?? '—' }}</div>
<table>
    <tr>
        <th rowspan="2">N°</th>
        <th rowspan="2">DNI</th>
        <th rowspan="2">Apellidos y Nombres</th>
        @foreach($areas as $a)
            <th class="area-nombre" colspan="{{ max($a->areasHijas->count(), 1) }}">{{ $a->nombre }}</th>
        @endforeach
    </tr>
    <tr>
        @foreach($areas as $a)
            @forelse($a->areasHijas as $h)
                <th class="comp">{{ $h->nombre }}</th>
            @empty
                <th class="comp">Nivel</th>
            @endforelse
        @endforeach
    </tr>
    @foreach($filas as $i => $f)
        @php
            $dniFila = $f['matricula']->estudiante->dni ?? '—';
            $apeFila = $f['matricula']->estudiante->apellidos ?? '';
            $nomFila = $f['matricula']->estudiante->nombres ?? '';
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td>{{ $dniFila }}</td>
            <td class="nom">{{ $apeFila }}, {{ $nomFila }}</td>
            @foreach($areas as $a)
                @php
                    $d = $f['areas'][$a->id] ?? null;
                    $porComp = [];
                    foreach (($d['competencias'] ?? []) as $c) {
                        $porComp[$c['id']] = $c;
                    }
                @endphp
                @forelse($a->areasHijas as $h)
                    @php
                        $c = $porComp[$h->id] ?? null;
                        $nv = $c['nivel_final'] ?? null;
                    @endphp
                    <td class="niv {{ $nv ?? '' }}">{{ $nv ?? '—' }}</td>
                @empty
                    <td class="niv {{ $d['nivel_logro_area'] ?? '' }}">{{ $d['nivel_logro_area'] ?? '—' }}</td>
                @endforelse
            @endforeach
        </tr>
    @endforeach
</table>
<div class="leyenda">Leyenda CNEB: <b>AD</b> = Logro destacado · <b>A</b> = Logro esperado · <b>B</b> = En proceso · <b>C</b> = En inicio · — = Sin calificar. Cada columna corresponde a una competencia; el nivel mostrado es el nivel final anual.</div>
<div class="resumen">Total de registros: {{ count($filas) }}.</div><table class="firmas"><tr><td><p class="lin">Docente tutor</p></td><td><p class="lin">Dirección</p></td></tr></table>
<p class="pie">Generado por SIGEL-EGA el {{ date('d/m/Y H:i') }}<span class="pg"></span></p></body></html>
