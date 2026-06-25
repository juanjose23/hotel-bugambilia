<?php

declare(strict_types=1);

use App\Models\Catalogos\Catalogo;
use App\Models\Monedas\Moneda;
use App\Models\Servicios\Servicio;
use App\Models\Shared\Precio;
use App\Models\User;
use App\UseCases\Servicios\Queries\ObtenerHistoricoServiciosPrecios;
use App\UseCases\Servicios\Reportes\Queries\GenerarHistoricoPreciosExcel;
use App\UseCases\Servicios\Reportes\Queries\GenerarHistoricoPreciosPdf;
use Database\Seeders\CatalogoSeeder;
use Database\Seeders\CatalogoTipoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\LaravelPdf\PdfBuilder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

uses(RefreshDatabase::class);

// ─── Helpers ────────────────────────────────────────────────────────────

function crearMoneda(string $codigo, string $nombre, string $simbolo, bool $predeterminada = false): Moneda
{
    return Moneda::create([
        'codigo' => $codigo,
        'nombre' => $nombre,
        'simbolo' => $simbolo,
        'es_predeterminada' => $predeterminada,
    ]);
}

function crearServicio(string $nombre, string $codigo, int $categoriaId): Servicio
{
    return Servicio::create([
        'codigo' => $codigo,
        'nombre' => $nombre,
        'categoria_id' => $categoriaId,
        'estado' => 1,
    ]);
}

function crearPrecio(Servicio $servicio, Moneda $moneda, float $precio, string $fechaInicio, int $estado = 1, bool $esOferta = false): Precio
{
    return Precio::create([
        'priceable_type' => Servicio::class,
        'priceable_id' => $servicio->id,
        'moneda_id' => $moneda->id,
        'precio' => $precio,
        'fecha_inicio' => $fechaInicio,
        'estado' => $estado,
        'es_oferta' => $esOferta,
    ]);
}

// ─── ObtenerHistoricoServiciosPrecios ───────────────────────────────────

beforeEach(function (): void {
    $this->query = app(ObtenerHistoricoServiciosPrecios::class);

    $this->seed([
        CatalogoTipoSeeder::class,
        CatalogoSeeder::class,
    ]);

    $this->usd = crearMoneda('USD', 'Dólar', '$');
    $this->nio = crearMoneda('NIO', 'Córdoba', 'C$');

    $this->categoriaAlojamiento = Catalogo::where('codigo', 'CAT_SERV_ALOJAMIENTO')->first();
    $this->categoriaBienestar = Catalogo::where('codigo', 'CAT_SERV_BIENESTAR')->first();

    $this->servicio1 = crearServicio('Early Check-in', 'SRV-0001', $this->categoriaAlojamiento->id);
    $this->servicio2 = crearServicio('Masaje Relajante', 'SRV-0002', $this->categoriaBienestar->id);
});

it('retorna todos los historicos de precios sin filtros', function () {
    crearPrecio($this->servicio1, $this->nio, 350.00, now()->toDateString());
    crearPrecio($this->servicio2, $this->usd, 30.00, now()->toDateString());

    $result = $this->query->ejecutar();

    expect($result)->toHaveCount(2);
});

it('filtra por servicio_id', function () {
    crearPrecio($this->servicio1, $this->nio, 350.00, now()->toDateString());
    crearPrecio($this->servicio2, $this->usd, 30.00, now()->toDateString());

    $result = $this->query->ejecutar(['servicio_id' => $this->servicio1->id]);

    expect($result)->toHaveCount(1);
    expect($result->first()->servicio_id)->toBe($this->servicio1->id);
});

it('filtra por moneda_id', function () {
    crearPrecio($this->servicio1, $this->nio, 350.00, now()->toDateString());
    crearPrecio($this->servicio1, $this->usd, 10.00, now()->toDateString());

    $result = $this->query->ejecutar(['moneda_id' => $this->usd->id]);

    expect($result)->toHaveCount(1);
    expect($result->first()->moneda_id)->toBe($this->usd->id);
});

