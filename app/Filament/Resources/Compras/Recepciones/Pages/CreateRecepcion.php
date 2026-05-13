<?php

namespace App\Filament\Resources\Compras\Recepciones\Pages;

use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\Models\Compras\RecepcionCompra;
use App\UseCases\Compras\ObtenerOrdenCompraConItems;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateRecepcion extends CreateRecord
{
    protected static string $resource = RecepcionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $year = now()->year;
        $codigo = DB::transaction(function () use ($year) {
            $max = RecepcionCompra::whereYear('fecha_recepcion', $year)
                ->lockForUpdate()
                ->max('codigo');
            $last = $max ? (int) substr($max, -3) : 0;

            return "REC-{$year}-".str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
        });
        $data['codigo'] = $codigo;

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

    /** @return array<int, Action | ActionGroup> */
    protected function getFormActions(): array
    {
        return [];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
