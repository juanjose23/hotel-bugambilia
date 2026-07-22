<?php

namespace App\Filament\Resources\Servicios\Servicios\Pages;

use App\Filament\Resources\Servicios\Servicios\ServicioResource;
use App\Repository\Models\Servicios\Servicio;
use Filament\Resources\Pages\CreateRecord;

class CreateServicio extends CreateRecord
{
    protected static string $resource = ServicioResource::class;

    protected function afterCreate(): void
    {
        /** @var Servicio $record */
        $record = $this->getRecord();
        $imagenes = $this->data['imagenes'] ?? null;

        if (is_array($imagenes)) {
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
