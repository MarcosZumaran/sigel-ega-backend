<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Resetear caché de permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Crear permisos
        $permisos = [
            'ver estudiantes', 'crear estudiantes', 'editar estudiantes', 'eliminar estudiantes',
            'ver matriculas', 'crear matriculas', 'editar matriculas', 'eliminar matriculas',
            'ver calificaciones', 'crear calificaciones', 'editar calificaciones', 'eliminar calificaciones',
            'ver reportes', 'generar reportes',
            'ver documentos', 'crear documentos', 'editar documentos', 'eliminar documentos',
            'administrar usuarios', 'administrar roles',
            'administrar configuracion',
            'ver auditoria',
        ];

        foreach ($permisos as $permiso) {
            Permission::firstOrCreate(['name' => $permiso]);
        }

        // Crear roles
        $admin = Role::firstOrCreate(['name' => 'ADMIN']);
        $docente = Role::firstOrCreate(['name' => 'DOCENTE']);
        $director = Role::firstOrCreate(['name' => 'DIRECTOR']);

        // Asignar todos los permisos al ADMIN
        $admin->syncPermissions(Permission::all());

        // Permisos del DOCENTE (solo lo académico)
        $docente->syncPermissions([
            'ver estudiantes',
            'ver calificaciones', 'crear calificaciones', 'editar calificaciones',
            'ver reportes', 'generar reportes',
            'ver documentos', 'crear documentos',
        ]);

        // Permisos del DIRECTOR (casi todo excepto administración de usuarios)
        $director->syncPermissions([
            'ver estudiantes', 'crear estudiantes', 'editar estudiantes',
            'ver matriculas', 'crear matriculas', 'editar matriculas',
            'ver calificaciones', 'editar calificaciones',
            'ver reportes', 'generar reportes',
            'ver documentos', 'crear documentos', 'editar documentos',
            'ver auditoria',
        ]);

        // Asignar rol ADMIN al usuario existente admin@ega.edu.pe
        $adminUser = User::where('email', 'admin@ega.edu.pe')->first();
        if ($adminUser) {
            $adminUser->assignRole('ADMIN');
        }

        $this->command->info('Roles y permisos creados correctamente.');
    }
}
