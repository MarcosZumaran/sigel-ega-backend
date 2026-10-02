## Trazabilidad SIAGIE (RF-29)

### Registro automático
Toda importación y exportación SIAGIE se registra automáticamente en la tabla
`siagie_logs` con:
- Usuario, operación (import/export), archivo, formato
- Periodo, sección
- Filas procesadas/exitosas/con error
- Errores detallados (JSON)
- Resultado: exitoso / parcial / fallido
- Duración en milisegundos
- IP del cliente

### Consultar logs
- `GET /api/siagie/logs` — Lista paginada con filtros
- `GET /api/siagie/logs/{id}` — Detalle con errores

Filtros disponibles:
- `operacion=import|export`
- `resultado=exitoso|parcial|fallido`
- `periodo_id=X`
- `desde=YYYY-MM-DD`, `hasta=YYYY-MM-DD`

### Determinación del resultado
- Sin filas procesadas → **fallido**
- 0 errores → **exitoso**
- Con errores pero >0 exitosas → **parcial**
- Con errores y 0 exitosas → **fallido**
