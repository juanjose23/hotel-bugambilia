<?php

declare(strict_types=1);

use App\Filament\Resources\Servicios\Servicios\RelationManagers\PreciosRelationManager;
use App\Models\Monedas\Moneda;
use App\Models\Servicios\Servicio;
use App\Models\Shared\Precio;
use App\UseCases\Servicios\Mutations\GenerarCodigoServicio;
use Filament\Forms\Components\Component;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->moneda = Moneda::create([
        'id' => 1,
        'codigo' => 'USD',
        'nombre' => 'Dólar',
        'simbolo' => '$',
        'es_predeterminada' => true,
    ]);
});

it('genera codigo secuencial de servicio', function () {
    // Generate first
    $codigo1 = app(GenerarCodigoServicio::class)->ejecutar();
    expect($codigo1)->toBe('SRV-0001');

    // Create service
    $srv1 = Servicio::create([
        'codigo' => $codigo1,
        'nombre' => 'Spa Relax',
        'estado' => 1,
    ]);

    // Generate second
    $codigo2 = app(GenerarCodigoServicio::class)->ejecutar();
    expect($codigo2)->toBe('SRV-0002');

    // Soft delete first and generate again - should still be sequential based on max id or deleted records
    $srv1->delete();
    $codigo3 = app(GenerarCodigoServicio::class)->ejecutar();
    expect($codigo3)->toBe('SRV-0002');
});

it('valida que no existan precios vigentes duplicados para el mismo servicio y moneda', function () {
    // Create a service
    $srv = Servicio::create([
        'codigo' => 'SRV-0001',
        'nombre' => 'Spa Relax',
        'estado' => 1,
    ]);

    // Create a first price active
    $precio1 = Precio::create([
        'priceable_type' => Servicio::class,
        'priceable_id' => $srv->id,
        'moneda_id' => $this->moneda->id,
        'precio' => 100.00,
        'fecha_inicio' => now()->toDateString(),
        'estado' => 1,
        'es_oferta' => false,
    ]);

    // Now let's test our rule on a duplicate price edit/creation
    $relationManager = mock(PreciosRelationManager::class)->makePartial();
    $relationManager->shouldReceive('getOwnerRecord')->andReturn($srv);

    // Call getUniquePrecioVigenteRule
    $ruleClosure = $relationManager->getUniquePrecioVigenteRule();

    // Mock $get and $component
    $get = fn (string $field) => match ($field) {
        'estado' => 1,
        'es_oferta' => false,
        'moneda_id' => $this->moneda->id,
        default => null,
    };

    $component = mock(Component::class);
    $component->shouldReceive('getRecord')->andReturn(null); // Creating new price

    // Invoke the first closure to get the actual Laravel validator closure
    $laravelRule = $ruleClosure($get, $component);

    // The validation should fail
    $failed = false;
    $laravelRule('moneda_id', $this->moneda->id, function (string $message) use (&$failed) {
        $failed = true;
        expect($message)->toContain('Ya existe un precio vigente activo');
    });

    expect($failed)->toBeTrue();
});

it('permite guardar al editar el mismo registro de precio activo', function () {
    // Create a service
    $srv = Servicio::create([
        'codigo' => 'SRV-0001',
        'nombre' => 'Spa Relax',
        'estado' => 1,
    ]);

    // Create a price active
    $precio1 = Precio::create([
        'priceable_type' => Servicio::class,
        'priceable_id' => $srv->id,
        'moneda_id' => $this->moneda->id,
        'precio' => 100.00,
        'fecha_inicio' => now()->toDateString(),
        'estado' => 1,
        'es_oferta' => false,
    ]);

    $relationManager = mock(PreciosRelationManager::class)->makePartial();
    $relationManager->shouldReceive('getOwnerRecord')->andReturn($srv);

    $ruleClosure = $relationManager->getUniquePrecioVigenteRule();

    $get = fn (string $field) => match ($field) {
        'estado' => 1,
        'es_oferta' => false,
        'moneda_id' => $this->moneda->id,
        default => null,
    };

    $component = mock(Component::class);
    $component->shouldReceive('getRecord')->andReturn($precio1); // Editing the same price

    $laravelRule = $ruleClosure($get, $component);

    $failed = false;
    $laravelRule('moneda_id', $this->moneda->id, function (string $message) use (&$failed) {
        $failed = true;
    });

    expect($failed)->toBeFalse(); // Should not fail because we are editing the existing record!
});

it('puede guardar y obtener un servicio con icono e imagenes', function () {
    $srv = Servicio::create([
        'codigo' => 'SRV-1234',
        'nombre' => 'Spa Deluxe',
        'icono' => 'heroicon-o-sparkles',
        'estado' => 1,
    ]);

    // Create related polymorphic images
    $srv->imagenes()->createMany([
        ['url' => 'servicios/galeria/image1.jpg', 'orden' => 1],
        ['url' => 'servicios/galeria/image2.jpg', 'orden' => 2],
    ]);

    expect($srv->icono)->toBe('heroicon-o-sparkles');
    expect($srv->imagenes)->toHaveCount(2);
    expect($srv->imagenes->first()->url)->toBe('servicios/galeria/image1.jpg');
    expect($srv->imagenes->first()->orden)->toBe(1);

    // Retrieve from database and check morph relationship
    $retrieved = Servicio::with('imagenes')->find($srv->id);
    expect($retrieved->icono)->toBe('heroicon-o-sparkles');
    expect($retrieved->imagenes)->toHaveCount(2);
    expect($retrieved->imagenes->last()->url)->toBe('servicios/galeria/image2.jpg');
    expect($retrieved->imagenes->last()->orden)->toBe(2);
});
