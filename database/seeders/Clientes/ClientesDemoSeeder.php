<?php

declare(strict_types=1);

namespace Database\Seeders\Clientes;

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

final class ClientesDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tipoRegular = Catalogo::where('codigo', 'CLI_REGULAR')->first();
        $tipoCorporativo = Catalogo::where('codigo', 'CLI_CORPORATIVO')->first();
        $tipoVIP = Catalogo::where('codigo', 'CLI_VIP')->first();

        // ─── 0. Cliente genérico "Público General" (mostrador) ───
        DB::transaction(function () use ($tipoRegular): void {
            if (Persona::where('primer_nombre', 'Público')->where('segundo_nombre', 'General')->exists()) {
                return;
            }

            if ($tipoRegular === null) {
                return;
            }

            $persona = Persona::create([
                'primer_nombre' => 'Público',
                'segundo_nombre' => 'General',
                'tipo_persona' => 'natural',
                'telefono' => null,
                'direccion' => null,
            ]);
            PersonaNatural::create([
                'persona_id' => $persona->id,
                'primer_apellido' => 'General',
                'segundo_apellido' => null,
                'tipo_identificacion' => null,
                'numero_identificacion' => null,
                'sexo' => null,
            ]);
            Cliente::create(['persona_id' => $persona->id, 'catalogo_id' => $tipoRegular->id, 'estado' => 1]);
        });

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

        // ─── 7. Clientes Corporativos B2B con Convenio y RUC ───
        $clientesCorporativos = [
            [
                'nombre' => 'Corporación Financiera Centroamericana S.A.',
                'razon' => 'Corporación Financiera Centroamericana Sociedad Anónima',
                'ruc' => 'J0310000012345',
                'email' => 'finanzas@corpfinanciera.com',
                'telefono' => '+505 2278 9000',
                'direccion' => 'Edificio Pellas, Piso 8, Managua',
            ],
            [
                'nombre' => 'Consultores & Auditores Asociados S.A.',
                'razon' => 'Consultores & Auditores Asociados Sociedad Anónima',
                'ruc' => 'J0410000067890',
                'email' => 'admin@consultoresasoc.com',
                'telefono' => '+505 2255 4321',
                'direccion' => 'Plaza España 2c. al Norte, Managua',
            ],
            [
                'nombre' => 'Destinos Nica Touroperador S.A.',
                'razon' => 'Destinos Nica Touroperador Sociedad Anónima',
                'ruc' => 'J0510000099881',
                'email' => 'reservas@destinosnica.com',
                'telefono' => '+505 2552 1100',
                'direccion' => 'Calle Real Xalteva, Granada',
            ],
            [
                'nombre' => 'Constructora Midence & Asociados S.A.',
                'razon' => 'Constructora Midence & Asociados Sociedad Anónima',
                'ruc' => 'J0210000044332',
                'email' => 'compras@midence.com',
                'telefono' => '+505 2713 5500',
                'direccion' => 'Salida Sur, Contiguo a Enacal, Estelí',
            ],
        ];

        foreach ($clientesCorporativos as $corp) {
            DB::transaction(function () use ($corp, $tipoCorporativo): void {
                if (PersonaJuridica::where('numero_identificacion', $corp['ruc'])->exists()) {
                    return;
                }

                $persona = Persona::create([
                    'primer_nombre' => $corp['nombre'],
                    'tipo_persona' => 'juridica',
                    'telefono' => $corp['telefono'],
                    'direccion' => $corp['direccion'],
                ]);

                PersonaJuridica::create([
                    'persona_id' => $persona->id,
                    'razon_social' => $corp['razon'],
                    'tipo_identificacion' => 'ruc',
                    'numero_identificacion' => $corp['ruc'],
                ]);

                Cliente::create([
                    'persona_id' => $persona->id,
                    'catalogo_id' => $tipoCorporativo->id ?? 1,
                    'estado' => 1,
                ]);

                User::create([
                    'persona_id' => $persona->id,
                    'name' => $corp['nombre'],
                    'email' => $corp['email'],
                    'password' => Hash::make('password123'),
                    'is_admin' => false,
                ]);
            });
        }

        // ─── 8. Turistas Internacionales con Pasaporte ───
        $turistas = [
            [
                'primer_nombre' => 'John',
                'segundo_nombre' => 'William',
                'primer_apellido' => 'Smith',
                'segundo_apellido' => null,
                'pasaporte' => 'USA-987654321',
                'sexo' => 'M',
                'email' => 'john.smith@globetrotter.com',
                'telefono' => '+1 305 555 0199',
                'direccion' => 'Miami, Florida, United States',
            ],
            [
                'primer_nombre' => 'Carlos',
                'segundo_nombre' => 'Alberto',
                'primer_apellido' => 'Monge',
                'segundo_apellido' => 'Solano',
                'pasaporte' => 'CR-55443322',
                'sexo' => 'M',
                'email' => 'carlitos.monge@puravida.cr',
                'telefono' => '+506 8877 6655',
                'direccion' => 'San José, Curridabat, Costa Rica',
            ],
            [
                'primer_nombre' => 'Beatriz',
                'segundo_nombre' => 'Elena',
                'primer_apellido' => 'Morales',
                'segundo_apellido' => 'Garrido',
                'pasaporte' => 'ESP-88776655',
                'sexo' => 'F',
                'email' => 'beatriz.morales@viajesiberia.es',
                'telefono' => '+34 612 345 678',
                'direccion' => 'Paseo de la Castellana 45, Madrid, España',
            ],
        ];

        foreach ($turistas as $tur) {
            DB::transaction(function () use ($tur, $tipoRegular): void {
                if (PersonaNatural::where('numero_identificacion', $tur['pasaporte'])->exists()) {
                    return;
                }

                $persona = Persona::create([
                    'primer_nombre' => $tur['primer_nombre'],
                    'segundo_nombre' => $tur['segundo_nombre'],
                    'tipo_persona' => 'natural',
                    'telefono' => $tur['telefono'],
                    'direccion' => $tur['direccion'],
                ]);

                PersonaNatural::create([
                    'persona_id' => $persona->id,
                    'primer_apellido' => $tur['primer_apellido'],
                    'segundo_apellido' => $tur['segundo_apellido'],
                    'tipo_identificacion' => 'pasaporte',
                    'numero_identificacion' => $tur['pasaporte'],
                    'sexo' => $tur['sexo'],
                ]);

                Cliente::create([
                    'persona_id' => $persona->id,
                    'catalogo_id' => $tipoRegular->id ?? 1,
                    'estado' => 1,
                ]);

                User::create([
                    'persona_id' => $persona->id,
                    'name' => "{$tur['primer_nombre']} {$tur['primer_apellido']}",
                    'email' => $tur['email'],
                    'password' => Hash::make('password123'),
                    'is_admin' => false,
                ]);
            });
        }

        // ─── 9. Clientes VIP Platino / Oro y Clientes Frecuentes ───
        $clientesVipYLocales = [
            [
                'primer_nombre' => 'Fernando',
                'segundo_nombre' => 'José',
                'primer_apellido' => 'Solís',
                'segundo_apellido' => 'Caldera',
                'cedula' => '001-140280-0004A',
                'sexo' => 'M',
                'tipo_catalogo' => $tipoVIP?->id,
                'email' => 'fernando.solis@soliscorp.com',
                'telefono' => '+505 8444 7777',
                'direccion' => 'Villa Fontana Sur, Managua',
            ],
            [
                'primer_nombre' => 'Claudia',
                'segundo_nombre' => 'Marcela',
                'primer_apellido' => 'Vega',
                'segundo_apellido' => 'Reyes',
                'cedula' => '161-220785-0002L',
                'sexo' => 'F',
                'tipo_catalogo' => $tipoVIP?->id,
                'email' => 'claudia.vega@medicos.com',
                'telefono' => '+505 8999 3322',
                'direccion' => 'Reparto Los Robles, Managua',
            ],
            [
                'primer_nombre' => 'Gabriel',
                'segundo_nombre' => 'Antonio',
                'primer_apellido' => 'Rivas',
                'segundo_apellido' => 'Torres',
                'cedula' => '001-050992-0009M',
                'sexo' => 'M',
                'tipo_catalogo' => $tipoRegular?->id,
                'email' => 'gabriel.rivas@restaurante.com',
                'telefono' => '+505 8777 4411',
                'direccion' => 'Barrio Boris Vega, Estelí',
            ],
            [
                'primer_nombre' => 'Lucía',
                'segundo_nombre' => 'Valentina',
                'primer_apellido' => 'Barreto',
                'segundo_apellido' => 'Mendoza',
                'cedula' => '401-180499-0001B',
                'sexo' => 'F',
                'tipo_catalogo' => $tipoRegular?->id,
                'email' => 'lucia.barreto@gmail.com',
                'telefono' => '+505 8666 2200',
                'direccion' => 'Barrio Juno Rodríguez, Estelí',
            ],
        ];

        foreach ($clientesVipYLocales as $cli) {
            DB::transaction(function () use ($cli, $tipoRegular): void {
                if (PersonaNatural::where('numero_identificacion', $cli['cedula'])->exists()) {
                    return;
                }

                $persona = Persona::create([
                    'primer_nombre' => $cli['primer_nombre'],
                    'segundo_nombre' => $cli['segundo_nombre'],
                    'tipo_persona' => 'natural',
                    'telefono' => $cli['telefono'],
                    'direccion' => $cli['direccion'],
                ]);

                PersonaNatural::create([
                    'persona_id' => $persona->id,
                    'primer_apellido' => $cli['primer_apellido'],
                    'segundo_apellido' => $cli['segundo_apellido'],
                    'tipo_identificacion' => 'cedula',
                    'numero_identificacion' => $cli['cedula'],
                    'sexo' => $cli['sexo'],
                ]);

                Cliente::create([
                    'persona_id' => $persona->id,
                    'catalogo_id' => $cli['tipo_catalogo'] ?? $tipoRegular->id ?? 1,
                    'estado' => 1,
                ]);

                User::create([
                    'persona_id' => $persona->id,
                    'name' => "{$cli['primer_nombre']} {$cli['primer_apellido']}",
                    'email' => $cli['email'],
                    'password' => Hash::make('password123'),
                    'is_admin' => false,
                ]);
            });
        }

        $this->command->info('Clientes demo (naturales, jurídicos, corporativos B2B, turistas y conflictos de identidad) creados.');
    }
}
