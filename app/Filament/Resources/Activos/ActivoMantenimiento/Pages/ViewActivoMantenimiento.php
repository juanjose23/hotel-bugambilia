<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoMantenimiento\Pages;

use App\Filament\Resources\Activos\ActivoMantenimiento\ActivoMantenimientoResource;
use App\Models\Activos\ActivoMantenimiento;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;

class ViewActivoMantenimiento extends ViewRecord
{
    protected static string $resource = ActivoMantenimientoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('imprimir')
                ->label('Imprimir Ficha')
                ->icon(Heroicon::Printer)
                ->color('info')
                ->url(fn (ActivoMantenimiento $record) => route('reporte.activos.mantenimiento.pdf', $record))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->can('Activos:ReporteMantenimiento') ?? false),

            EditAction::make(),
        ];
    }

    public function getRecord(): Model
    {
        return parent::getRecord()->load([
            'activo.producto',
            'plan.moneda',
            'plan.proveedor.persona',
            'realizadoPor',
        ]);
    }
}
