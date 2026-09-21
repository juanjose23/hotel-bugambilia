<?php

declare(strict_types=1);

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Interactors\Espacios\SincronizarActivosEspacio;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\User;

test('sincroniza correctamente los activos asignados a un espacio', function () {
    $user = User::factory()->create();
    $producto = Producto::factory()->create();

    $espacio = Espacio::factory()->create([
        'codigo' => 'MESA-0010',
        'nombre' => 'Mesa 10',
        'tipo' => TipoEspacio::MESA,
        'estado' => EstadoEspacio::Disponible,
    ]);

    $activoMesa = Activo::create([
        'codigo_inventario' => 'ACT-MESA-01',
        'nombre_descriptivo' => 'Mesa Madera 4P',
        'producto_id' => $producto->id,
        'fecha_adquisicion' => now()->toDateString(),
        'costo_adquisicion' => 150.00,
        'estado' => EstadoActivo::Activo,
    ]);

    $activoSilla1 = Activo::create([
        'codigo_inventario' => 'ACT-SILLA-01',
        'nombre_descriptivo' => 'Silla Comedor 1',
        'producto_id' => $producto->id,
        'fecha_adquisicion' => now()->toDateString(),
        'costo_adquisicion' => 45.00,
        'estado' => EstadoActivo::Activo,
    ]);

    $activoSilla2 = Activo::create([
        'codigo_inventario' => 'ACT-SILLA-02',
        'nombre_descriptivo' => 'Silla Comedor 2',
        'producto_id' => $producto->id,
        'fecha_adquisicion' => now()->toDateString(),
        'costo_adquisicion' => 45.00,
        'estado' => EstadoActivo::Activo,
    ]);

    /** @var SincronizarActivosEspacio $sincronizador */
    $sincronizador = app(SincronizarActivosEspacio::class);

    // 1. Asignar mesa y silla 1
    $sincronizador->ejecutar($espacio, [$activoMesa->id, $activoSilla1->id], (int) $user->id);

    $espacio->refresh();
    expect($espacio->inventarioFijo)->toHaveCount(2)
        ->and($espacio->inventarioFijo->pluck('activo_id')->all())->toContain($activoMesa->id, $activoSilla1->id);

    // 2. Reemplazar silla 1 por silla 2 (manteniendo la mesa)
    $sincronizador->ejecutar($espacio, [$activoMesa->id, $activoSilla2->id], (int) $user->id);

    $espacio->refresh();
    expect($espacio->inventarioFijo)->toHaveCount(2)
        ->and($espacio->inventarioFijo->pluck('activo_id')->all())->toContain($activoMesa->id, $activoSilla2->id)
        ->and($espacio->inventarioFijo->pluck('activo_id')->all())->not->toContain($activoSilla1->id);

    // Verificar que la asignación de silla 1 quedó cerrada
    $asignacionCerrada = $activoSilla1->asignaciones()->where('estado', EstadoAsignacion::Cerrada)->first();
    expect($asignacionCerrada)->not->toBeNull()
        ->and($asignacionCerrada->fecha_fin)->not->toBeNull();
});
