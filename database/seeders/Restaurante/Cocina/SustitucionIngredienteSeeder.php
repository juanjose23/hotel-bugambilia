<?php

declare(strict_types=1);

namespace Database\Seeders\Restaurante\Cocina;

use App\Interactors\Restaurante\Cocina\AutorizarSustitucionIngrediente;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Models\Restaurante\Pedido;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Restaurante\SustitucionIngrediente;

/**
 * Sustitución de ingredientes autorizada sobre el pedido demo enviado a cocina:
 * mozzarella por queso fresco, apoyándose en el stock de la Cocina Restaurante.
 */
final class SustitucionIngredienteSeeder extends BaseCocinaSeeder
{
    public function run(): void
    {
        $pedido = Pedido::query()->where('codigo', 'PDR-DEMO-COCINA')->first();

        if (! $pedido instanceof Pedido) {
            $this->command->warn('Sustitución de ingredientes omitida: no existe el pedido demo enviado a cocina.');

            return;
        }

        $sustituta = $this->variantePorNombreProducto('Queso fresco');

        if (! $sustituta instanceof ProductoVariante) {
            $this->command->warn('Sustitución de ingredientes omitida: no se encontró la variante sustituta (Queso fresco).');

            return;
        }

        $pedido->load('items.plato');

        foreach ($pedido->items as $item) {
            $plato = $item->plato;

            if (! $plato instanceof Plato) {
                continue;
            }

            $ingredientes = $plato->ingredientes()->with('variante.producto')->get();

            /** @var ProductoKit|null $mozzarella */
            $mozzarella = $ingredientes->first(
                static fn (ProductoKit $kit): bool => $kit->variante?->producto?->nombre === 'Queso Mozzarella Rallado'
            );

            if (! $mozzarella instanceof ProductoKit || ! $mozzarella->variante instanceof ProductoVariante) {
                continue;
            }

            $original = $mozzarella->variante;

            if ($original->id === $sustituta->id) {
                continue;
            }

            $existe = SustitucionIngrediente::query()
                ->where('pedido_item_id', $item->id)
                ->where('variante_original_id', $original->id)
                ->exists();

            if ($existe) {
                continue;
            }

            $cantidadRequerida = ((float) $mozzarella->cantidad) * ((float) $item->cantidad);

            app(AutorizarSustitucionIngrediente::class)->ejecutar(
                item: $item,
                varianteOriginalId: $original->id,
                varianteSustitutaId: $sustituta->id,
                cantidadRequerida: $cantidadRequerida,
                usuarioId: $this->usuarioId(),
                motivo: 'Demostración: queso mozzarella agotado, se autoriza queso fresco como sustituto.',
            );

            $this->command->info('Cocina restaurante: sustitución de ingredientes autorizada para el pedido demo.');

            return;
        }

        $this->command->warn('Sustitución de ingredientes omitida: el pedido demo no tiene ítems con mozzarella.');
    }
}
