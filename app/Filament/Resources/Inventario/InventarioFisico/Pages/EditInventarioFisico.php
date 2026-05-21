<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\InventarioFisico\Pages;

use App\Enums\Inventario\EstadoInventarioFisico;
use App\Filament\Resources\Inventario\InventarioFisico\InventarioFisicoResource;
use App\Models\Inventario\InventarioFisico;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditInventarioFisico extends EditRecord
{
    protected static string $resource = InventarioFisicoResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $inventario = $this->record instanceof InventarioFisico ? $this->record : null;

        if ($inventario && $inventario->estado === EstadoInventarioFisico::Procesado) {
            Notification::make()
                ->warning()
                ->title('Toma de Inventario no editable')
                ->body('Las tomas de inventario físico procesadas no se pueden editar.')
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
