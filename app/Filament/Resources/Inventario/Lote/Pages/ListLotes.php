<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\Lote\Pages;

use App\Enums\Inventario\EstadoLote;
use App\Filament\Pages\Inventario\ReportesInventario;
use App\Filament\Resources\Inventario\Lote\LoteResource;
use App\Filament\Resources\Inventario\Lote\Widgets\LoteStatsOverview;
use App\Models\Inventario\Lote;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ListLotes extends ListRecords
{
    protected static string $resource = LoteResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            LoteStatsOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('ver_reportes')
                ->label('Reportes de Inventario')
                ->icon(Heroicon::ChartBar)
                ->color('gray')
                ->url(ReportesInventario::getUrl()),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make(),
            'Disponibles' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', EstadoLote::Disponible))
                ->icon(Heroicon::CheckCircle),
            'Cuarentena' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', EstadoLote::Cuarentena))
                ->icon(Heroicon::ShieldExclamation)
                ->badge(Lote::where('estado', EstadoLote::Cuarentena)->count()),
            'Vencidos' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', EstadoLote::Vencido))
                ->icon(Heroicon::XCircle),
            'Agotados' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('estado', EstadoLote::Agotado))
                ->icon(Heroicon::ArchiveBox),
        ];
    }
}
