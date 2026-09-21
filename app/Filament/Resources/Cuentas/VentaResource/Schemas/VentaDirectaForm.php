<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\VentaResource\Schemas;

use App\Enums\Facturacion\TipoFactura;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Forms\SelectorCliente;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class VentaDirectaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Cliente y Moneda de la Operación')
                ->icon(Heroicon::User)
                ->columns(3)
                ->schema([
                    SelectorCliente::single('cliente_id', 'Cliente', 'Consumidor Final (Opcional)'),

                    Select::make('moneda_id')
                        ->label('Moneda de Venta')
                        ->options(fn (): array => Moneda::query()->pluck('nombre', 'id')->all())
                        ->default(function (): int {
                            $moneda = app(ObtenerMonedaPredeterminadaQuery::class)->ejecutar();

                            return $moneda instanceof Moneda ? $moneda->id : 1;
                        })
                        ->required(),

                    Select::make('metodo_pago')
                        ->label('Método de Pago')
                        ->options([
                            'Efectivo' => 'Efectivo',
                            'Tarjeta' => 'Tarjeta Débito / Crédito',
                            'Transferencia' => 'Transferencia Bancaria',
                            'Mostrador' => 'Cargo Mostrador / POS',
                        ])
                        ->default('Efectivo')
                        ->required(),
                ]),

            Section::make('Detalle de Ítems (Productos y Servicios)')
                ->icon(Heroicon::ShoppingBag)
                ->schema([
                    Repeater::make('items')
                        ->hiddenLabel()
                        ->addActionLabel('Agregar Ítem a la Venta')
                        ->minItems(1)
                        ->defaultItems(1)
                        ->columns(12)
                        ->schema([
                            Select::make('tipo_item')
                                ->label('Tipo')
                                ->options([
                                    'producto' => 'Producto',
                                    'servicio' => 'Servicio',
                                    'manual' => 'Concepto Libre',
                                ])
                                ->default('producto')
                                ->columnSpan(2)
                                ->live(),

                            Select::make('producto_id')
                                ->label('Producto')
                                ->placeholder('Seleccionar producto...')
                                ->options(fn (): array => Producto::query()
                                    ->where('estado', EstadoGeneral::Activo)
                                    ->pluck('nombre', 'id')
                                    ->all())
                                ->searchable()
                                ->visible(fn (Get $get): bool => ($get('tipo_item') ?? 'producto') === 'producto')
                                ->columnSpan(3)
                                ->live()
                                ->afterStateUpdated(function (mixed $state, Set $set): void {
                                    if (is_numeric($state)) {
                                        $prod = Producto::find($state);
                                        if ($prod instanceof Producto) {
                                            $set('concepto', $prod->nombre);
                                        }
                                    }
                                }),

                            Select::make('servicio_id')
                                ->label('Servicio')
                                ->placeholder('Seleccionar servicio...')
                                ->options(fn (): array => Servicio::query()->pluck('nombre', 'id')->all())
                                ->searchable()
                                ->visible(fn (Get $get): bool => ($get('tipo_item') ?? '') === 'servicio')
                                ->columnSpan(3)
                                ->live()
                                ->afterStateUpdated(function (mixed $state, Set $set): void {
                                    if (is_numeric($state)) {
                                        $serv = Servicio::with('precios')->find($state);
                                        if ($serv instanceof Servicio) {
                                            $set('concepto', $serv->nombre);
                                            $precio = $serv->precios->first();
                                            if ($precio instanceof Precio) {
                                                $set('precio_unitario', (float) $precio->precio);
                                            }
                                        }
                                    }
                                }),

                            TextInput::make('concepto')
                                ->label('Concepto / Descripción')
                                ->required()
                                ->columnSpan(fn ($get): int => ($get('tipo_item') ?? '') === 'manual' ? 5 : 2),

                            TextInput::make('cantidad')
                                ->label('Cant.')
                                ->numeric()
                                ->default(1)
                                ->minValue(0.01)
                                ->required()
                                ->columnSpan(1)
                                ->live(onBlur: true),

                            TextInput::make('precio_unitario')
                                ->label('Precio Unit.')
                                ->numeric()
                                ->default(0.00)
                                ->minValue(0.00)
                                ->required()
                                ->columnSpan(2)
                                ->live(onBlur: true),

                            TextInput::make('descuento')
                                ->label('Descuento')
                                ->numeric()
                                ->default(0.00)
                                ->minValue(0.00)
                                ->columnSpan(2)
                                ->live(onBlur: true),
                        ]),
                ]),

            Section::make('Facturación Fiscal DGI (Opcional)')
                ->icon(Heroicon::DocumentCheck)
                ->collapsed()
                ->columns(3)
                ->schema([
                    Toggle::make('emitir_factura')
                        ->label('¿Emitir Factura Fiscal DGI de inmediato?')
                        ->helperText('Si se marca, el sistema reservará folio y emitirá la factura oficial con la venta.')
                        ->columnSpanFull()
                        ->live(),

                    Select::make('factura_serie_id')
                        ->label('Serie Fiscal DGI')
                        ->options(fn (): array => FacturaSerie::query()->where('activa', true)->pluck('codigo', 'id')->all())
                        ->visible(fn ($get): bool => (bool) $get('emitir_factura'))
                        ->required(fn ($get): bool => (bool) $get('emitir_factura')),

                    Select::make('tipo_factura')
                        ->label('Tipo de Factura')
                        ->options([
                            TipoFactura::Contado->value => TipoFactura::Contado->getLabel(),
                            TipoFactura::Credito->value => TipoFactura::Credito->getLabel(),
                        ])
                        ->default(TipoFactura::Contado->value)
                        ->visible(fn ($get): bool => (bool) $get('emitir_factura')),
                ]),
        ]);
    }
}
