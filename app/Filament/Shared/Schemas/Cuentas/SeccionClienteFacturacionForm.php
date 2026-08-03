<?php

declare(strict_types=1);

namespace App\Filament\Shared\Schemas\Cuentas;

use App\Filament\Shared\Forms\SelectorCliente;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

final class SeccionClienteFacturacionForm
{
    public static function make(): Section
    {
        return Section::make('Cliente y Facturación (Opcional)')
            ->icon('heroicon-o-document-text')
            ->collapsed(fn (callable $get): bool => ! filled($get('cliente_id')))
            ->schema([
                Grid::make(['default' => 1, 'sm' => 2])->schema([
                    Select::make('tipo_comprobante')
                        ->label('Tipo de Comprobante')
                        ->options([
                            'voucher' => 'Voucher / Ticket de Consumo',
                            'factura_empresarial' => 'Factura Empresarial',
                        ])
                        ->default('voucher')
                        ->required()
                        ->live(),

                    SelectorCliente::single('cliente_id')
                        ->hidden(fn (callable $get): bool => (bool) $get('registrar_nuevo_cliente')),
                ]),

                Grid::make(['default' => 1, 'sm' => 2])->schema([
                    TextInput::make('ruc_factura')
                        ->label('RUC / Identificación Fiscal')
                        ->placeholder('0010101900001A')
                        ->required(fn (callable $get): bool => $get('tipo_comprobante') === 'factura_empresarial')
                        ->visible(fn (callable $get): bool => $get('tipo_comprobante') === 'factura_empresarial'),

                    TextInput::make('razon_social_factura')
                        ->label('Nombre o Razón Social')
                        ->placeholder('Nombre de la Empresa S.A.')
                        ->required(fn (callable $get): bool => $get('tipo_comprobante') === 'factura_empresarial')
                        ->visible(fn (callable $get): bool => $get('tipo_comprobante') === 'factura_empresarial'),
                ]),

                Toggle::make('registrar_nuevo_cliente')
                    ->label('Registrar cliente rápidamente')
                    ->live(),

                Grid::make(['default' => 1, 'sm' => 2])->schema([
                    TextInput::make('nuevo_cliente_nombre')
                        ->label('Nombres')
                        ->required(fn (callable $get): bool => (bool) $get('registrar_nuevo_cliente')),

                    TextInput::make('nuevo_cliente_apellido')
                        ->label('Apellidos')
                        ->required(fn (callable $get): bool => (bool) $get('registrar_nuevo_cliente')),

                    TextInput::make('nuevo_cliente_identificacion')
                        ->label('Cédula / RUC / Pasaporte')
                        ->placeholder('Nro. identificación'),

                    TextInput::make('nuevo_cliente_telefono')
                        ->label('Teléfono')
                        ->placeholder('88888888'),
                ])->visible(fn (callable $get): bool => (bool) $get('registrar_nuevo_cliente')),
            ]);
    }
}
