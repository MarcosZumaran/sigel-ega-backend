<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Ficha Única de Matrícula</title>
    @include('reportes.partials.estilos', ['orientacion' => 'vertical'])
</head>
<body>

@include('reportes.partials.membrete', [
    'titulo' => 'FICHA ÚNICA DE MATRÍCULA',
    'subtitulo' => 'Año escolar: ' . ($periodo?->anio ?? date('Y')),
    'orientacion' => 'vertical',
])

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
