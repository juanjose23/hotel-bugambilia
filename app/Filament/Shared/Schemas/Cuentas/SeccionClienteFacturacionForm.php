<?php

declare(strict_types=1);

namespace App\Filament\Shared\Schemas\Cuentas;

use App\BusinessLogic\Personas\PersonaNatural\ValidCedulaNicaragua;
use App\Enums\Personas\TipoIdentificacion;
use App\Filament\Shared\Forms\SelectorCliente;
use App\Repository\Models\Clientes\Cliente;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;

final class SeccionClienteFacturacionForm
{
    public static function make(): Group
    {
        return Group::make([
            Grid::make(2)->schema([
                Select::make('tipo_comprobante')
                    ->label('Tipo de Comprobante')
                    ->options([
                        'voucher' => 'Voucher / Ticket de Consumo',
                        'factura_empresarial' => 'Factura Fiscal (Empresa o Persona Natural)',
                    ])
                    ->default('voucher')
                    ->required()
                    ->native(false)
                    ->live()
                    ->afterStateUpdated(function (mixed $state, Get $get, Set $set): void {
                        if ($state === 'factura_empresarial') {
                            $clienteId = $get('cliente_id');
                            if (is_numeric($clienteId) && (blank($get('ruc_factura')) || blank($get('razon_social_factura')))) {
                                self::autocompletarDatosFiscales((int) $clienteId, $set);
                            }
                        }
                    }),

                SelectorCliente::single('cliente_id')
                    ->hidden(fn (Get $get): bool => (bool) $get('registrar_nuevo_cliente'))
                    ->live()
                    ->afterStateUpdated(function (mixed $state, Set $set): void {
                        if (is_numeric($state)) {
                            self::autocompletarDatosFiscales((int) $state, $set);
                        }
                    }),
            ]),

            Grid::make(2)->schema([
                TextInput::make('ruc_factura')
                    ->label('RUC / Identificación Fiscal')
                    ->placeholder('0010101900001A')
                    ->required(fn (Get $get): bool => $get('tipo_comprobante') === 'factura_empresarial')
                    ->visible(fn (Get $get): bool => $get('tipo_comprobante') === 'factura_empresarial'),

                TextInput::make('razon_social_factura')
                    ->label('Nombre o Razón Social')
                    ->placeholder('Nombre de la Empresa S.A. o Cliente')
                    ->required(fn (Get $get): bool => $get('tipo_comprobante') === 'factura_empresarial')
                    ->visible(fn (Get $get): bool => $get('tipo_comprobante') === 'factura_empresarial'),
            ]),

            Toggle::make('registrar_nuevo_cliente')
                ->label('Registrar cliente rápidamente')
                ->live(),

            Grid::make(2)->schema([
                Select::make('nuevo_cliente_tipo_persona')
                    ->label('Tipo de Cliente')
                    ->options([
                        'natural' => 'Persona Natural',
                        'juridica' => 'Persona Jurídica / Empresa',
                    ])
                    ->default('natural')
                    ->required(fn (Get $get): bool => (bool) $get('registrar_nuevo_cliente'))
                    ->native(false)
                    ->live()
                    ->columnSpan(2),

                // Campos Persona Natural
                TextInput::make('nuevo_cliente_nombre')
                    ->label('Nombres')
                    ->required(fn (Get $get): bool => (bool) $get('registrar_nuevo_cliente') && ($get('nuevo_cliente_tipo_persona') ?? 'natural') === 'natural')
                    ->visible(fn (Get $get): bool => ($get('nuevo_cliente_tipo_persona') ?? 'natural') === 'natural')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        $nombre = is_string($get('nuevo_cliente_nombre')) ? $get('nuevo_cliente_nombre') : '';
                        $apellido = is_string($get('nuevo_cliente_apellido')) ? $get('nuevo_cliente_apellido') : '';
                        $nom = trim("{$nombre} {$apellido}");
                        if (filled($nom)) {
                            $set('razon_social_factura', $nom);
                        }
                    })
                    ->columnSpan(1),

                TextInput::make('nuevo_cliente_apellido')
                    ->label('Apellidos')
                    ->required(fn (Get $get): bool => (bool) $get('registrar_nuevo_cliente') && ($get('nuevo_cliente_tipo_persona') ?? 'natural') === 'natural')
                    ->visible(fn (Get $get): bool => ($get('nuevo_cliente_tipo_persona') ?? 'natural') === 'natural')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        $nombre = is_string($get('nuevo_cliente_nombre')) ? $get('nuevo_cliente_nombre') : '';
                        $apellido = is_string($get('nuevo_cliente_apellido')) ? $get('nuevo_cliente_apellido') : '';
                        $nom = trim("{$nombre} {$apellido}");
                        if (filled($nom)) {
                            $set('razon_social_factura', $nom);
                        }
                    })
                    ->columnSpan(1),

                Select::make('nuevo_cliente_tipo_identificacion')
                    ->label('Tipo de Documento')
                    ->options(TipoIdentificacion::class)
                    ->default('cedula')
                    ->live()
                    ->native(false)
                    ->visible(fn (Get $get): bool => ($get('nuevo_cliente_tipo_persona') ?? 'natural') === 'natural')
                    ->columnSpan(1),

                TextInput::make('nuevo_cliente_identificacion')
                    ->label('Nro. de Identificación')
                    ->placeholder('Ej. 001-010190-0001A')
                    ->rules([
                        fn (Get $get) => in_array($get('nuevo_cliente_tipo_identificacion'), ['cedula', TipoIdentificacion::Cedula, TipoIdentificacion::Cedula->value], true)
                            ? new ValidCedulaNicaragua
                            : null,
                    ])
                    ->visible(fn (Get $get): bool => ($get('nuevo_cliente_tipo_persona') ?? 'natural') === 'natural')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (mixed $state, Set $set): void {
                        if (is_scalar($state) && filled($state)) {
                            $set('ruc_factura', (string) $state);
                        }
                    })
                    ->columnSpan(1),

