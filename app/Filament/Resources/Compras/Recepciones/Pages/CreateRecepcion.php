<?php

namespace App\Filament\Resources\Compras\Recepciones\Pages;

use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\Models\Compras\RecepcionCompra;
use App\UseCases\Compras\ObtenerOrdenCompraConItems;
use Filament\Resources\Pages\CreateRecord;

class CreateRecepcion extends CreateRecord
{
    protected static string $resource = RecepcionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $year = now()->year;
        $count = RecepcionCompra::whereYear('fecha_recepcion', $year)->count() + 1;
        $data['codigo'] = "REC-{$year}-".str_pad((string) $count, 3, '0', STR_PAD_LEFT);

        return $data;
    }

    public function mount(): void
    {
        parent::mount();

        $ordenId = (int) request()->query('orden_compra_id');

        if ($ordenId) {
            $orden = app(ObtenerOrdenCompraConItems::class)->execute($ordenId);

            if ($orden) {
                $this->form->fill([
                    'orden_compra_id' => $orden->id,
                    'fecha_recepcion' => now(),
                    'items' => $orden->items->map(fn ($item) => [
                        'orden_item_id' => $item->id,
                        'cantidad_recibida' => $item->cantidad,
                        'cantidad_rechazada' => 0,
                    ])->toArray(),
                ]);
            }
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
