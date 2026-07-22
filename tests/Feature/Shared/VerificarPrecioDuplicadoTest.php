<?php

use App\Models\Monedas\Moneda;
use App\Models\Shared\Precio;
use App\Repository\Queries\Shared\VerificarPrecioDuplicado;

beforeEach(function () {
    $this->moneda = Moneda::create([
        'codigo' => 'NIO',
        'nombre' => 'Cordoba',
        'simbolo' => 'C$',
    ]);
});

describe('VerificarPrecioDuplicado', function () {
    it('retorna false cuando no existe precio vigente duplicado', function () {
        $resultado = app(VerificarPrecioDuplicado::class)->ejecutar(
            modelClass: Precio::class,
            parentId: 999,
            monedaId: $this->moneda->id,
        );

        expect($resultado)->toBeFalse();
    });

    it('retorna true cuando existe precio vigente duplicado', function () {
        Precio::create([
            'priceable_id' => 1,
            'priceable_type' => 'App\Models\Servicios\Servicio',
            'moneda_id' => $this->moneda->id,
            'precio' => 100.00,
            'fecha_inicio' => now(),
            'estado' => 1,
            'es_oferta' => false,
        ]);

        $resultado = app(VerificarPrecioDuplicado::class)->ejecutar(
            modelClass: Precio::class,
            parentId: 1,
            monedaId: $this->moneda->id,
            foreignType: 'priceable_type',
            parentType: 'App\Models\Servicios\Servicio',
        );

        expect($resultado)->toBeTrue();
    });

    it('ignora precios en estado inactivo', function () {
        Precio::create([
            'priceable_id' => 1,
            'priceable_type' => 'App\Models\Servicios\Servicio',
            'moneda_id' => $this->moneda->id,
            'precio' => 100.00,
            'fecha_inicio' => now(),
            'estado' => 0,
            'es_oferta' => false,
        ]);

        $resultado = app(VerificarPrecioDuplicado::class)->ejecutar(
            modelClass: Precio::class,
            parentId: 1,
            monedaId: $this->moneda->id,
        );

        expect($resultado)->toBeFalse();
    });

    it('ignora precios marcados como oferta', function () {
        Precio::create([
            'priceable_id' => 1,
            'priceable_type' => 'App\Models\Servicios\Servicio',
            'moneda_id' => $this->moneda->id,
            'precio' => 100.00,
            'fecha_inicio' => now(),
            'estado' => 1,
            'es_oferta' => true,
        ]);

        $resultado = app(VerificarPrecioDuplicado::class)->ejecutar(
            modelClass: Precio::class,
            parentId: 1,
            monedaId: $this->moneda->id,
        );

        expect($resultado)->toBeFalse();
    });

    it('excluye el precio actual cuando se proporciona excludeId', function () {
        $precio = Precio::create([
            'priceable_id' => 1,
            'priceable_type' => 'App\Models\Servicios\Servicio',
            'moneda_id' => $this->moneda->id,
            'precio' => 100.00,
            'fecha_inicio' => now(),
            'estado' => 1,
            'es_oferta' => false,
        ]);

        $resultado = app(VerificarPrecioDuplicado::class)->ejecutar(
            modelClass: Precio::class,
            parentId: 1,
            monedaId: $this->moneda->id,
            excludeId: $precio->id,
        );

        expect($resultado)->toBeFalse();
    });

    it('considera el tipo_precio cuando se proporciona', function () {
        Precio::create([
            'priceable_id' => 1,
            'priceable_type' => 'App\Models\Servicios\Servicio',
            'moneda_id' => $this->moneda->id,
            'precio' => 100.00,
            'fecha_inicio' => now(),
            'estado' => 1,
            'es_oferta' => false,
            'tipo_precio' => 'base',
        ]);

        // Mismo parent, misma moneda, pero tipo_precio diferente
        $resultado = app(VerificarPrecioDuplicado::class)->ejecutar(
            modelClass: Precio::class,
            parentId: 1,
            monedaId: $this->moneda->id,
            tipoPrecio: 'otro',
        );

        expect($resultado)->toBeFalse();
    });
});
