<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Pages;

use App\Filament\Resources\Restaurante\PedidoResource\PedidoResource;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\Mesas\ObtenerColaboradorPorUsuarioQuery;
use Filament\Resources\Pages\CreateRecord;

final class CreatePedido extends CreateRecord
{
    protected static string $resource = PedidoResource::class;

    public function mount(): void
    {
        parent::mount();

        $mesaIdParam = request()->query('mesa_id');
        if ($mesaIdParam !== null && is_numeric($mesaIdParam)) {
            $this->form->fill([
                'mesa_id' => (int) $mesaIdParam,
                'codigo' => 'PED-'.strtoupper(substr(uniqid(), -6)),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        /** @var User|null $user */
        $user = auth()->user();

        if (empty($data['mesero_id']) && $user instanceof User) {
            $colaborador = app(ObtenerColaboradorPorUsuarioQuery::class)->ejecutar($user->id);
            $data['mesero_id'] = $colaborador !== null ? $colaborador->id : $user->id;
        }

        return $data;
    }
}
