<?php

namespace App\Filament\Resources\Compras\OrdenesCompra\Pages;

use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Models\Compras\OrdenCompra;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateOrdenCompra extends CreateRecord
{
    protected static string $resource = OrdenCompraResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $year = now()->year;
        $codigo = DB::transaction(function () use ($year) {
            $max = OrdenCompra::whereYear('fecha_orden', $year)
                ->lockForUpdate()
                ->max('codigo');
            $last = 0;
            if ($max && preg_match('/-(\d+)$/', $max, $matches)) {
                $last = (int) $matches[1];
            }

            return "OC-{$year}-".str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
        });
        $data['codigo'] = $codigo;

        return $data;
    }

    /** @return array<int, Action | ActionGroup> */
    protected function getFormActions(): array
    {
        return [];
    }
}
