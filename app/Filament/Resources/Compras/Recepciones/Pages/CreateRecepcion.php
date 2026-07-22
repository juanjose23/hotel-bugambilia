<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Recepciones\Pages;

use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\Interactors\Compras\Recepciones\CalcularYPrepararRecepcion;
use App\Repository\Queries\Compras\OrdenesCompra\ObtenerOrdenCompraConItems;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateRecepcion extends CreateRecord
{
    protected CalcularYPrepararRecepcion $calcularYPrepararRecepcion;

    protected ObtenerOrdenCompraConItems $obtenerOrdenCompraConItems;

    public function boot(
        CalcularYPrepararRecepcion $calcularYPrepararRecepcion,
        ObtenerOrdenCompraConItems $obtenerOrdenCompraConItems
    ): void {
        $this->calcularYPrepararRecepcion = $calcularYPrepararRecepcion;
        $this->obtenerOrdenCompraConItems = $obtenerOrdenCompraConItems;
    }

    protected static string $resource = RecepcionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->calcularYPrepararRecepcion->ejecutar($data);
    }

    public function mount(): void
    {
        parent::mount();

        $ordenId = (int) request()->query('orden_compra_id');

        if ($ordenId) {
            $orden = $this->obtenerOrdenCompraConItems->ejecutar($ordenId);

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
        } catch (\InvalidArgumentException $e) {

            Notification::make()
                ->danger()
                ->title('Error de recepción')
                ->body($e->getMessage())
                ->persistent()
                ->send();
        } catch (\Throwable $e) {
            Log::error('Error al crear recepción', ['error' => $e->getMessage()]);

            Notification::make()
                ->danger()
                ->title('Error al guardar la recepción')
                ->body('Ocurrió un error inesperado. Verifique los datos e intente nuevamente.')
                ->persistent()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
