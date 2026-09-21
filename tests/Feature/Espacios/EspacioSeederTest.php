<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\Stock;
use Database\Seeders\DatabaseSeeder;

test('espacio seeder siembra catalogo modular completo con mesas imagenes precios politicas y stock', function (): void {
    $this->seed(DatabaseSeeder::class);

    // 1. Espacios principales y sub-espacios (Mesas)
    $espaciosPrincipales = Espacio::query()->whereNull('padre_id')->get();
    $subEspacios = Espacio::query()->whereNotNull('padre_id')->get();

    expect($espaciosPrincipales->count())->toBeGreaterThanOrEqual(7)
        ->and($subEspacios->count())->toBeGreaterThanOrEqual(6);

    // 2. Comprobar que los espacios web están configurados
    $espaciosWeb = Espacio::query()->whereNull('padre_id')->where('web', true)->get();
    expect($espaciosWeb->count())->toBeGreaterThanOrEqual(7);

    // 3. Comprobar Restaurante Bugambilias con sus sub-espacios (mesas) y galería
    $restaurante = Espacio::query()->where('codigo', 'REST-001')->first();
    expect($restaurante)->not->toBeNull()
        ->and($restaurante?->tipo)->toBe(TipoEspacio::RESTAURANTE)
        ->and($restaurante?->hijos->count())->toBeGreaterThanOrEqual(6)
        ->and($restaurante?->imagenes->count())->toBeGreaterThanOrEqual(3)
        ->and($restaurante?->web)->toBeTrue()
        ->and($restaurante?->reservable)->toBeTrue();

    // 4. Comprobar Gimnasio (Web pero no reservable como alquiler de sala, acceso libre huéspedes)
    $gym = Espacio::query()->where('codigo', 'GYM-001')->first();
    expect($gym)->not->toBeNull()
        ->and($gym?->tipo)->toBe(TipoEspacio::GYM)
        ->and($gym?->web)->toBeTrue()
        ->and($gym?->reservable)->toBeFalse()
        ->and($gym?->imagenes->count())->toBeGreaterThanOrEqual(2);

    // 5. Comprobar precios vigentes e históricos y stock polimórfico
    $preciosEspacios = Precio::query()->where('priceable_type', Espacio::class)->get();
    $imagenesEspacios = Imagen::query()->where('imagenable_type', Espacio::class)->get();
    $stockEspacios = Stock::query()->where('stockable_type', Espacio::class)->get();

    expect($preciosEspacios->count())->toBeGreaterThanOrEqual(10)
        ->and($imagenesEspacios->count())->toBeGreaterThanOrEqual(14)
        ->and($stockEspacios->count())->toBeGreaterThanOrEqual(6);
});

test('servicios y espacios reflejan correctamente visibilidad web para portal y consumo publico', function (): void {
    $this->seed(DatabaseSeeder::class);

    // Espacios activos en web
    $espaciosPublicos = Espacio::query()->activosWeb()->get();
    expect($espaciosPublicos->count())->toBeGreaterThanOrEqual(7)
        ->and($espaciosPublicos->every(fn (Espacio $e) => $e->web && $e->estado !== EstadoEspacio::Inactivo))->toBeTrue();

    // Servicios activos en web (debe haber servicios públicos como Masajes, Tours, Traslados)
    $serviciosPublicos = Servicio::query()->activosWeb()->get();
    expect($serviciosPublicos->count())->toBeGreaterThanOrEqual(15);

    // Servicios marcados para uso interno (como Alquiler de Laptop corporativo, Valet Parking, etc.)
    $serviciosInternos = Servicio::query()->where('web', false)->get();
    expect($serviciosInternos->count())->toBeGreaterThanOrEqual(2);
});
