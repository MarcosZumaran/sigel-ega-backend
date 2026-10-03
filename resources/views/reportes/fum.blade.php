<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha Única de Matrícula</title>
    <style>
        @page { size: A4 portrait; margin: 12mm 10mm; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 8.5px; color: #1E293B; }
        .header { text-align: center; border-bottom: 2px solid #1E3A8A; padding-bottom: 4px; margin-bottom: 6px; }
        .header h1 { color: #1E3A8A; font-size: 13px; margin: 0; }
        .header h2 { color: #334155; font-size: 10px; margin: 2px 0; }
        .header p { font-size: 7.5px; color: #64748B; margin: 0; }
        .seccion { margin-top: 6px; }
        .seccion-titulo { background: #1E3A8A; color: #fff; padding: 3px 6px; font-weight: bold; font-size: 8px; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 5px; border: 1px solid #CBD5E1; vertical-align: top; }
        td.label { background: #F1F5F9; font-weight: bold; width: 22%; font-size: 7.5px; color: #475569; }
        td.valor { background: #FFFFFF; }
        .firma { text-align: center; margin-top: 20px; }
        .firma-line { border-top: 1px solid #000; width: 200px; margin: 0 auto 2px; padding-top: 3px; font-size: 8px; }
        .foto { width: 90px; height: 120px; border: 1px solid #CBD5E1; background: #F8FAFC; text-align: center; font-size: 7px; color: #94A3B8; padding-top: 50px; }
    </style>
</head>
<body>

<div class="header">
    <h1>{{ $ie['nombre'] }}</h1>
    <h2>FICHA ÚNICA DE MATRÍCULA</h2>
    <p>
        Código Modular: {{ $ie['codigo_modular'] ?: '___________' }} ·
        Resolución: {{ $ie['resolucion_creacion'] ?: '___________' }} ·
        UGEL: {{ $ie['ugel'] ?: '___________' }} ·
        DRE: {{ $ie['dre'] ?: '___________' }}
    </p>
    <p>Año escolar: {{ $periodo?->anio ?? date('Y') }}</p>
</div>

<table>
    <tr>
        <td rowspan="6" width="100">
            <div class="foto">FOTO DEL<br>ESTUDIANTE</div>
        </td>
        <td class="label">Apellidos y Nombres</td>
        <td class="valor" colspan="3">{{ trim(($estudiante->apellidos ?? '') . ', ' . ($estudiante->nombres ?? '')) }}</td>
    </tr>
    <tr>
        <td class="label">DNI</td>
        <td class="valor">{{ $estudiante->dni ?? '' }}</td>
        <td class="label">Código</td>
        <td class="valor">{{ $estudiante->codigo_estudiante ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Fecha de nacimiento</td>
        <td class="valor">{{ $estudiante->fecha_nacimiento }}</td>
        <td class="label">Sexo</td>
        <td class="valor">{{ $estudiante->sexo === 'M' ? 'Masculino' : ($estudiante->sexo === 'F' ? 'Femenino' : '') }}</td>
    </tr>
    <tr>
        <td class="label">Lugar de nacimiento</td>
        <td class="valor" colspan="3">
            {{ collect([$estudiante->distrito_nacimiento, $estudiante->provincia_nacimiento, $estudiante->departamento_nacimiento, $estudiante->pais_nacimiento])->filter()->implode(', ') ?: '___________' }}
        </td>
    </tr>
    <tr>
        <td class="label">Lengua materna</td>
        <td class="valor">{{ $estudiante->lengua_materna ?? '' }}</td>
        <td class="label">Autoidentificación étnica</td>
        <td class="valor">{{ $estudiante->autoidentificacion_etnica ?? '' }}</td>
    </tr>
    <tr>
        <td class="label">Dirección</td>
        <td class="valor" colspan="3">{{ $estudiante->direccion ?? '' }}</td>
    </tr>
</table>

<div class="seccion">
    <div class="seccion-titulo">INFORMACIÓN DE MATRÍCULA</div>
    <table>
        <tr>
            <td class="label">Nivel</td>
            <td class="valor">{{ $matricula?->seccion?->grado?->nivel?->nombre ?? '' }}</td>
            <td class="label">Grado y Sección</td>
            <td class="valor">{{ $matricula?->seccion?->grado?->nombre ?? '' }} "{{ $matricula?->seccion?->nombre ?? '' }}"</td>
        </tr>
        <tr>
            <td class="label">Tipo de matrícula</td>
            <td class="valor">{{ $matricula?->tipoMatricula?->nombre ?? 'Regular' }}</td>
            <td class="label">Fecha de matrícula</td>
            <td class="valor">{{ $matricula?->fecha ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Estado</td>
            <td class="valor" colspan="3">{{ $matricula?->estado?->nombre ?? '' }}</td>
        </tr>
    </table>
</div>

<div class="seccion">
    <div class="seccion-titulo">DATOS DEL REPRESENTANTE LEGAL</div>
    <table>
        <tr>
            <td class="label">Apellidos y Nombres</td>
            <td class="valor">{{ $padre ? trim($padre->apellidos . ', ' . $padre->nombres) : '___________' }}</td>
            <td class="label">DNI</td>
            <td class="valor">{{ $padre?->dni ?? '___________' }}</td>
        </tr>
        <tr>
            <td class="label">Teléfono</td>
            <td class="valor">{{ $padre?->telefono ?? '' }}</td>
            <td class="label">Correo</td>
            <td class="valor">{{ $padre?->email ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Dirección</td>
            <td class="valor" colspan="3">{{ $padre?->direccion ?? '' }}</td>
        </tr>
    </table>
</div>

@if($estudiante->tiene_discapacidad || $nee)
<div class="seccion">
    <div class="seccion-titulo">NECESIDADES EDUCATIVAS ESPECIALES</div>
    <table>
        <tr>
            <td class="label">Tiene discapacidad</td>
            <td class="valor">{{ $estudiante->tiene_discapacidad ? 'SÍ' : 'NO' }}</td>
            <td class="label">Tipo</td>
            <td class="valor">{{ $estudiante->tipo_discapacidad ?? $nee?->tipo_nee ?? '' }}</td>
        </tr>
        <tr>
            <td class="label">Grado</td>
            <td class="valor">{{ $estudiante->grado_discapacidad ?? '' }}</td>
            <td class="label">Certificado</td>
            <td class="valor">{{ $estudiante->tiene_certificado_discapacidad ? 'SÍ' : 'NO' }}</td>
        </tr>
        @if($nee)
        <tr>
            <td class="label">Servicio</td>
            <td class="valor">{{ $nee->servicio }}</td>
            <td class="label">Estado SEHO</td>
            <td class="valor">{{ $nee->estado_seho ?? '' }} {{ $nee->codigo_seho ? '('.$nee->codigo_seho.')' : '' }}</td>
        </tr>
        @endif
    </table>
</div>
@endif

@if($hermanos->isNotEmpty())
<div class="seccion">
    <div class="seccion-titulo">HERMANOS EN LA INSTITUCIÓN EDUCATIVA</div>
    <table>
        <thead>
            <tr>
                <td class="label">DNI</td>
                <td class="label">Apellidos y Nombres</td>
                <td class="label">Nivel</td>
                <td class="label">Grado</td>
            </tr>
        </thead>
        <tbody>
            @foreach($hermanos as $h)
            <tr>
                <td class="valor">{{ $h['dni'] }}</td>
                <td class="valor">{{ $h['nombre_completo'] }}</td>
                <td class="valor">{{ $h['nivel'] }}</td>
                <td class="valor">{{ $h['grado'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

<div class="firma">
    <table style="border: none;">
        <tr>
            <td style="border: none; text-align: center;">
                <div class="firma-line">Firma del Padre/Madre/Apoderado</div>
                <div style="font-size: 7px;">DNI: {{ $padre?->dni ?? '' }}</div>
            </td>
            <td style="border: none; text-align: center;">
                <div class="firma-line">Firma del Director</div>
                <div style="font-size: 7px;">{{ $ie['director'] ?? '' }}</div>
            </td>
        </tr>
    </table>
</div>

<p style="font-size: 6.5px; color: #94A3B8; text-align: center; margin-top: 12px;">
    SIGEL-EGA — Generado {{ $generado_en->format('d/m/Y H:i') }} ·
    {{ $ie['nombre'] }} · Este documento tiene validez oficial al ser firmado y sellado.
</p>

</body>
</html>
