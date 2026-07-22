<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\Enums\Restaurante\EstadoPedido;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\PedidoItem;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\BaseFilter;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ReportesRestaurante extends Page implements HasTable
{
    use InteractsWithTable;

    protected static UnitEnum|string|null $navigationGroup = 'Restaurante';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Reportes';

    protected static ?string $title = 'Reportes del Restaurante';

    protected static ?string $slug = 'restaurante/reportes';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.reportes-restaurante';

    /** @var array<string, mixed> */
    public array $resumen = [];

    /** @var array<int, mixed> */
    public array $topPlatos = [];

    /** @var array<int, mixed> */
    public array $porCategoria = [];

    public int $totalPedidos = 0;

    public string $fechaInicio;

    public string $fechaFin;

    public function mount(): void
    {
        $this->fechaInicio = now()->startOfMonth()->toDateString();
        $this->fechaFin = now()->toDateString();
        $this->cargarReportes();
    }

    public function cargarReportes(): void
    {
        $pedidos = Pedido::with(['items.plato', 'mesa'])
            ->whereBetween('created_at', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59'])
            ->get();

        $this->totalPedidos = $pedidos->count();
        $totalFacturado = $pedidos->sum('total');
        $pedidosPagados = $pedidos->where('estado', EstadoPedido::Pagado->value)->count();
        $pedidosPendientes = $pedidos->whereIn('estado', ['abierto', 'preparacion'])->count();

        $this->resumen = [
            'total_pedidos' => $this->totalPedidos,
            'total_facturado' => $totalFacturado,
            'pedidos_pagados' => $pedidosPagados,
            'pedidos_pendientes' => $pedidosPendientes,
        ];

        $items = PedidoItem::whereHas('pedido', fn ($q) => $q->whereBetween('created_at', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59']))
            ->with('plato')
            ->get()
            ->groupBy('plato_id')
            ->map(function ($grupo) {
                $first = $grupo->first();
                $nombre = 'Desconocido';
                if ($first instanceof PedidoItem && $first->plato) {
                    $nombre = $first->plato->nombre;
                }

                return [
                    'plato' => $nombre,
                    // @phpstan-ignore cast.double (Collection::sum returns mixed)
                    'cantidad' => (float) $grupo->sum('cantidad'),
                    // @phpstan-ignore cast.double (Collection::sum returns mixed)
                    'total' => (float) $grupo->sum('subtotal'),
                ];
            })
            ->sortByDesc('cantidad')
            ->take(10)
            ->values()
            ->toArray();

        $this->topPlatos = $items;

        $this->porCategoria = PedidoItem::whereHas('pedido', fn ($q) => $q->whereBetween('created_at', [$this->fechaInicio.' 00:00:00', $this->fechaFin.' 23:59:59']))
            ->with('plato.categoria')
            ->get()
            ->groupBy(function (PedidoItem $item) {
                return $item->plato?->categoria->nombre ?? 'Sin categoria';
            })
            ->map(fn ($grupo, $cat) => [
                'categoria' => (string) $cat,
                // @phpstan-ignore cast.double (Collection::sum returns mixed)
                'cantidad' => (float) $grupo->sum('cantidad'),
                // @phpstan-ignore cast.double (Collection::sum returns mixed)
                'total' => (float) $grupo->sum('subtotal'),
            ])
            ->sortByDesc('total')
            ->values()
            ->toArray();
    }

    /**
     * @return Builder<Pedido>
     */
    protected function query(): Builder
    {
        return Pedido::with(['mesa', 'mesero.persona'])->latest();
    }

    /**
     * @return array<int, Column>
     */
    protected function getTableColumns(): array
    {
        return [
            TextColumn::make('codigo')->label('Pedido')->searchable()->sortable(),
            TextColumn::make('mesa.nombre')->label('Mesa'),
            TextColumn::make('mesero.persona.nombre_completo')->label('Mesero'),
            TextColumn::make('estado')->label('Estado')->badge()
                ->formatStateUsing(fn (string $state): string => EstadoPedido::from($state)->label())
                ->color(fn (string $state): string => EstadoPedido::from($state)->color()),
            TextColumn::make('total')->label('Total')->money('NIO')->sortable(),
            TextColumn::make('created_at')->label('Fecha')->dateTime()->sortable(),
        ];
    }

    protected function getTablePollingInterval(): ?string
    {
        return '30s';
    }

    /**
     * @return array<int, BaseFilter>
     */
    protected function getTableFilters(): array
    {
        return [];
    }
}
