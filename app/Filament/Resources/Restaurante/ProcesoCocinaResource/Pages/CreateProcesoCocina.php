<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Pages;

use App\Filament\Resources\Restaurante\ProcesoCocinaResource\ProcesoCocinaResource;
use App\Interactors\Restaurante\RegistrarProcesoCocina;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class CreateProcesoCocina extends CreateRecord
{
    protected static string $resource = ProcesoCocinaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $data['realizado_por'] = Auth::id();

        /** @var array{codigo: string, plato_id: int, cantidad_platos: int, realizado_por?: int|null, observaciones?: string|null} $data */
        return app(RegistrarProcesoCocina::class)->ejecutar($data);
    }

    protected function afterCreate(): void
    {
        $this->redirect(route('filament.admin.resources.restaurante.procesos-cocina.edit', $this->record));
    }
}
