# Tests de SIGEL-EGA

## Estructura

- `tests/Feature/ActividadApiTest.php` — Tests de actividades (CRUD + validaciones)
- `tests/Feature/AuthApiTest.php` — Tests de auth (login / me)
- `tests/Feature/BimestreCerradoTest.php` — Bloqueo de bimestre cerrado (403)
- `tests/Feature/CalificacionActividadTest.php` — Validación de niveles de logro
- `tests/Unit/PromedioCompetenciaTest.php` — Conversión MINEDU (RVM 094-2020, Anexo 09)
- `tests/Traits/TransaccionalTest.php` — Trait que usa `DatabaseTransactions` (rollback por test)
- `tests/Legacy/` — Tests viejos de Breeze (deshabilitados, ver abajo)

## ⚠️ Reglas

- **NUNCA usar `RefreshDatabase`** — usa el trait `TransaccionalTest`. `RefreshDatabase`
  ejecuta `migrate:fresh` y **borraría la BD real `sigel_ega`**.
- **SIEMPRE verificar que `phpunit.xml` apunta a la BD correcta**
  (`DB_CONNECTION=mysql`, `DB_DATABASE=sigel_ega`).
- **SIEMPRE hacer backup antes de correr tests nuevos**: `php artisan sigel:backup`.
- Correr con `--filter`: `php artisan test --filter=ActividadApiTest`.

## Tests legacy

Los archivos en `tests/Legacy/` son los tests originales de Breeze. Fueron movidos fuera
de `tests/Feature` y `tests/Unit` (no están en los testsuites de `phpunit.xml`), se les
quitó el trait `RefreshDatabase` y cada clase hace `markTestSkipped()` en `setUp()`.
Doble protección: aunque alguien los ejecute por ruta explícita, se omiten sin tocar la BD.
