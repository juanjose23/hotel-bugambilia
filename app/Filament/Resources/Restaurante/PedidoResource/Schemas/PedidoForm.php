<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Schemas;

use App\Enums\Restaurante\EstadoItemPedido;
use App\Enums\Restaurante\EstadoPedido;
use App\Filament\Shared\Forms\SelectorCuenta;
use App\Repository\Models\Personas\Persona;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use App\Repository\Queries\Restaurante\Pedidos\BuscarClientesRapidoQuery;
use App\Repository\Queries\Restaurante\Pedidos\ObtenerDatosPedidoFormQuery;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

final class PedidoForm
{
    public static function configure(Schema $schema): Schema
    {
        $pedidoQuery = app(ObtenerDatosPedidoFormQuery::class);
        $monedaPredeterminada = app(ObtenerMonedaPredeterminadaQuery::class)->ejecutar();
        $simboloMoneda = $monedaPredeterminada !== null ? ($monedaPredeterminada->simbolo ?? 'C$') : 'C$';

        return $schema
            ->columns(1)
            ->components([
                Section::make('Datos de la Comanda / Pedido')
                    ->description('Seleccione la mesa asignada y el cliente en caso de ser necesario.')
                    ->columnSpanFull()
                    ->schema([
                        Grid::make(3)->schema([
                            TextInput::make('codigo')
                                ->label('Código Comanda')
                                ->disabled()
                                ->dehydrated(false)
                                ->maxLength(20)
                                ->prefixIcon(Heroicon::Key),

                            Select::make('mesa_id')
                                ->label('Mesa de Servicio')
                                ->options(fn () => $pedidoQuery->mesasDisponibles())
                                ->searchable()
                                ->required()
                                ->prefixIcon('hugeicons-restaurant-table'),

                            Select::make('estado')
                                ->label('Estado Comanda')
                                ->options(EstadoPedido::class)
                                ->default(EstadoPedido::ABIERTO)
                                ->disabled()
                                ->dehydrated(false)
                                ->prefixIcon(Heroicon::ArrowPath),
                        ]),

                        Grid::make(2)->schema([
                            SelectorCuenta::make(columnSpan: 1),

                            TextInput::make('subtotal')
                                ->label('Subtotal Acumulado')
                                ->numeric()
                                ->prefix($simboloMoneda)
                                ->formatStateUsing(fn ($record, $state) => number_format((float) ($record?->items?->where('estado', '!=', EstadoItemPedido::ANULADO->value)->sum('subtotal') ?? $state ?? 0.00), 2))
                                ->disabled()
                                ->dehydrated(false),
                        ]),

                        Select::make('cliente_id')
                            ->label('Cliente (Opcional)')
                            ->placeholder('Buscar por nombre o teléfono...')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search): array => app(BuscarClientesRapidoQuery::class)
                                ->ejecutar($search)
                                ->mapWithKeys(fn (Persona $p) => [
                                    $p->id => trim(($p->nombre_completo ?? $p->primer_nombre).' — '.($p->telefono ?? 'S/T')),
                                ])
                                ->toArray())
                            ->getOptionLabelUsing(function ($value): ?string {
                                if (! is_numeric($value)) {
                                    return null;
                                }

                                $persona = Persona::with(['personaNatural', 'personaJuridica'])->find((int) $value);

                                return $persona instanceof Persona ? ($persona->nombre_completo ?? 'Cliente #'.$value) : 'Cliente #'.$value;
                            })
                            ->nullable()
                            ->native(false)
                            ->prefixIcon(Heroicon::User)
                            ->columnSpan(1),

                        Textarea::make('notas')
                            ->label('Notas Generales de la Comanda')
                            ->columnSpanFull()
                            ->rows(2)
                            ->placeholder('Ej. Mesa VIP / Solicitud especial de cliente...'),
                    ]),

                Section::make('Platillos & Bebidas de la Comanda')
                    ->description('Seleccione el menú organizado por categorías (Bebidas, Desayunos, Almuerzos, Cenas, Platos Fuertes, etc.) con sus especificaciones de cocina o bar.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship('items')
                            ->schema([
                                Select::make('plato_id')
                                    ->label('Platillo / Bebida (Organizado por Categoría)')
                                    ->options(fn () => $pedidoQuery->platosActivosAgrupadosPorCategoria())
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function (mixed $state, Set $set) use ($pedidoQuery): void {
                                        if (is_numeric($state)) {
                                            $precio = $pedidoQuery->precioActualDePlato((int) $state);

                                            if ($precio !== null) {
                                                $set('precio_unitario', $precio);
                                            }
                                        }
                                    })
                                    ->columnSpan(5),

                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->default(1)
                                    ->minValue(1)
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('precio_unitario')
                                    ->label('Precio Unit.')
                                    ->numeric()
                                    ->default(0.00)
                                    ->prefix($simboloMoneda)
                                    ->columnSpan(2),

                                TextInput::make('observaciones')
                                    ->label('Observaciones Cocina')
                                    ->columnSpan(3)
                                    ->placeholder('Ej. Sin cebolla, término medio...'),
                            ])
                            ->columns(12)
                            ->defaultItems(1)
                            ->addActionLabel('Agregar Platillo / Bebida'),
                    ]),
            ]);
    }
}
