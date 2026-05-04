<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Pages;

use App\Filament\Resources\Catalogos\Catalogos\CatalogoResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use App\UseCases\Catalogo\Queries\ObtenerCatalogo;
use App\UseCases\Catalogo\Commands\ActualizarCatalogo;

class ViewCatalogo extends ViewRecord
{
    protected static string $resource = CatalogoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->modalHeading('Editar catálogo')
                ->modalWidth('lg')
                ->using(fn($record, array $data) => app(ActualizarCatalogo::class)->execute($record, $data)),
        ];
    }

    protected function resolveRecord(int|string $record): \Illuminate\Database\Eloquent\Model
    {
        return app(ObtenerCatalogo::class)->execute(['id' => $record]);
    }
}
