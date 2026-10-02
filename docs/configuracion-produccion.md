# Configuración de producción — SIGEL-EGA backend

Variables que deben cambiarse en `.env` antes de exponer el servidor:

```ini
APP_ENV=production
APP_DEBUG=false
LOG_CHANNEL=daily
LOG_LEVEL=warning
LOG_DAILY_DAYS=14
ADMIN_INITIAL_PASSWORD=<clave-inicial-aleatoria>
```

## Por qué `APP_DEBUG=false`

Con `APP_DEBUG=true`, cualquier error 500 devuelve el stack trace completo
al cliente: rutas de archivos, fragmentos de código, variables de entorno y
consultas SQL. En producción debe ser `false` para devolver solo un mensaje
genérico.

## Por qué `LOG_CHANNEL=daily`

El canal `single` escribe a un único `storage/logs/laravel.log` que crece sin
límite hasta llenar el disco. El canal `daily` crea un archivo por día
(`laravel-YYYY-MM-DD.log`) y conserva los últimos `LOG_DAILY_DAYS` (14).
`LOG_LEVEL=warning` evita verbosidad de `debug` en producción.

## Rotar la contraseña del admin

```bash
php artisan sigel:rotar-password-admin             # genera clave aleatoria
php artisan sigel:rotar-password-admin --password="NuevaClave123!"
```

Guardar la clave impresa en un lugar seguro y cambiarla en el primer login.

## Revocar tokens (cerrar todas las sesiones)

```bash
php artisan tinker --execute="App\Models\User::where('email','admin@ega.edu.pe')->first()->tokens()->delete();"
```

## Backup (pendiente — Fase 1)

Aún no existe estrategia de respaldo automatizado. Antes del despliegue
definitivo, definir: `mysqldump` programado de `sigel_ega` + copia de
`storage/app/*`, con retención y prueba de restauración.
