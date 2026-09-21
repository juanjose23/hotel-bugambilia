<?php

declare(strict_types=1);

use App\BusinessLogic\Activos\GeneradorCodigoInventario;
use App\BusinessLogic\Activos\GeneradorPrefijo;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Persistencia\Activos\PrefijoCodigoRepositorioInterface;
use Tests\TestCase;

uses(TestCase::class);

test('genera prefijo desde la categoria del catalogo cuando tiene prefijo', function (): void {
    $generador = new GeneradorPrefijo;

    $categoria = new Catalogo([
        'nombre' => 'Televisores LED',
        'prefijo' => 'TV',
    ]);

    $producto = new Producto([
        'nombre' => 'Samsung 55 Pulgadas 4K',
    ]);
    $producto->setRelation('categoria', $categoria);

    $prefijo = $generador->generarDesdeProducto($producto);

    expect($prefijo)->toBe('TV');
});

test('genera prefijo limpio desde el nombre de la categoria cuando no tiene prefijo explicito', function (): void {
    $generador = new GeneradorPrefijo;

    $categoria = new Catalogo([
        'nombre' => 'Mobiliario',
        'prefijo' => null,
    ]);

    $producto = new Producto([
        'nombre' => 'Silla Ejecutiva Ergonómica',
    ]);
    $producto->setRelation('categoria', $categoria);

    $prefijo = $generador->generarDesdeProducto($producto);

    expect($prefijo)->toBe('MOB');
});

test('generador de codigo inventario utiliza el prefijo del catalogo dinamicamente', function (): void {
    $fakeRepo = new class implements PrefijoCodigoRepositorioInterface
    {
        public ?string $prefijoRecibido = null;

        public function generarSiguienteCodigo(string $prefijo): string
        {
            $this->prefijoRecibido = $prefijo;

            return $prefijo.'-2026-0001';
        }
    };

    $generadorPrefijo = new GeneradorPrefijo;
    $generadorCodigo = new GeneradorCodigoInventario($fakeRepo, $generadorPrefijo);

    $categoria = new Catalogo([
        'nombre' => 'Aires Acondicionados',
        'prefijo' => 'AC',
    ]);

    $producto = new Producto([
        'nombre' => 'Inverter 18000 BTU',
    ]);
    $producto->setRelation('categoria', $categoria);

    $codigo = $generadorCodigo->generar($producto);

    expect($codigo)->toBe('AC-2026-0001')
        ->and($fakeRepo->prefijoRecibido)->toBe('AC');
});
