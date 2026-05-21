<?php

namespace App\Filament\Resources\Compras\Recepciones\Pages;

use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\UseCases\Compras\OrdenesCompra\Queries\ObtenerOrdenCompraConItems;
use App\UseCases\Compras\Recepciones\Mutations\CalcularYPrepararRecepcion;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\QueryException;
use PDOException;

class CreateRecepcion extends CreateRecord
{
    protected static string $resource = RecepcionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return app(CalcularYPrepararRecepcion::class)->execute($data);
    }

    public function mount(): void
    {
        parent::mount();

        $ordenId = (int) request()->query('orden_compra_id');

        if ($ordenId) {
            $useCase = app(ObtenerOrdenCompraConItems::class);
            $orden = $useCase->execute($ordenId);

            if ($orden) {
                $items = [];
                foreach ($orden->items as $item) {
                    $pending = $item->cantidad_pendiente ?? (float) $item->cantidad;
                    if ($pending > 0) {
                        $items[] = [
                            'orden_item_id' => $item->id,
                            'cantidad_recibida' => $pending,
                            'cantidad_rechazada' => 0,
                        ];
                    }
                }

                $this->form->fill([
                    'orden_compra_id' => $orden->id,
                    'fecha_recepcion' => now(),
                    'items' => $items,
                ]);
            }
        }
    }

    /** @return array<int, Action | ActionGroup> */
    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Guardar Recepción')
                ->submit('create')
                ->keyBindings(['mod+s']),
            Action::make('cancel')
                ->label('Cancelar')
                ->url($this->getResource()::getUrl('index')),
        ];
    }

    public function create(bool $another = false): void
    {
        try {
            parent::create($another);
        } catch (PDOException|QueryException $e) {
            $message = $e->getMessage();

            if (str_contains($message, 'CONTROL INDUSTRIAL')) {
                preg_match('/La cantidad recibida \(.*?\) supera la cantidad ordenada/', $message, $matches);

                Notification::make()
                    ->danger()
                    ->title('Error de recepción')
                    ->body($matches[0] ?? 'La cantidad recibida excede la cantidad ordenada en la Orden de Compra.')
                    ->persistent()
                    ->send();
            } else {
                Notification::make()
                    ->danger()
                    ->title('Error al guardar la recepción')
                    ->body('Ocurrió un error inesperado. Verifique los datos e intente nuevamente.')
                    ->persistent()
                    ->send();
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
