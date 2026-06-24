<?php

declare(strict_types=1);

use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Catalogos\Ubicacion;
use App\Models\User;
use App\UseCases\Catalogos\Mutations\ImportProductosUseCase;
use App\UseCases\Catalogos\Queries\ExportProductosUseCase;
use App\UseCases\Catalogos\Queries\GenerarCodigoBarrasUseCase;
use App\UseCases\Catalogos\Queries\ObtenerArbolUbicaciones;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

// ─── Helpers ────────────────────────────────────────────────────────────

function generarCsvTemp(string $contenido): string
{
    $path = tempnam(sys_get_temp_dir(), 'csv_');
    file_put_contents($path, $contenido);

    return $path;
}

// ─── ImportProductosUseCase ─────────────────────────────────────────────

beforeEach(function (): void {
    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
    ]);
    // Use an existing CATEGORIA_PRODUCTO catalog id for CSV imports
    $this->categoriaId = Catalogo::where('codigo', 'CAT_PRO_GENERAL')->value('id');
});

it('retorna error si el archivo CSV no existe', function () {
    $result = app(ImportProductosUseCase::class)->importarDesdeCsv('ruta/inexistente.csv');

    expect($result)->toBe([
        'processed' => 0,
        'errors' => ['file_not_found'],
    ]);
});

it('importa productos desde un CSV valido', function () {
    $csvContent = "nombre,descripcion,categoria_id,tipo,estado\n".
        "Producto A,Desc A,{$this->categoriaId},2,1\n".
        "Producto B,Desc B,{$this->categoriaId},1,1\n";
    $path = generarCsvTemp($csvContent);

    $result = app(ImportProductosUseCase::class)->importarDesdeCsv($path);

    expect($result['processed'])->toBe(2)
        ->and($result['errors'])->toBe([]);

    expect(Producto::where('nombre', 'Producto A')->exists())->toBeTrue()
        ->and(Producto::where('nombre', 'Producto B')->exists())->toBeTrue();
});

it('importa productos con variantes desde CSV', function () {
    $csvContent = "nombre,descripcion,categoria_id,variante_codigo,variante_nombre\n".
        "Producto C,Desc C,{$this->categoriaId},VAR001,Talla M\n";
    $path = generarCsvTemp($csvContent);

    $result = app(ImportProductosUseCase::class)->importarDesdeCsv($path);

    expect($result['processed'])->toBe(1);

    $producto = Producto::where('nombre', 'Producto C')->first();
    expect($producto)->not->toBeNull();
    expect($producto->variantes)->toHaveCount(1);
    expect($producto->variantes->first()->codigo)->toBe('VAR001');
});

it('salta filas malformadas del CSV', function () {
    $csvContent = "nombre,descripcion,categoria_id\n".
        "Producto D,Desc D,{$this->categoriaId}\n".
        "fila_malformada_sin_comillas\n";
    $path = generarCsvTemp($csvContent);

    $result = app(ImportProductosUseCase::class)->importarDesdeCsv($path);

    expect($result['processed'])->toBe(1);
});

// ─── ExportProductosUseCase ─────────────────────────────────────────────

it('exporta productos a xlsx y retorna la ruta del archivo', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
    ]);

    Producto::factory()->count(3)->create();

    Storage::fake('private');

    $result = app(ExportProductosUseCase::class)->exportarCsv([]);

    expect($result)->toBeString();
    expect(Storage::disk('private')->exists('exports'))->toBeTrue();
});

it('aplica filtros al exportar productos', function () {
    $user = User::factory()->create();
    $this->actingAs($user);
    Storage::fake('private');

    $result = app(ExportProductosUseCase::class)->exportarCsv([
        'estado' => 1,
    ]);

    expect($result)->toBeString();
});

// ─── GenerarCodigoBarrasUseCase ─────────────────────────────────────────

it('genera codigo de barras desde el nombre del producto cuando no hay variante', function () {
    $producto = Producto::factory()->create(['nombre' => 'Jabon Liquido']);

    $codigo = app(GenerarCodigoBarrasUseCase::class)->ejecutar($producto);

    expect($codigo)->toBe('JabonLiquido');
});

