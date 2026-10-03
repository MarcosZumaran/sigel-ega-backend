## Cobertura de auditoría

### Modelos auditados (27)
- Todos los modelos de negocio y catálogos
- **Actualización Fase 2.1:** `ConclusionDescriptiva` y `NivelLogroConsolidado`
  ahora también se auditan (antes eran las 2 únicas tablas CNEB sin cobertura).
- **Actualización Fase 2.2:** `Bimestre` también se audita.

### Modelos NO auditados (justificado)
- `Auditoria` (evita recursión)
- `NormaDetectada` (se genera automáticamente, no editable)
- `AsistenciaPersonalArchivo` (metadatos de archivos)

### Verificación
Cualquier operación (create/update/delete/restore) sobre las tablas CNEB
deja rastro en `auditorias` con usuario, fecha y cambios.

## Auditoría SIAGIE

### Doble trazabilidad
Cada operación SIAGIE deja rastro en 2 tablas:

1. **`siagie_logs`** (tabla dedicada):
   - operación (import/export)
   - archivo, formato, periodo, sección
   - filas procesadas/exitosas/con error
   - errores detallados (JSON)
   - duración, IP, resultado

2. **`auditorias`** (sistema general, vía trait Auditable):
   - creado/modificado/eliminado del registro en `siagie_logs`
   - payload con cambios

### Captura de usuario
`SiagieLogService` resuelve `usuario_id` como `$data['usuario_id'] ?? Auth::id()`.
En peticiones HTTP autenticadas queda el usuario real; en consola/tinker queda
`null` con `origen=console` (caso de las 6 filas de verificación de Fase 1A.3).

### Consultas útiles
- Ver últimas importaciones: `SiagieLog::where('operacion','import')->latest()->take(10)->get()`
- Ver errores recientes: `SiagieLog::where('resultado','fallido')->latest()->get()`
- Auditoría detallada: `Auditoria::where('tabla','siagie_logs')->latest()->get()`
