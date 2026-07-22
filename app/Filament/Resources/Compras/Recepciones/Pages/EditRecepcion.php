<?php

namespace App\Filament\Resources\Compras\Recepciones\Pages;

use App\Enums\Compras\EstadoRecepcion;
use App\Filament\Resources\Compras\Recepciones\Actions\RecepcionEstadoActions;
use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\Repository\Models\Compras\RecepcionCompra;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditRecepcion extends EditRecord
{
    protected static string $resource = RecepcionResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $recepcion = $this->record instanceof RecepcionCompra ? $this->record : null;

        if ($recepcion && $recepcion->estado !== EstadoRecepcion::Pendiente) {
            Notification::make()
                ->warning()
                ->title('Recepción no editable')
                ->body('Solo las recepciones en estado Pendiente pueden editarse. Use los botones de acción para cambiar el estado.')
                ->send();

            $this->redirect($this->getResource()::getUrl('view', ['record' => $this->record]));
        }
    }

    /** @return array<int, Action | ActionGroup> */
    protected function getHeaderActions(): array
    {
        return [
            ...RecepcionEstadoActions::acciones(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
