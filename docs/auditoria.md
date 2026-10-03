## Cobertura de auditoría

### Modelos auditados (27)
- Todos los modelos de negocio y catálogos
- **Actualización Fase 2.2:** `ConclusionDescriptiva` y `NivelLogroConsolidado`
  ahora también se auditan (antes eran las 2 únicas tablas CNEB sin cobertura).

### Modelos NO auditados (justificado)
- `Auditoria` (evita recursión)
- `NormaDetectada` (se genera automáticamente, no editable)
- `AsistenciaPersonalArchivo` (metadatos de archivos)

### Verificación
Cualquier operación (create/update/delete/restore) sobre las tablas CNEB
deja rastro en `auditorias` con usuario, fecha y cambios.
