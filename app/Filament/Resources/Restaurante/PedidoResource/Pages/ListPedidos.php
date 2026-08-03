<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Pages;

use App\Enums\Restaurante\EstadoPedido;
use App\Filament\Resources\Restaurante\PedidoResource\PedidoResource;
use App\Repository\Queries\Restaurante\Pedidos\ContarPedidosPorEstadoQuery;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

final class ListPedidos extends ListRecords
{
    protected static string $resource = PedidoResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }

    public function getTabs(): array
    {
        $counts = app(ContarPedidosPorEstadoQuery::class)->ejecutar();

        return [
            'activos' => Tab::make('Activos')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('estado', [
                    EstadoPedido::ABIERTO,
                    EstadoPedido::EN_PREPARACION,
                    EstadoPedido::LISTO,
                    EstadoPedido::SERVIDO,
                ])),
            'abiertos' => Tab::make('Abiertos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', EstadoPedido::ABIERTO))
                ->badge(fn () => $counts['abiertos']),
            'en_preparacion' => Tab::make('En Preparación')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', EstadoPedido::EN_PREPARACION))
                ->badge(fn () => $counts['en_preparacion']),
            'listos' => Tab::make('Listos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', EstadoPedido::LISTO))
                ->badge(fn () => $counts['listos']),
            'pagados' => Tab::make('Historial: Pagados')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', EstadoPedido::PAGADO)),
            'cargados' => Tab::make('Historial: Cargados a Hab.')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', EstadoPedido::CARGADO_A_HABITACION)),
            'cancelados' => Tab::make('Historial: Cancelados')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', EstadoPedido::CANCELADO)),
        ];
    }
}
