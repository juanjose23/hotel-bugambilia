<?php

use App\Enums\Inventario\EstadoLote;
use App\Models\Catalogos\Producto;
use App\Models\Catalogos\Ubicacion;
use App\Models\Inventario\Lote;
use App\Models\Inventario\MovimientoStock;
use App\Models\User;
use App\Notifications\Inventario\CaducidadProxima;
use App\Services\Inventario\NotificadorInventario;
use App\UseCases\Inventario\Lotes\Mutations\VerificarCaducidades;
use App\UseCases\Inventario\Services\PutawayPolicy;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Notification;

use function Pest\Laravel\mock;

beforeEach(function () {
    // Reset static cache in PutawayPolicy to prevent test pollution
    $ref = new ReflectionClass(PutawayPolicy::class);
    $ref->setStaticPropertyValue('cache', null);

    $this->user = User::factory()->create();
    $this->actingAs($this->user);

    $this->producto = Producto::factory()->create();
    $this->ubicacion = Ubicacion::create([
        'nombre' => 'Almacén Principal',
        'tipo' => 'zona',
        'estado' => 1,
    ]);
    $this->ubicacionMerma = Ubicacion::create([
        'nombre' => 'Zona de Merma y Desecho',
        'tipo' => 'zona',
        'estado' => 1,
    ]);
});

it('vence los lotes que han superado su fecha de vencimiento y los mueve a la zona de merma', function () {
    // Lote 1: Ya vencido
    $loteVencido = Lote::create([
        'codigo_lote' => 'LOT-CADUCADO-1',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->subMonths(6)->toDateString(),
        'fecha_vencimiento' => now()->subDays(2)->toDateString(), // Vencido hace 2 días
    ]);

    // Lote 2: Aún vigente (no debe vencerse)
    $loteVigente = Lote::create([
        'codigo_lote' => 'LOT-VIGENTE-1',
        'producto_id' => $this->producto->id,
        'estado' => EstadoLote::Disponible,
        'cantidad_disponible' => 50.0,
        'cantidad_inicial' => 50.0,
        'ubicacion_id' => $this->ubicacion->id,
        'fecha_recepcion' => now()->toDateString(),
        'fecha_vencimiento' => now()->addMonths(6)->toDateString(),
    ]);

    // Mock notificador
    $notificador = mock(NotificadorInventario::class);
    $notificador->shouldReceive('loteCaducado')
        ->once()
        ->withArgs(fn ($arg) => $arg->id === $loteVencido->id);

    // No esperamos notificaciones de próximo a caducar para estos lotes en esta prueba
    $notificador->shouldReceive('loteProximoACaducar');
    $this->app->instance(NotificadorInventario::class, $notificador);

    $verificar = app(VerificarCaducidades::class);
    $verificar->execute();

    // Lote 1 debe estar vencido y con cantidad 0
    expect($loteVencido->fresh()->estado)->toBe(EstadoLote::Vencido);
    expect($loteVencido->fresh()->cantidad_disponible)->toBe(0.0);
    expect($loteVencido->fresh()->ubicacion_id)->toBe($this->ubicacion->id);

    // Lote 2 debe seguir disponible e intacto
    expect($loteVigente->fresh()->estado)->toBe(EstadoLote::Disponible);
    expect($loteVigente->fresh()->cantidad_disponible)->toBe(50.0);

    // Debe haberse creado un movimiento de stock para el lote vencido
    $movimiento = MovimientoStock::where('lote_id', $loteVencido->id)->first();
    expect($movimiento)->not->toBeNull();
    expect($movimiento->tipo)->toBe('MOV_AJUSTE');
    expect($movimiento->cantidad)->toBe(50.0);
    expect($movimiento->ubicacion_origen_id)->toBe($this->ubicacion->id);
    expect($movimiento->ubicacion_destino_id)->toBe($this->ubicacionMerma->id);
});

it('notifica y envia correos para los lotes proximos a caducar en los siguientes 30 dias', function () {
    Notification::fake();

    try {
        // Lote próximo a caducar en 15 días
        $loteProximo = Lote::create([
            'codigo_lote' => 'LOT-PROXIMO-2',
            'producto_id' => $this->producto->id,
            'estado' => EstadoLote::Disponible,
            'cantidad_disponible' => 20.0,
            'cantidad_inicial' => 20.0,
            'ubicacion_id' => $this->ubicacion->id,
            'fecha_recepcion' => now()->toDateString(),
            'fecha_vencimiento' => now()->addDays(15)->toDateString(),
        ]);

        // Mock notificador
        $notificador = mock(NotificadorInventario::class);
        $notificador->shouldReceive('loteProximoACaducar')
            ->once()
            ->withArgs(fn ($arg, $dias) => $arg->id === $loteProximo->id && is_int($dias));
        $this->app->instance(NotificadorInventario::class, $notificador);

        $verificar = app(VerificarCaducidades::class);
        $verificar->execute();

        // Verificar que la notificación de correo fue enviada
        Notification::assertSentTo(
            new AnonymousNotifiable,
            CaducidadProxima::class
        );
    } finally {
        Notification::swap(app(ChannelManager::class));
    }
});
