<?php

namespace App\Filament\Resources\Servicios\Servicios\Pages;

use App\Filament\Resources\Servicios\Servicios\ServicioResource;
use App\Repository\Models\Servicios\Servicio;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditServicio extends EditRecord
{
    protected static string $resource = ServicioResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        /** @var Servicio $record */
        $record = $this->getRecord();
        $imagenes = $this->data['imagenes'] ?? null;

        if (is_array($imagenes)) {
            $record->imagenes()->delete();
            foreach ($imagenes as $index => $path) {
                if ($path) {
                    $record->imagenes()->create([
                        'url' => $path,
                        'orden' => $index + 1,
                    ]);
                }
            }
        }
    }
}
