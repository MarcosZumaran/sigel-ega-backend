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

## Respaldos (RF-30, RF-31)

### Backup manual
```bash
php artisan sigel:backup
# Con destino externo (USB):
php artisan sigel:backup --destino=/media/usb-sigel
```

### Restauración
```bash
php artisan sigel:restore /ruta/al/backup.zip
# Sin confirmación (PELIGROSO):
php artisan sigel:restore /ruta/al/backup.zip --force
```

### Backup automático
- Diario a las 23:00 (configurado en `routes/console.php`)
- Limpieza semanal (domingos 02:00)
- Retención: 30 días todo, 7 días diarios, 4 semanas, 6 meses, 1 año
- Máximo: 5 GB de backups acumulados
- Requisito: Cron activo en producción. Agregar a crontab:
```
* * * * * cd /ruta/sigel-ega-backend && php artisan schedule:run >> /dev/null 2>&1
```

### Ubicación de backups
- Principal: `storage/app/private/{APP_NAME}-{APP_ENV}/`
- Externo: según `--destino`

### Prueba de restauración recomendada
Cada mes, verificar que un backup se puede restaurar en entorno de prueba.
