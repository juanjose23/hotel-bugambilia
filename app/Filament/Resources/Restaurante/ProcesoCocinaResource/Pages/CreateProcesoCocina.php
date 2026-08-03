<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Pages;

use App\Filament\Resources\Restaurante\ProcesoCocinaResource\ProcesoCocinaResource;
use App\Interactors\Restaurante\Cocina\ProcesarProcesoCocina;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class CreateProcesoCocina extends CreateRecord
{
    protected static string $resource = ProcesoCocinaResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $userId = Auth::id() !== null ? (int) Auth::id() : null;

        return app(ProcesarProcesoCocina::class)->guardar(null, $data, $userId);
    }
}
