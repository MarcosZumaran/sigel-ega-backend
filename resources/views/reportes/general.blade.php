<!DOCTYPE html><html lang="es"><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans, sans-serif; font-size:10px; color:#1e293b}h1{color:#1E3A8A; font-size:16px; border-bottom:2px solid #1E3A8A; padding-bottom:4px}table{width:100%; border-collapse:collapse; margin-top:8px}th{background:#1E3A8A; color:#fff; padding:4px 6px; text-align:left}td{padding:4px 6px; border-bottom:1px solid #e2e8f0}small{color:#64748b}</style></head><body>
<h1>SIGEL-EGA — Reporte {{ ucfirst($tipo) }}</h1>
<small>Generado: {{ $generado_en }} | Hash: {{ substr($hash,0,12) }} | Periodo: {{ $periodo->nombre ?? '—' }} @if($seccion) | Sección: {{ $seccion->grado->nombre ?? '' }}-{{ $seccion->nombre }} @endif</small>
<table><tr><th>Tipo</th><th>Generado</th><th>Matrículas</th><th>Asistencias</th><th>Notas</th></tr>
<tr><td>{{ $tipo }}</td><td>{{ $generado_en }}</td><td>{{ $matriculas->count() }}</td><td>{{ $asistencias->count() }}</td><td>{{ $notas->count() }}</td></tr></table>
<p style="margin-top:12px"><small>DejaVu Sans — tildes á é í ó ú ñ — CNEB AD/A/B/C</small></p>
</body></html>
