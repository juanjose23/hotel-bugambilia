<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Restaurante\Plato;
use App\Repository\Models\Shared\Precio;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    if (Moneda::query()->where('codigo', 'NIO')->doesntExist()) {
        Moneda::query()->create([
            'codigo' => 'NIO',
            'nombre' => 'Córdoba Nicaragüense',
            'simbolo' => 'C$',
            'es_predeterminada' => true,
            'estado' => EstadoGeneral::Activo,
        ]);
    }

    $tipoCliente = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => 'TIPO-CLIENTE'],
        ['nombre' => 'Tipo de Cliente', 'estado' => EstadoGeneral::Activo]
    );

    Catalogo::query()->firstOrCreate(
        ['codigo' => 'REGULAR'],
        [
            'catalogo_tipo_id' => $tipoCliente->id,
            'nombre' => 'Cliente Regular',
            'estado' => EstadoGeneral::Activo,
        ]
    );
});

test('la pagina publica de restaurante responde 200 y renderiza componente inertia cuando existe restaurante activo en web', function () {
    $restaurante = Espacio::query()->updateOrCreate(
        ['codigo' => 'REST-TEST-001'],
        [
            'nombre' => 'Restaurante Prueba Bugambilias',
            'descripcion' => 'Restaurante gourmet de alta cocina',
            'tipo' => TipoEspacio::RESTAURANTE,
            'capacidad_personas' => 50,
            'estado' => EstadoEspacio::Disponible,
            'web' => true,
            'reservable' => true,
            'meta_datos' => [
                'tipo_cocina' => 'Nicaragüense Gourmet',
                'tipo_servicio' => 'A la carta',
                'horario_desayuno' => '07:00 - 10:30 AM',
                'horario_almuerzo' => '12:00 - 03:30 PM',
                'horario_cena' => '06:00 - 10:00 PM',
                'permite_delivery' => true,
                'costo_delivery' => 50,
            ],
        ]
    );

    $response = $this->get('/restaurante');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('restaurante/Restaurante')
        ->has('restaurante')
        ->where('restaurante.nombre', 'Restaurante Prueba Bugambilias')
        ->where('restaurante.tipo_cocina', 'Nicaragüense Gourmet')
        ->where('restaurante_activo', true)
    );
});

test('la pagina publica de restaurante responde 404 cuando el restaurante tiene web=false', function () {
    Espacio::query()
        ->where('tipo', TipoEspacio::RESTAURANTE)
        ->update(['web' => false]);

    $response = $this->get('/restaurante');

    $response->assertNotFound();
});

test('crea un pedido de delivery en el backend con calculo de comanda y whatsapp', function () {
    $tipoMenu = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => 'CATEGORIA-MENU'],
        ['nombre' => 'Categoría de Menú', 'estado' => EstadoGeneral::Activo]
    );

    $categoria = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT-CARNES'],
        [
            'catalogo_tipo_id' => $tipoMenu->id,
            'nombre' => 'Carnes',
            'estado' => EstadoGeneral::Activo,
        ]
    );

    $moneda = Moneda::query()->where('codigo', 'NIO')->firstOrFail();

    $plato = Plato::create([
        'codigo' => 'PLATO-TEST-'.uniqid(),
        'nombre' => 'Churrasco Típico',
        'categoria_id' => $categoria->id,
        'descripcion' => 'Corte suave con chimichurri',
        'web' => true,
        'estado' => 1,
    ]);

    Precio::create([
        'priceable_id' => $plato->id,
        'priceable_type' => Plato::class,
        'moneda_id' => $moneda->id,
        'precio' => 350.00,
        'fecha_inicio' => now()->subDay(),
        'activo' => true,
    ]);

    $payload = [
        'nombre' => 'María José Gutiérrez',
        'email' => 'maria.gutierrez@gmail.com',
        'telefono' => '+505 8888 7777',
        'direccion' => 'Barrio El Rosario, de la iglesia 2c al sur',
        'metodo_pago' => 'efectivo',
        'monto_paga_con' => 'C$ 1000',
        'notas' => 'Sin cebolla por favor',
        'items' => [
            [
                'plato_id' => $plato->id,
                'cantidad' => 2,
                'observaciones' => 'Bien cocido',
            ],
        ],
    ];

    $response = $this->postJson('/restaurante/pedido', $payload);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'subtotal' => 700.0,
        'costo_delivery' => 50.0,
        'total' => 750.0,
        'metodo_pago' => 'efectivo',
    ]);
    $this->assertDatabaseHas('pedidos', [
        'subtotal' => 700.0,
    ]);
});

test('consulta disponibilidad de mesas y horarios para una fecha', function () {
    $mesa = Espacio::firstOrCreate(
        ['codigo' => 'MESA-TEST-01'],
        [
            'nombre' => 'Mesa 1 Terraza',
            'tipo' => TipoEspacio::MESA,
            'capacidad_personas' => 4,
            'estado' => EstadoEspacio::Disponible,
            'web' => true,
            'reservable' => true,
        ]
    );

    $response = $this->getJson('/restaurante/mesas-disponibles?fecha='.date('Y-m-d').'&hora=13:00&comensales=2');

    $response->assertOk();
    $response->assertJsonStructure([
        'fecha',
        'hora',
        'duracion_horas',
        'comensales',
        'mesas_disponibles',
        'horarios_disponibles',
    ]);
});

test('crea una reservacion de mesa por turno horario exitosamente', function () {
    $mesa = Espacio::firstOrCreate(
        ['codigo' => 'MESA-TEST-02'],
        [
            'nombre' => 'Mesa VIP 2',
            'tipo' => TipoEspacio::MESA,
            'capacidad_personas' => 6,
            'estado' => EstadoEspacio::Disponible,
            'web' => true,
            'reservable' => true,
        ]
    );

    $fechaManana = date('Y-m-d', strtotime('+1 day'));

    $payload = [
        'nombre_cliente' => 'Fernando Castillo',
        'telefono_cliente' => '+505 8765 4321',
        'email_cliente' => 'fernando@ejemplo.com',
        'fecha' => $fechaManana,
        'hora' => '19:00',
        'duracion_horas' => 2,
        'adultos' => 4,
        'espacio_id' => $mesa->id,
        'notas' => 'Cena de aniversario',
    ];

    $response = $this->postJson('/restaurante/reservar-mesa', $payload);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'fecha' => $fechaManana,
        'hora' => '19:00',
    ]);
});