it('genera codigo de barras desde el codigo de la variante', function () {
    $producto = Producto::factory()->create(['nombre' => 'Jabon Liquido']);
    $variante = ProductoVariante::create([
        'producto_id' => $producto->id,
        'codigo' => 'JAB001',
        'nombre_variante' => '500ml',
        'estado' => 1,
    ]);

    $codigo = app(GenerarCodigoBarrasUseCase::class)->ejecutar($producto, $variante);

    expect($codigo)->toBe('JAB001');
});

it('limpia espacios y caracteres especiales del codigo de barras', function () {
    $producto = Producto::factory()->create(['nombre' => 'Jabon Liquido']);
    $variante = ProductoVariante::create([
        'producto_id' => $producto->id,
        'codigo' => 'JAB 001 / A',
        'nombre_variante' => '1L',
        'estado' => 1,
    ]);

    $codigo = app(GenerarCodigoBarrasUseCase::class)->ejecutar($producto, $variante);

    expect($codigo)->toBe('JAB001A');
});

it('genera lote de codigos de barras para todas las variantes', function () {
    $producto = Producto::factory()->create(['nombre' => 'Shampoo']);
    ProductoVariante::create([
        'producto_id' => $producto->id,
        'codigo' => 'SH001',
        'nombre_variante' => '200ml',
        'estado' => 1,
    ]);
    ProductoVariante::create([
        'producto_id' => $producto->id,
        'codigo' => 'SH002',
        'nombre_variante' => '400ml',
        'estado' => 1,
    ]);

    $codigos = app(GenerarCodigoBarrasUseCase::class)->generarLote($producto);

    expect($codigos)->toHaveCount(2);
    expect($codigos)->toContain('SH001');
    expect($codigos)->toContain('SH002');
});

it('genera lote con un solo codigo si el producto no tiene variantes', function () {
    $producto = Producto::factory()->create(['nombre' => 'Toalla Algodon']);

    $codigos = app(GenerarCodigoBarrasUseCase::class)->generarLote($producto);

    expect($codigos)->toHaveCount(1);
});

// ─── ObtenerArbolUbicaciones ───────────────────────────────────────────

it('construye arbol jerarquico de ubicaciones con hijos', function () {
    $padre = Ubicacion::create([
        'nombre' => 'Edificio Principal',
        'tipo' => 'edificio',
        'orden' => 1,
        'estado' => 1,
    ]);
    $hijo = Ubicacion::create([
        'padre_id' => $padre->id,
        'nombre' => 'Planta Baja',
        'tipo' => 'piso',
        'orden' => 1,
        'estado' => 1,
    ]);
    $nieto = Ubicacion::create([
        'padre_id' => $hijo->id,
        'nombre' => 'Recepción',
        'tipo' => 'sector',
        'orden' => 1,
        'estado' => 1,
    ]);

    $arbol = app(ObtenerArbolUbicaciones::class)->execute();

    expect($arbol)->toHaveCount(1);
    expect($arbol[0]['nombre'])->toBe('Edificio Principal');
    expect($arbol[0]['children'])->toHaveCount(1);
    expect($arbol[0]['children'][0]['nombre'])->toBe('Planta Baja');
    expect($arbol[0]['children'][0]['children'])->toHaveCount(1);
    expect($arbol[0]['children'][0]['children'][0]['nombre'])->toBe('Recepción');
});

it('retorna arbol vacio cuando no hay ubicaciones', function () {
    $arbol = app(ObtenerArbolUbicaciones::class)->execute();

    expect($arbol)->toBe([]);
});

it('ordena ubicaciones por el campo orden en el arbol', function () {
    Ubicacion::create(['nombre' => 'Zona B', 'tipo' => 'zona', 'orden' => 2, 'estado' => 1]);
    Ubicacion::create(['nombre' => 'Zona A', 'tipo' => 'zona', 'orden' => 1, 'estado' => 1]);

    $arbol = app(ObtenerArbolUbicaciones::class)->execute();

    expect($arbol[0]['nombre'])->toBe('Zona A');
    expect($arbol[1]['nombre'])->toBe('Zona B');
});

it('construye multiples raices en el arbol', function () {
    Ubicacion::create(['nombre' => 'Edificio A', 'tipo' => 'edificio', 'orden' => 1, 'estado' => 1]);
    Ubicacion::create(['nombre' => 'Edificio B', 'tipo' => 'edificio', 'orden' => 2, 'estado' => 1]);

    $arbol = app(ObtenerArbolUbicaciones::class)->execute();

    expect($arbol)->toHaveCount(2);
});
