<?php

declare(strict_types=1);

use App\Actions\Catalogos\GenerarCodigoVarianteProducto;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\ProductoVariante;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

function crearProducto(string $nombre): Producto
{
    return Producto::factory()->create(['nombre' => $nombre]);
}

function crearVariante(Producto $producto, string $codigo): ProductoVariante
{
    return ProductoVariante::create([
        'producto_id' => $producto->id,
        'codigo' => $codigo,
        'nombre_variante' => $codigo,
    ]);
}

test('genera el primer código a partir de la abreviatura del nombre del producto', function (): void {
    $producto = crearProducto('Lavadora Industrial');

    $codigo = app(GenerarCodigoVarianteProducto::class)->ejecutar($producto);

    expect($codigo)->toBe('LAV-'.$producto->id.'-A');
});

test('continúa la secuencia de códigos existentes del producto', function (): void {
    $producto = crearProducto('Lavadora Industrial');
    crearVariante($producto, 'LAV-158-A');
    crearVariante($producto, 'LAV-158-B');

    $codigo = app(GenerarCodigoVarianteProducto::class)->ejecutar($producto);

    expect($codigo)->toBe('LAV-158-C');
});

test('salta los códigos de variantes eliminadas', function (): void {
    $producto = crearProducto('Lavadora Industrial');
    crearVariante($producto, 'LAV-158-A');
    crearVariante($producto, 'LAV-158-B');
    $eliminada = crearVariante($producto, 'LAV-158-C');
    $eliminada->delete();

    $codigo = app(GenerarCodigoVarianteProducto::class)->ejecutar($producto);

    expect($codigo)->toBe('LAV-158-D');
});

test('evita colisiones con variantes de otros productos', function (): void {
    $producto = crearProducto('Lavadora Industrial');
    crearVariante($producto, 'LAV-158-A');
    crearVariante($producto, 'LAV-158-B');

    $otroProducto = crearProducto('Secadora Industrial');
    crearVariante($otroProducto, 'LAV-158-C');

    $codigo = app(GenerarCodigoVarianteProducto::class)->ejecutar($producto);

    expect($codigo)->toBe('LAV-158-D');
});
