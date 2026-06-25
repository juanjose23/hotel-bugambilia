<?php

use App\Enums\Compras\EstadoOrdenCompra;
use App\Enums\Compras\EstadoRecepcion;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\OrdenCompraItem;
use App\Models\Compras\RecepcionCompra;
use App\Models\User;
use App\UseCases\Compras\Recepciones\Mutations\CalcularYPrepararRecepcion;
use App\UseCases\Compras\Recepciones\Mutations\GenerarCodigoRecepcion;
use Illuminate\Database\Eloquent\ModelNotFoundException;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('GenerarCodigoRecepcion', function () {
    it('genera el primer codigo REC-YYYY-001 cuando no existen recepciones', function () {
        RecepcionCompra::whereNotNull('id')->delete();

        $codigo = app(GenerarCodigoRecepcion::class)->execute();

        $year = now()->year;
        expect($codigo)->toBe("REC-{$year}-001");
    });

    it('genera codigos correlativos secuenciales', function () {
        RecepcionCompra::whereNotNull('id')->delete();
        $year = now()->year;

        $codigo1 = app(GenerarCodigoRecepcion::class)->execute();
        expect($codigo1)->toBe("REC-{$year}-001");

        RecepcionCompra::factory()->create(['codigo' => $codigo1]);

        $codigo2 = app(GenerarCodigoRecepcion::class)->execute();
        expect($codigo2)->toBe("REC-{$year}-002");
    });

    it('ignora recepciones de otros anios al generar', function () {
        RecepcionCompra::whereNotNull('id')->delete();
        $year = now()->year;

        RecepcionCompra::factory()->create(['codigo' => 'REC-1999-050']);

        $codigo = app(GenerarCodigoRecepcion::class)->execute();
        expect($codigo)->toBe("REC-{$year}-001");
    });

    it('genera correctamente despues de llegara 999', function () {
        RecepcionCompra::whereNotNull('id')->delete();
        $year = now()->year;

        RecepcionCompra::factory()->create(['codigo' => "REC-{$year}-999"]);

        $codigo = app(GenerarCodigoRecepcion::class)->execute();
        expect($codigo)->toBe("REC-{$year}-1000");
    });
});

describe('CalcularYPrepararRecepcion', function () {
    it('prepara datos de recepcion con codigo y estado Pendiente', function () {
        $orden = OrdenCompra::factory()->create([
            'estado' => EstadoOrdenCompra::Emitida,
        ]);

        $ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'cantidad' => 10,
        ]);

        $resultado = app(CalcularYPrepararRecepcion::class)->execute([
            'orden_compra_id' => $orden->id,
            'items' => [
                [
                    'orden_item_id' => $ordenItem->id,
                    'cantidad_recibida' => 5,
                    'cantidad_rechazada' => 0,
                ],
            ],
        ]);

        expect($resultado['codigo'])->toMatch('/^REC-\d{4}-\d{3,}$/');
        expect($resultado['estado'])->toBe(EstadoRecepcion::Pendiente);
        expect($resultado['items'][0]['producto_id'])->toBe($ordenItem->producto_id);
    });

    it('lanza excepcion si cantidad recibida supera la pendiente', function () {
        $orden = OrdenCompra::factory()->create([
            'estado' => EstadoOrdenCompra::Emitida,
        ]);

        $ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'cantidad' => 10,
        ]);

        expect(fn () => app(CalcularYPrepararRecepcion::class)->execute([
            'orden_compra_id' => $orden->id,
            'items' => [
                [
                    'orden_item_id' => $ordenItem->id,
                    'cantidad_recibida' => 15,
                    'cantidad_rechazada' => 0,
                ],
            ],
        ]))->toThrow(InvalidArgumentException::class, 'solo quedan 10 de 10');
    });

    it('lanza excepcion si cantidad recibida supera la pendiente considerando recepciones previas', function () {
        $orden = OrdenCompra::factory()->create([
            'estado' => EstadoOrdenCompra::Emitida,
        ]);

        $ordenItem = OrdenCompraItem::factory()->create([
            'orden_compra_id' => $orden->id,
            'cantidad' => 10,
        ]);

        $recepcionPrevia = RecepcionCompra::factory()->create([
            'orden_compra_id' => $orden->id,
            'estado' => EstadoRecepcion::Completa,
        ]);

        $recepcionPrevia->items()->create([
            'orden_item_id' => $ordenItem->id,
            'producto_id' => $ordenItem->producto_id,
            'cantidad_recibida' => 7,
            'cantidad_rechazada' => 0,
        ]);

        expect(fn () => app(CalcularYPrepararRecepcion::class)->execute([
            'orden_compra_id' => $orden->id,
            'items' => [
                [
                    'orden_item_id' => $ordenItem->id,
                    'cantidad_recibida' => 5,
                    'cantidad_rechazada' => 0,
                ],
            ],
        ]))->toThrow(InvalidArgumentException::class, 'solo quedan 3 de 10');
    });

    it('lanza excepcion si el orden_item no existe', function () {
        expect(fn () => app(CalcularYPrepararRecepcion::class)->execute([
            'orden_compra_id' => 99999,
            'items' => [
                [
                    'orden_item_id' => 99999,
                    'cantidad_recibida' => 5,
                    'cantidad_rechazada' => 0,
                ],
            ],
        ]))->toThrow(ModelNotFoundException::class);
    });
});
