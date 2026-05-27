<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo\Pages;

use App\Filament\Resources\Activos\Activo\ActivoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewActivo extends ViewRecord
{
    protected static string $resource = ActivoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }

    public function getRecord(): Model
    {
        return parent::getRecord()->load([
            'asignacionActiva.asignable',
            'asignaciones.asignable',
            'mantenimientos',
        ]);
    }
}
