<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Usuarios\EstadoConflictoIdentidad;
use App\Enums\Usuarios\TipoConflictoIdentidad;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaJuridica;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\User;
use App\Repository\Models\Usuarios\ConflictoIdentidad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ClientesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tipoRegular = Catalogo::where('codigo', 'CLI_REGULAR')->first();
        $tipoCorporativo = Catalogo::where('codigo', 'CLI_CORPORATIVO')->first();
        $tipoVIP = Catalogo::where('codigo', 'CLI_VIP')->first();

        // ─── 1. Cliente Natural con usuario ───
        DB::transaction(function () use ($tipoRegular) {
            if (User::where('email', 'ana.lopez@email.com')->exists()) {
                return;
            }
            $persona = Persona::create([
                'primer_nombre' => 'Ana María',
                'segundo_nombre' => 'de Jesús',
                'tipo_persona' => 'natural',
                'telefono' => '+505 8888 1111',
                'direccion' => 'Del Parque Central 1c. al Sur, Estelí',
            ]);
            PersonaNatural::create([
                'persona_id' => $persona->id,
                'primer_apellido' => 'López',
                'segundo_apellido' => 'Martínez',
                'tipo_identificacion' => 'cedula',
                'numero_identificacion' => '4011506840003F',
                'sexo' => 'F',
            ]);
            Cliente::create(['persona_id' => $persona->id, 'catalogo_id' => $tipoRegular->id ?? 1, 'estado' => 1]);
            User::create([
                'persona_id' => $persona->id, 'name' => 'Ana López',
                'email' => 'ana.lopez@email.com', 'password' => Hash::make('password123'),
                'is_admin' => false,
            ]);
        });

        // ─── 2. Cliente Empresa (Persona Jurídica) sin usuario ───
        DB::transaction(function () use ($tipoCorporativo) {
            if (Persona::where('primer_nombre', 'Hotel Hacienda Real S.A.')->exists()) {
                return;
            }
            $persona = Persona::create([
                'primer_nombre' => 'Hotel Hacienda Real S.A.',
                'tipo_persona' => 'juridica',
                'telefono' => '+505 2222 3333',
                'direccion' => 'Km 148 Carretera Panamericana, Estelí',
            ]);
            PersonaJuridica::create([
                'persona_id' => $persona->id,
                'razon_social' => 'Hotel Hacienda Real Sociedad Anónima',
                'tipo_identificacion' => 'ruc',
                'numero_identificacion' => 'J0310000005678',
            ]);
            Cliente::create(['persona_id' => $persona->id, 'catalogo_id' => $tipoCorporativo->id ?? 1, 'estado' => 1]);
        });

        // ─── 3. Cliente VIP con usuario ───
        DB::transaction(function () use ($tipoVIP) {
            if (User::where('email', 'roberto.castro@email.com')->exists()) {
                return;
            }
            $persona = Persona::create([
                'primer_nombre' => 'Roberto Carlos',
                'tipo_persona' => 'natural',
                'telefono' => '+505 9999 0000',
                'direccion' => 'Residencial Las Colinas, Casa #5, Estelí',
            ]);
            PersonaNatural::create([
                'persona_id' => $persona->id,
                'primer_apellido' => 'Castro',
                'segundo_apellido' => 'González',
                'tipo_identificacion' => 'cedula',
                'numero_identificacion' => '4012505840004K',
                'sexo' => 'M',
            ]);
            Cliente::create(['persona_id' => $persona->id, 'catalogo_id' => $tipoVIP->id ?? 1, 'estado' => 1]);
            User::create([
                'persona_id' => $persona->id, 'name' => 'Roberto Castro',
                'email' => 'roberto.castro@email.com', 'password' => Hash::make('password123'),
                'is_admin' => false,
            ]);
        });

        // ─── 4. Conflicto de identidad: Homonimia ───
        DB::transaction(function () {
            $existente = PersonaNatural::where('numero_identificacion', '4011506840003F')->first();

            if ($existente && ! ConflictoIdentidad::where('persona_id', $existente->persona_id)->where('tipo_conflicto', TipoConflictoIdentidad::Homonimia->value)->exists()) {
                ConflictoIdentidad::create([
                    'persona_id' => $existente->persona_id,
                    'tipo_conflicto' => TipoConflictoIdentidad::Homonimia,
                    'datos_providos' => [
                        'primer_nombre' => 'Juana',
                        'primer_apellido' => 'Pérez',
                        'tipo_identificacion' => 'cedula',
                        'numero_identificacion' => '401-150684-0003F',
                        'telefono' => '+505 7777 2222',
                    ],
                    'datos_existentes' => [
                        'primer_nombre' => $existente->primer_nombre ?? '',
                        'primer_apellido' => $existente->primer_apellido ?? '',
                        'tipo_identificacion' => $existente->tipo_identificacion ?? '',
                        'numero_identificacion' => $existente->numero_identificacion ?? '',
                    ],
                    'estado' => EstadoConflictoIdentidad::Pendiente,
                ]);
            }
        });

        // ─── 5. Conflicto: Datos Divergentes (mismo ID, nombre similar) ───
        DB::transaction(function () {
            $existente = PersonaNatural::where('numero_identificacion', '4012505840004K')->first();

            if ($existente && ! ConflictoIdentidad::where('persona_id', $existente->persona_id)->where('tipo_conflicto', TipoConflictoIdentidad::DatosDivergentes->value)->exists()) {
                ConflictoIdentidad::create([
                    'persona_id' => $existente->persona_id,
                    'tipo_conflicto' => TipoConflictoIdentidad::DatosDivergentes,
                    'datos_providos' => [
                        'primer_nombre' => 'Roberto',
                        'primer_apellido' => 'Castro',
                        'tipo_identificacion' => 'cedula',
                        'numero_identificacion' => '4012505840004K',
                        'telefono' => '+505 5555 4444',
                        'direccion' => 'Barrio El Calvario, Estelí',
                    ],
                    'datos_existentes' => [
                        'primer_nombre' => $existente->primer_nombre ?? '',
                        'primer_apellido' => $existente->primer_apellido ?? '',
                        'tipo_identificacion' => $existente->tipo_identificacion ?? '',
                        'numero_identificacion' => $existente->numero_identificacion ?? '',
                    ],
                    'estado' => EstadoConflictoIdentidad::Pendiente,
                ]);
            }
        });

        // ─── 6. Cliente Jurídico con usuario ───
        DB::transaction(function () use ($tipoCorporativo) {
            if (User::where('email', 'reservas@haciendareal.com')->exists()) {
                return;
            }
            $persona = Persona::create([
                'primer_nombre' => 'Turismo del Norte S.A.',
                'tipo_persona' => 'juridica',
                'telefono' => '+505 2777 8888',
                'direccion' => 'Centro Comercial Plaza Real, Local #12, Estelí',
            ]);
            PersonaJuridica::create([
                'persona_id' => $persona->id,
                'razon_social' => 'Turismo del Norte Sociedad Anónima',
                'tipo_identificacion' => 'nit',
                'numero_identificacion' => 'J0412000009876',
            ]);
            Cliente::create(['persona_id' => $persona->id, 'catalogo_id' => $tipoCorporativo->id ?? 1, 'estado' => 1]);
            User::create([
                'persona_id' => $persona->id, 'name' => 'Turismo del Norte',
                'email' => 'reservas@haciendareal.com', 'password' => Hash::make('password123'),
                'is_admin' => false,
            ]);
        });

        $this->command->info('Clientes demo (naturales, jurídicos y conflictos de identidad) creados.');
    }
}
