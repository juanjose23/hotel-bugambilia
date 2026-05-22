<?php

declare(strict_types=1);

namespace App\Filament\Resources\Servicios\Servicios\Pages;

use App\Filament\Pages\Servicios\ReporteHistoricoPrecios;
use App\Filament\Resources\Servicios\Servicios\ServicioResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListServicios extends ListRecords
{
    protected static string $resource = ServicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reporte_historico_precios')
                ->label('Histórico de Precios')
                ->icon(Heroicon::CurrencyDollar)
                ->color('gray')
                ->url(ReporteHistoricoPrecios::getUrl()),
            CreateAction::make(),
        ];
    }
}
