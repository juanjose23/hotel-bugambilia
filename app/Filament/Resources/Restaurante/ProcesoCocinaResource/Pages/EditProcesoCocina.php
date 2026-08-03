<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Pages;

use App\Filament\Resources\Restaurante\ProcesoCocinaResource\ProcesoCocinaResource;
use App\Filament\Shared\Actions\Restaurante\ReporteCostosCocinaAction;
use App\Interactors\Restaurante\Cocina\ProcesarProcesoCocina;
use App\Repository\Models\Restaurante\ProcesoCocina;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

final class EditProcesoCocina extends EditRecord
{
    protected static string $resource = ProcesoCocinaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ReporteCostosCocinaAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var ProcesoCocina $proceso */
        $proceso = $record;
        $userId = Auth::id() !== null ? (int) Auth::id() : null;

        return app(ProcesarProcesoCocina::class)->guardar($proceso, $data, $userId);
    }
}