it('filtra por estado', function () {
    crearPrecio($this->servicio1, $this->nio, 350.00, now()->toDateString(), 1);
    crearPrecio($this->servicio1, $this->usd, 10.00, now()->toDateString(), 0);

    $result = $this->query->ejecutar(['estado' => 1]);

    expect($result)->toHaveCount(1);
});

it('filtra por categoria_id', function () {
    crearPrecio($this->servicio1, $this->nio, 350.00, now()->toDateString());
    crearPrecio($this->servicio2, $this->usd, 30.00, now()->toDateString());

    $result = $this->query->ejecutar(['categoria_id' => $this->categoriaAlojamiento->id]);

    expect($result)->toHaveCount(1);
    expect($result->first()->categoria_id)->toBe($this->categoriaAlojamiento->id);
});

it('retorna coleccion vacia cuando no hay precios', function () {
    $result = $this->query->ejecutar();

    expect($result)->toHaveCount(0);
});

it('retorna datos agrupados por categoria', function () {
    crearPrecio($this->servicio1, $this->nio, 350.00, now()->toDateString());
    crearPrecio($this->servicio2, $this->usd, 30.00, now()->toDateString());

    $agrupado = $this->query->agrupadoPorCategoria();

    expect($agrupado)->toHaveKeys(['Alojamiento y Estancia', 'Bienestar y Relajación']);
    expect($agrupado['Alojamiento y Estancia'])->toHaveCount(1);
    expect($agrupado['Bienestar y Relajación'])->toHaveCount(1);
});

it('agrupa servicios sin categoria como "Sin categoría"', function () {
    $servicio = Servicio::create([
        'codigo' => 'SRV-9999',
        'nombre' => 'Sin categoria',
        'categoria_id' => null,
        'estado' => 1,
    ]);
    crearPrecio($servicio, $this->nio, 100.00, now()->toDateString());

    $agrupado = $this->query->agrupadoPorCategoria();

    expect($agrupado)->toHaveKey('Sin categoría');
});

// ─── GenerarHistoricoPreciosExcel ───────────────────────────────────────

beforeEach(function (): void {
    $this->user = User::factory()->create(['name' => 'Test User']);
    $this->actingAs($this->user);
});

it('genera archivo excel de historico de precios', function () {
    crearPrecio($this->servicio1, $this->nio, 350.00, now()->toDateString());

    $response = app(GenerarHistoricoPreciosExcel::class)->ejecutar();

    expect($response)->toBeInstanceOf(BinaryFileResponse::class);
});

it('genera excel con filtros aplicados', function () {
    crearPrecio($this->servicio1, $this->nio, 350.00, now()->toDateString());
    crearPrecio($this->servicio2, $this->usd, 30.00, now()->toDateString());

    $response = app(GenerarHistoricoPreciosExcel::class)->ejecutar([
        'categoria_id' => $this->categoriaAlojamiento->id,
    ]);

    expect($response)->toBeInstanceOf(BinaryFileResponse::class);
});

// ─── GenerarHistoricoPreciosPdf ─────────────────────────────────────────

it('genera archivo pdf de historico de precios', function () {
    crearPrecio($this->servicio1, $this->nio, 350.00, now()->toDateString());

    $response = app(GenerarHistoricoPreciosPdf::class)->ejecutar();

    expect($response)->toBeInstanceOf(PdfBuilder::class);
});

it('genera pdf con filtros aplicados', function () {
    crearPrecio($this->servicio1, $this->nio, 350.00, now()->toDateString());
    crearPrecio($this->servicio2, $this->usd, 30.00, now()->toDateString());

    $response = app(GenerarHistoricoPreciosPdf::class)->ejecutar([
        'moneda_id' => $this->nio->id,
    ]);

    expect($response)->toBeInstanceOf(PdfBuilder::class);
});
