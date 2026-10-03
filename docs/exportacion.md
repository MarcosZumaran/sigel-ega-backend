## Exportación de documentos

### Formatos disponibles

| Documento | PDF | Excel | Word |
|-----------|-----|-------|------|
| Acta de evaluación | ✅ | — | ✅ |
| Nómina de matrícula | ✅ | — | ✅ |
| Orden de mérito | ✅ | — | ✅ |
| FUM | ✅ | — | ✅ |
| Boletín / Informe | ✅ | — | ✅ |

### Endpoints

**PDF (existentes):**
- `GET /reportes/acta-evaluacion?seccion_id=X&periodo_id=Y`
- `GET /reportes/nomina-matricula?seccion_id=X&periodo_id=Y`
- `GET /reportes/orden-merito?grado_id=X&periodo_id=Y`
- `GET /estudiantes/{id}/fum.pdf`

**Word (nuevos):**
- `GET /reportes/acta-evaluacion.docx?seccion_id=X&periodo_id=Y`
- `GET /reportes/nomina-matricula.docx?seccion_id=X&periodo_id=Y`
- `GET /reportes/orden-merito.docx?grado_id=X&periodo_id=Y`
- `GET /estudiantes/{id}/fum.docx`
- `GET /estudiantes/{id}/informe-progreso.docx`

### Generación programática (WordBuilderService)

Todos los documentos oficiales se construyen de forma programática
(`ActaWordService`, `NominaWordService`, `OrdenWordService`, `BoletaWordService`,
`FumService::generarDocx` sobre `WordBuilderService`): membrete, tablas con
colores CNEB, firmas y pie. `WordExportService::desdeVista()` queda solo como
fallback genérico HTML→DOCX (ya sin usos en documentos oficiales).

### Limitaciones de Word (.docx)
- Tablas simples: OK
- Estilos CSS complejos: parcialmente soportados
- Colores de fondo: soportados si están inline
- Imágenes: NO soportadas (ext-gd ausente)

Para documentos con imágenes complejas, usar PDF.

### Notas de implementación
- Conversión vía `phpoffice/phpword` (`Html::addHtml`) en `App\Services\WordExportService`.
- Las vistas Blade se reducen a su fragmento `<body>` (sin `<style>` ni etiquetas sin cerrar) antes de convertir.
- Los avisos `libxml` y `E_DEPRECATED` de phpword 1.4 en PHP ≥ 8.4 se aíslan dentro del servicio para no ensuciar el log.
- Instalado con `--ignore-platform-req=ext-gd` (igual que `spatie/laravel-backup`).
- `composer audit` reporta 2 avisos (medium + high) en `league/commonmark` (dependencia transitiva), sin fix disponible en la rama 1.x.
- Archivos temporales `storage/app/private/tmp_*.docx` (ignorados por git, eliminados tras la descarga).
