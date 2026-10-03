{{--
    Membrete institucional reutilizable.
    Variables requeridas:
    - $titulo (string): título del documento (ej. "ACTA OFICIAL DE EVALUACIÓN")
    - $subtitulo (string|null): subtítulo opcional
    - $orientacion (string): 'horizontal' o 'vertical' (default: vertical)
--}}

@php
    $ie = [
        'nombre' => \App\Models\Configuracion::where('clave', 'nombre_institucion')->value('valor') ?? 'I.E. Pública EGA',
        'codigo_modular' => \App\Models\Configuracion::where('clave', 'codigo_modular')->value('valor'),
        'resolucion_creacion' => \App\Models\Configuracion::where('clave', 'resolucion_creacion')->value('valor'),
        'ugel' => \App\Models\Configuracion::where('clave', 'ugel')->value('valor'),
        'dre' => \App\Models\Configuracion::where('clave', 'dre')->value('valor'),
        'direccion' => \App\Models\Configuracion::where('clave', 'direccion')->value('valor'),
        'telefono' => \App\Models\Configuracion::where('clave', 'telefono')->value('valor'),
        'correo' => \App\Models\Configuracion::where('clave', 'correo')->value('valor'),
    ];
@endphp

<div class="membrete">
    <div class="membrete-linea-1">
        <strong>{{ $ie['nombre'] }}</strong>
        @if($ie['codigo_modular'])
            <span class="membrete-sep">·</span>
            Código Modular: {{ $ie['codigo_modular'] }}
        @endif
    </div>

    @if($ie['resolucion_creacion'] || $ie['ugel'] || $ie['dre'])
    <div class="membrete-linea-2">
        @if($ie['resolucion_creacion'])Resolución: {{ $ie['resolucion_creacion'] }}@endif
        @if($ie['ugel'])<span class="membrete-sep">·</span>UGEL: {{ $ie['ugel'] }}@endif
        @if($ie['dre'])<span class="membrete-sep">·</span>DRE: {{ $ie['dre'] }}@endif
    </div>
    @endif

    @if($ie['direccion'] || $ie['telefono'] || $ie['correo'])
    <div class="membrete-linea-3">
        @if($ie['direccion']){{ $ie['direccion'] }}@endif
        @if($ie['telefono'])<span class="membrete-sep">·</span>Tel: {{ $ie['telefono'] }}@endif
        @if($ie['correo'])<span class="membrete-sep">·</span>{{ $ie['correo'] }}@endif
    </div>
    @endif

    <div class="membrete-titulo">
        <h1>{{ $titulo }}</h1>
        @if(!empty($subtitulo))
            <h2>{{ $subtitulo }}</h2>
        @endif
    </div>
</div>
