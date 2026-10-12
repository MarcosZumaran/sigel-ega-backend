<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Fix BUG-05: hacer que los índices UNIQUE ignoren registros soft-deleted.
     *
     * Técnica: columna generada STORED con CASE WHEN deleted_at IS NULL
     * THEN {columna} ELSE NULL. Los NULLs no colisionan en UNIQUE de MySQL/MariaDB.
     *
     * Afecta: estudiantes.dni, estudiantes.codigo_estudiante, padres.dni,
     * docentes.dni, personal.dni, users.email.
     */
    public function up(): void
    {
        $this->aplicarFix('estudiantes', 'dni', 'estudiantes_dni_unique');
        $this->aplicarFix('estudiantes', 'codigo_estudiante', 'estudiantes_codigo_estudiante_unique');
        $this->aplicarFix('padres', 'dni', 'padres_dni_unique');
        $this->aplicarFix('docentes', 'dni', 'docentes_dni_unique');
        $this->aplicarFix('personal', 'dni', 'personal_dni_unique');
        $this->aplicarFix('users', 'email', 'users_email_unique');
    }

    private function aplicarFix(string $tabla, string $columna, string $indiceOriginal): void
    {
        $columnaGenerada = $columna.'_activo';

        // 1. Verificar que el índice original existe
        $existe = DB::select(
            "SHOW INDEX FROM {$tabla} WHERE Key_name = ?",
            [$indiceOriginal]
        );

        if (empty($existe)) {
            \Log::warning("[migration] Índice {$indiceOriginal} no encontrado, saltando");

            return;
        }

        // 2. Drop el índice original
        DB::statement("ALTER TABLE {$tabla} DROP INDEX {$indiceOriginal}");

        // 3. Agregar columna generada (si no existe)
        $columnaExiste = DB::select(
            "SHOW COLUMNS FROM {$tabla} WHERE Field = ?",
            [$columnaGenerada]
        );

        if (empty($columnaExiste)) {
            DB::statement("
                ALTER TABLE {$tabla}
                ADD COLUMN {$columnaGenerada} VARCHAR(255)
                GENERATED ALWAYS AS (
                    CASE WHEN deleted_at IS NULL THEN {$columna} ELSE NULL END
                ) STORED
            ");
        }

        // 4. Crear nuevo índice UNIQUE en la columna generada
        DB::statement("
            CREATE UNIQUE INDEX {$indiceOriginal} ON {$tabla}({$columnaGenerada})
        ");
    }

    public function down(): void
    {
        // ⚠️ El down() es riesgoso: si hay múltiples soft-deleted con el mismo valor,
        // recrear el unique original fallaría. Se documenta en el log.

        $reversiones = [
            ['estudiantes', 'dni', 'estudiantes_dni_unique'],
            ['estudiantes', 'codigo_estudiante', 'estudiantes_codigo_estudiante_unique'],
            ['padres', 'dni', 'padres_dni_unique'],
            ['docentes', 'dni', 'docentes_dni_unique'],
            ['personal', 'dni', 'personal_dni_unique'],
            ['users', 'email', 'users_email_unique'],
        ];

        foreach ($reversiones as [$tabla, $columna, $indice]) {
            $columnaGenerada = $columna.'_activo';

            DB::statement("ALTER TABLE {$tabla} DROP INDEX {$indice}");
            DB::statement("ALTER TABLE {$tabla} DROP COLUMN {$columnaGenerada}");

            // Intento de recrear el unique original
            try {
                DB::statement("CREATE UNIQUE INDEX {$indice} ON {$tabla}({$columna})");
            } catch (\Exception $e) {
                \Log::error("[migration down] No se pudo recrear {$indice}: ".$e->getMessage());
                throw new \RuntimeException(
                    "Rollback falló en {$tabla}.{$columna}: hay duplicados. ".
                    'Restaurar desde backup.'
                );
            }
        }
    }
};
