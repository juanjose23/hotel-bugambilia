<?php

declare(strict_types=1);

use App\Filament\Resources\Habitaciones\HabitacionResource\Pages\EditHabitacion;
use App\Interactors\Habitaciones\SincronizarGaleriaImagenesHabitacion;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

test('el formulario administrativo guarda y vuelve a cargar las vistas relacionadas', function (): void {
    $user = User::factory()->create(['is_admin' => true]);
    $user->assignRole(Role::findOrCreate('super_admin', 'web'));
    Filament::setCurrentPanel(Filament::getPanel('admin'));
    Livewire::actingAs($user);
    Storage::fake('public');
    $tipo = CatalogoTipo::query()->create(['codigo' => 'TIPO_VISTA', 'nombre' => 'Vistas', 'estado' => 1]);
    $vista = Catalogo::query()->create(['catalogo_tipo_id' => $tipo->id, 'codigo' => 'VISTA_JARDIN', 'nombre' => 'Jardin', 'estado' => 1]);
    $tipoCategoria = CatalogoTipo::query()->create(['codigo' => 'CATEGORIA_HABITACION', 'nombre' => 'Categorias de habitacion', 'estado' => 1]);
    $categoria = Catalogo::query()->create(['catalogo_tipo_id' => $tipoCategoria->id, 'codigo' => 'ESTANDAR', 'nombre' => 'Estandar', 'estado' => 1]);
    $habitacion = Habitacion::factory()->create(['categoria_id' => $categoria->id]);
    $habitacion->detalle()->create(['capacidad_adultos' => 2, 'capacidad_ninos' => 1]);
    $path = UploadedFile::fake()->image('existente.jpg')->store('habitaciones/galeria', 'public');
    $imagen = $habitacion->imagenes()->create(['url' => $path, 'public_id' => 'IMAGEN-PROPIA', 'orden' => 1]);
    $remota = $habitacion->imagenes()->create(['url' => 'https://example.com/habitacion.jpg', 'public_id' => 'IMAGEN-REMOTA', 'orden' => 2]);
    Livewire::test(EditHabitacion::class, ['record' => $habitacion->id])
        ->fillForm(['vista_ids' => [$vista->id]])->call('save')->assertHasNoFormErrors();
    expect($habitacion->vistas()->pluck('catalogos.id')->all())->toBe([$vista->id]);
    Livewire::test(EditHabitacion::class, ['record' => $habitacion->id])
        ->assertSchemaStateSet(['vista_ids' => [$vista->id]])
        ->fillForm(['vista_ids' => []])->call('save')->assertHasNoFormErrors();
    expect($habitacion->vistas()->count())->toBe(0)->and($habitacion->detalle->vistas_legacy)->toBeNull()
        ->and($habitacion->imagenes()->count())->toBe(2)
        ->and($habitacion->imagenes()->firstOrFail()->id)->toBe($imagen->id)
        ->and($imagen->refresh()->public_id)->toBe('IMAGEN-PROPIA')
        ->and($remota->refresh()->deleted_at)->toBeNull();
});

test('editar la galeria usa la relacion polimorfica y conserva las imagenes que permanecen', function (): void {
    $habitacion = Habitacion::factory()->create();
    $original = $habitacion->imagenes()->create(['url' => 'galeria/original.jpg', 'public_id' => 'ORIGINAL', 'orden' => 1]);
    app(SincronizarGaleriaImagenesHabitacion::class)->ejecutar($habitacion, ['galeria/original.jpg', 'galeria/nueva.jpg']);
    expect($habitacion->imagenes()->count())->toBe(2)
        ->and($original->refresh()->public_id)->toBe('ORIGINAL')
        ->and($original->deleted_at)->toBeNull();
    $nueva = $habitacion->imagenes()->where('url', 'galeria/nueva.jpg')->firstOrFail();
    expect($nueva->imagenable_id)->toBe($habitacion->id)->and($nueva->imagenable_type)->toBe($habitacion->getMorphClass());
});
