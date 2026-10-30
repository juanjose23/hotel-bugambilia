<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Pages;

use App\Filament\Resources\Habitaciones\HabitacionResource\HabitacionResource;
use App\Interactors\Habitaciones\SincronizarGaleriaImagenesHabitacion;
use App\Interactors\Habitaciones\SincronizarVistasHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Livewire\Attributes\Locked;

class EditHabitacion extends EditRecord
{
    #[Locked]
    public bool $galeriaEditada = false;

    public function marcarGaleriaEditada(): void
    {
        $this->galeriaEditada = true;
    }

    protected ?bool $hasDatabaseTransactions = true;

    protected static string $resource = HabitacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterSave(): void
    {
        /** @var Habitacion $record */
        $record = $this->getRecord();
        $vistas = $this->data['vista_ids'] ?? [];
        app(SincronizarVistasHabitacion::class)->ejecutar($record, is_array($vistas) ? $vistas : []);
        if (! $this->galeriaEditada) {
            return;
        }
        $imagenes = $this->data['imagenes'] ?? null;

        if (! is_array($imagenes)) {
            return;
        }

        app(SincronizarGaleriaImagenesHabitacion::class)->ejecutar($record, $imagenes);
        $this->galeriaEditada = false;
    }
}
