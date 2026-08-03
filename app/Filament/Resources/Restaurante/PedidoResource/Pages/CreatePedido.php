<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PedidoResource\Pages;

use App\Filament\Resources\Restaurante\PedidoResource\PedidoResource;
use App\Filament\Shared\Actions\Restaurante\RegistrarClienteRapidoAction;
use App\Interactors\Restaurante\Pedidos\AbrirPedidoMesa;
use App\Interactors\Restaurante\Pedidos\EnviarPedidoACocina;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Queries\Restaurante\Mesas\ObtenerColaboradorPorUsuarioQuery;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;

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
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            RegistrarClienteRapidoAction::make(
                onClienteRegistrado: function ($persona): void {
                    $this->form->fill([
                        ...(array) $this->form->getRawState(),
                        'cliente_id' => $persona->id,
                    ]);
                }
            ),
        ];
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

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Pedido
    {
        /** @var User|null $user */
        $user = auth()->user();

        $mesaId = isset($data['mesa_id']) && is_numeric($data['mesa_id']) ? (int) $data['mesa_id'] : null;
        $mesa = $mesaId !== null
            ? app(RestauranteRepositorioInterface::class)->obtenerMesaPorId($mesaId)
            : null;

        $meseroId = isset($data['mesero_id']) && is_numeric($data['mesero_id']) ? (int) $data['mesero_id'] : ($user?->id);
        $clienteId = isset($data['cliente_id']) && is_numeric($data['cliente_id']) ? (int) $data['cliente_id'] : null;
        $notas = is_string($data['notas'] ?? null) ? $data['notas'] : null;

        /** @var array<int, array{plato_id: int|string, cantidad?: float|int|string, precio_unitario?: float|int|string, observaciones?: string|null}> $itemsData */
        $itemsData = is_array($data['items'] ?? null) ? $data['items'] : [];

        $interactor = app(AbrirPedidoMesa::class);

        try {
            $pedido = $interactor->ejecutar(
                mesa: $mesa,
                meseroId: $meseroId,
                clienteId: $clienteId,
                notas: $notas,
                items: $itemsData,
            );

            $pedido->loadMissing('items');

            if ($pedido->items->count() > 0) {
                try {
                    app(EnviarPedidoACocina::class)->ejecutar($pedido);
                } catch (DomainException $e) {
                    Notification::make()
                        ->title('Comanda creada pero no se pudo enviar a cocina')
                        ->body($e->getMessage())
                        ->warning()
                        ->send();
                }
            }

            return $pedido;
        } catch (DomainException $e) {
            Notification::make()
                ->title('No se pudo crear el pedido')
                ->body($e->getMessage())
                ->danger()
                ->send();

            throw new Halt;
        }
    }
}
