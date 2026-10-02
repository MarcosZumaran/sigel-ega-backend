<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

#[Signature('sigel:rotar-password-admin {--password=}')]
#[Description('Rota la contraseña del admin y fuerza cambio en primer login')]
class RotarPasswordAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = User::where('email', 'admin@ega.edu.pe')->first();
        if (! $user) {
            $this->error('Usuario admin no encontrado.');

            return 1;
        }

        $newPassword = $this->option('password') ?: Str::password(16);

        // NOTA: no usar Hash::make aquí; el cast 'hashed' del modelo lo hace.
        $user->password = $newPassword;
        $user->save();

        $this->info("Contraseña rotada para {$user->email}");
        $this->warn("NUEVA CONTRASEÑA: {$newPassword}");
        $this->warn('Guárdala en un lugar seguro y cámbiala en el primer login.');

        return 0;
    }
}
