<?php

declare(strict_types=1);

use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Promociones\PromocionItem;
use App\Repository\Models\Shared\Imagen;
use App\Repository\Models\Shared\Precio;
use App\Repository\Models\Shared\Stock;
use Database\Seeders\DatabaseSeeder;

test('promocion seeder siembra catalogo modular completo con precios imagenes politicas y stock', function (): void {
    $this->seed(DatabaseSeeder::class);

    expect(Promocion::query()->count())->toBeGreaterThanOrEqual(6)
        ->and(Precio::query()->where('priceable_type', Promocion::class)->count())->toBeGreaterThanOrEqual(12)
        ->and(Imagen::query()->where('imagenable_type', Promocion::class)->count())->toBeGreaterThanOrEqual(10)
        ->and(PromocionItem::query()->count())->toBeGreaterThanOrEqual(8)
        ->and(Stock::query()->where('stockable_type', Promocion::class)->count())->toBeGreaterThanOrEqual(6);

    $promoRomantica = Promocion::query()->where('codigo', 'PROM-ROMANTICA')->first();
    expect($promoRomantica)->not->toBeNull()
        ->and($promoRomantica?->imagenes->count())->toBeGreaterThanOrEqual(3)
        ->and($promoRomantica?->politicas->count())->toBeGreaterThanOrEqual(2)
        ->and($promoRomantica?->items->count())->toBeGreaterThanOrEqual(3)
        ->and($promoRomantica?->precios->count())->toBeGreaterThanOrEqual(2);
});
