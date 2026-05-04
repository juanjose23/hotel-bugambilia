<?php

namespace App\Filament\Resources\Catalogos\CatalogoTipos\Pages;

use App\Filament\Resources\Catalogos\CatalogoTipos\CatalogoTipoResource;
use App\UseCases\CatalogoTipo\Commands\CrearCatalogoTipo;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use App\UseCases\CatalogoTipo\Queries\ObtenerCatalogoTipo;

class ManageCatalogoTipos extends ManageRecords
{
    protected static string $resource = CatalogoTipoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->using(fn(array $data) => app(CrearCatalogoTipo::class)->execute($data)),
        ];
    }

    protected function resolveRecord(int|string $record): \Illuminate\Database\Eloquent\Model
    {
        return app(ObtenerCatalogoTipo::class)->execute(['id' => $record]);
    }
}