                // Campos Persona Jurídica / Empresa
                TextInput::make('nuevo_cliente_razon_social')
                    ->label('Razón Social / Nombre Comercial')
                    ->placeholder('Ej: Distribuidora S.A.')
                    ->required(fn (Get $get): bool => (bool) $get('registrar_nuevo_cliente') && $get('nuevo_cliente_tipo_persona') === 'juridica')
                    ->visible(fn (Get $get): bool => $get('nuevo_cliente_tipo_persona') === 'juridica')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (mixed $state, Set $set): void {
                        if (is_scalar($state) && filled($state)) {
                            $set('razon_social_factura', (string) $state);
                        }
                    })
                    ->columnSpan(1),

                TextInput::make('nuevo_cliente_ruc')
                    ->label('Número RUC')
                    ->placeholder('Ej: J0310000000001')
                    ->required(fn (Get $get): bool => (bool) $get('registrar_nuevo_cliente') && $get('nuevo_cliente_tipo_persona') === 'juridica')
                    ->visible(fn (Get $get): bool => $get('nuevo_cliente_tipo_persona') === 'juridica')
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (mixed $state, Set $set): void {
                        if (is_scalar($state) && filled($state)) {
                            $set('ruc_factura', (string) $state);
                        }
                    })
                    ->columnSpan(1),

                // Campo compartido
                TextInput::make('nuevo_cliente_telefono')
                    ->label('Teléfono')
                    ->placeholder('88888888')
                    ->columnSpan(1),
            ])->visible(fn (Get $get): bool => (bool) $get('registrar_nuevo_cliente')),
        ]);
    }

    public static function autocompletarDatosFiscales(int $clienteId, Set $set): void
    {
        $cliente = Cliente::query()
            ->with(['persona.personaNatural', 'persona.personaJuridica'])
            ->find($clienteId);

        if (! $cliente?->persona) {
            return;
        }

        $persona = $cliente->persona;

        $ruc = $persona->personaJuridica !== null
            ? $persona->personaJuridica->numero_identificacion
            : $persona->personaNatural?->numero_identificacion;
        $razonSocial = $persona->personaJuridica !== null
            ? $persona->personaJuridica->razon_social
            : $persona->nombre_completo;

        if (filled($ruc)) {
            $set('ruc_factura', $ruc);
        }
        if (filled($razonSocial)) {
            $set('razon_social_factura', $razonSocial);
        }
    }
}
