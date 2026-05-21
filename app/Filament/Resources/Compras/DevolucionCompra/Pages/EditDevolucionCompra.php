<?php

namespace App\Filament\Resources\Compras\DevolucionCompra\Pages;

use App\Enums\Compras\EstadoDevolucion;
use App\Filament\Resources\Compras\DevolucionCompra\DevolucionCompraResource;
use App\Models\Compras\DevolucionCompra;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditDevolucionCompra extends EditRecord
{
    protected static string $resource = DevolucionCompraResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $devolucion = $this->record instanceof DevolucionCompra ? $this->record : null;

        if ($devolucion && $devolucion->estado === EstadoDevolucion::Confirmada) {
            Notification::make()
                ->warning()
                ->title('Devolución no editable')
                ->body('Las devoluciones confirmadas no se pueden editar.')
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
