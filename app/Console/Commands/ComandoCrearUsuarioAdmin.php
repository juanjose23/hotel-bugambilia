<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\Console\Command\Command as CommandAlias;
use Throwable;

class ComandoCrearUsuarioAdmin extends Command
{
    protected $signature = 'dev:crear-usuario
        {--email=admin@hotel.com}
        {--password=12345678}
        {--nombre=Admin}
        {--telefono= : Telefono opcional}';

    protected $description = 'Crea un usuario con su persona relacionada (bootstrap dev)';

    /**
     * @throws Throwable
     */
    public function handle(): int
    {
        $email = $this->option('email');
        $password = $this->option('password');
        $nombre = $this->option('nombre');

        DB::beginTransaction();

        try {

            /**
             *  2. CREAR USER
             */
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'persona_id' => 1,
                    'name' => $nombre,
                    'password' => Hash::make((string) $password),
                    'email_verified_at' => now(),
                ]
            );

            DB::commit();

            $this->info('Usuario creado correctamente');
            $this->line("Email: $user->email");

            return CommandAlias::SUCCESS;

        } catch (Throwable $e) {
            DB::rollBack();

            $this->error('Error creando usuario: '.$e->getMessage());

            return CommandAlias::FAILURE;
        }
    }
}
