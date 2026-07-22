<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Restaurante\Pedido;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

class GestionMesas extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Gestión de Mesas';

    protected static ?string $title = 'Mapa de Mesas';

    protected static ?string $slug = 'restaurante/mesas';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.gestion-mesas';

    /** @var Collection<int, Espacio> */
    public Collection $ambientes;

    /** @var Collection<int, Espacio> */
    public Collection $mesas;

    public function mount(): void
    {
        $this->recargarMesas();
    }

    public function recargarMesas(): void
    {
        // Obtener el restaurante principal
        $restaurante = Espacio::where('tipo', 'restaurante')->first();

        if (! $restaurante) {
            $this->ambientes = collect();
            $this->mesas = collect();

            return;
        }

        // Obtener los ambientes del restaurante (sub-espacios directos)
        $this->ambientes = Espacio::where('padre_id', $restaurante->id)
            ->whereIn('tipo', ['ambiente', 'terraza', 'bar'])
            ->orderBy('orden')
            ->get();

        // Obtener todas las mesas del restaurante
        $this->mesas = Espacio::where('padre_id', $restaurante->id)
            ->where('tipo', 'mesa')
            ->get()->map(function ($mesa) {
                // Obtener pedidos abiertos para esta mesa
                $pedidoAbierto = Pedido::where('mesa_id', $mesa->id)
                    ->whereIn('estado', ['abierto', 'preparacion', 'listo', 'servido'])
                    ->first();

                $mesa->pedido_abierto_id = $pedidoAbierto?->id;
                $mesa->pedido_abierto_codigo = $pedidoAbierto?->codigo;
                $mesa->pedido_abierto_total = $pedidoAbierto?->total;

                return $mesa;
            });
    }

    public function cambiarEstadoMesa(int $mesaId, string $nuevoEstado): void
    {
        $mesa = Espacio::findOrFail($mesaId);

        $estadoEnum = EstadoEspacio::from((int) $nuevoEstado);
        $mesa->estado = $estadoEnum;
        $mesa->save();

        $this->recargarMesas();

        Notification::make()
            ->title("Mesa {$mesa->nombre} cambiada a {$estadoEnum->getLabel()}")
            ->success()
            ->send();
    }
}
