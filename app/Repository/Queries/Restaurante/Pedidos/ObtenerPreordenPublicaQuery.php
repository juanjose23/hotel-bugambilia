<?php

declare(strict_types=1);

namespace App\Repository\Queries\Restaurante\Pedidos;

use App\Repository\Models\Restaurante\Plato;
use App\Repository\Queries\Monedas\ObtenerMonedaPredeterminadaQuery;
use DomainException;

final readonly class ObtenerPreordenPublicaQuery
{
    public function __construct(private ObtenerMonedaPredeterminadaQuery $monedas) {}

    /**
     * @param  array<int, array{plato_id: int, cantidad: int, observaciones?: ?string}>  $items
     * @return list<array{plato_id: int, cantidad: int, precio_unitario: float, observaciones: ?string}>
     */
    public function ejecutar(array $items): array
    {
        if ($items === []) {
            return [];
        }
        $moneda = $this->monedas->ejecutar() ?? throw new DomainException('Configura la moneda del restaurante antes de recibir pedidos.');
        $platos = Plato::query()->whereIn('id', array_column($items, 'plato_id'))
            ->where('estado', 1)->where('web', true)
            ->with(['precios' => fn ($query) => $query->where('moneda_id', $moneda->id)
                ->where('estado', 1)
                ->where(fn ($q) => $q->whereNull('fecha_inicio')->orWhereDate('fecha_inicio', '<=', today()))
                ->where(fn ($q) => $q->whereNull('fecha_fin')->orWhereDate('fecha_fin', '>=', today()))
                ->orderByDesc('fecha_inicio')->orderByDesc('id')])
            ->get()->keyBy('id');
        $resultado = [];
        foreach ($items as $item) {
            $plato = $platos->get($item['plato_id']);
            if (! $plato instanceof Plato || $item['cantidad'] < 1 || $item['cantidad'] > 50) {
                throw new DomainException('Uno de los platillos ya no está disponible. Revisa tu selección.');
            }
            $precio = $plato->precios->first()->precio ?? $plato->getAttribute('precio_base');
            if (! is_numeric($precio) || (float) $precio <= 0) {
                throw new DomainException("El platillo {$plato->nombre} no tiene una tarifa de venta disponible.");
            }
            $resultado[] = ['plato_id' => $plato->id, 'cantidad' => $item['cantidad'],
                'precio_unitario' => round((float) $precio, 2), 'observaciones' => $item['observaciones'] ?? null];
        }

        return $resultado;
    }
}
