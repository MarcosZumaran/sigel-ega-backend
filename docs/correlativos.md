## Numeración Correlativa (RF-19)

### Formato
Los números se generan automáticamente con el patrón:
`{TIPO} N° {numero:03d}-{anio}-IE-EGA`

Ejemplo: `OFICIO N° 001-2026-IE-EGA`

### Configuración
Cada tipo de documento tiene su formato en la tabla `correlativos`:
- Un registro por (tipo_documento_id, anio)
- Se resetea automáticamente cada año
- El formato es personalizable editando el campo `formato`

### Uso desde API
- **Crear documento sin número**: enviar sin `numero` → se genera automáticamente.
- **Previsualizar próximo**: `GET /api/documentos/proximo-numero?tipo_documento_id=X`
- **Crear documento con número manual**: enviar `numero` → se respeta (útil para migraciones).
