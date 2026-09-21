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

    Espacio::query()->updateOrCreate(
        ['codigo' => 'REST-CHECKOUT-001'],
        [
            'nombre' => 'Restaurante Bugambilias',
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
});

test('la pagina de checkout de restaurante responde 200 y renderiza componente inertia con departamentos configurados', function () {
    $response = $this->get('/restaurante/checkout');

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('restaurante/RestauranteCheckout')
        ->has('restaurante')
        ->has('departamentos_delivery')
        ->where('restaurante.nombre', 'Restaurante Bugambilias')
    );
});

test('crea un pedido de delivery con departamento y municipio validos', function () {
    $tipoMenu = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => 'CATEGORIA-MENU'],
        ['nombre' => 'Categoría de Menú', 'estado' => EstadoGeneral::Activo]
    );

    $categoria = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT-BEBIDAS'],
        [
            'catalogo_tipo_id' => $tipoMenu->id,
            'nombre' => 'Bebidas',
            'estado' => EstadoGeneral::Activo,
        ]
    );

    $moneda = Moneda::query()->where('codigo', 'NIO')->firstOrFail();

    $plato = Plato::create([
        'codigo' => 'PLATO-CHECKOUT-'.uniqid(),
        'nombre' => 'Jugo Natural de Pitahaya',
        'categoria_id' => $categoria->id,
        'descripcion' => 'Bebida refrescante típica',
        'web' => true,
        'estado' => 1,
    ]);

    Precio::create([
        'priceable_id' => $plato->id,
        'priceable_type' => Plato::class,
        'moneda_id' => $moneda->id,
        'precio' => 80.00,
        'fecha_inicio' => now()->subDay(),
        'activo' => true,
    ]);

    $payload = [
        'nombre' => 'Carlos Mendoza',
        'email' => 'carlos.mendoza@gmail.com',
        'telefono' => '+505 8899 0011',
        'departamento' => 'Estelí',
        'municipio' => 'Estelí',
        'direccion' => 'Del Parque Central 1c al sur, casa esquinera #12',
        'metodo_pago' => 'efectivo',
        'monto_paga_con' => 'C$ 500',
        'notas' => 'Con poco hielo',
        'items' => [
            [
                'plato_id' => $plato->id,
                'cantidad' => 2,
                'observaciones' => 'Sin azúcar extra',
            ],
        ],
    ];

    $response = $this->postJson('/restaurante/pedido', $payload);

    $response->assertStatus(201);
    $response->assertJson([
        'success' => true,
        'subtotal' => 160.0,
        'costo_delivery' => 50.0,
        'total' => 210.0,
        'metodo_pago' => 'efectivo',
    ]);
});

test('falla creacion de pedido si el departamento no tiene cobertura activa', function () {
    $tipoMenu = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => 'CATEGORIA-MENU'],
        ['nombre' => 'Categoría de Menú', 'estado' => EstadoGeneral::Activo]
    );

    $categoria = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CAT-GENERAL'],
        [
            'catalogo_tipo_id' => $tipoMenu->id,
            'nombre' => 'General',
            'estado' => EstadoGeneral::Activo,
        ]
    );

    $moneda = Moneda::query()->where('codigo', 'NIO')->firstOrFail();

    $plato = Plato::create([
        'codigo' => 'PLATO-ERR-'.uniqid(),
        'nombre' => 'Plato Prueba',
        'categoria_id' => $categoria->id,
        'descripcion' => 'Prueba',
        'web' => true,
        'estado' => 1,
    ]);

    Precio::create([
        'priceable_id' => $plato->id,
        'priceable_type' => Plato::class,
        'moneda_id' => $moneda->id,
        'precio' => 100.00,
        'fecha_inicio' => now()->subDay(),
        'activo' => true,
    ]);

    $payload = [
        'nombre' => 'Juan Pérez',
        'email' => 'juan.perez@gmail.com',
        'telefono' => '+505 8899 0011',
        'departamento' => 'Madriz', // En la configuración está inactivo
        'municipio' => 'Somoto',
        'direccion' => 'Calle principal Somoto',
        'metodo_pago' => 'efectivo',
        'items' => [
            [
                'plato_id' => $plato->id,
                'cantidad' => 1,
            ],
        ],
    ];

    $response = $this->postJson('/restaurante/pedido', $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['items']);
});

test('falla si la direccion es demasiado corta', function () {
    $payload = [
        'nombre' => 'Juan Pérez',
        'email' => 'juan.perez@gmail.com',
        'telefono' => '+505 8899 0011',
        'departamento' => 'Estelí',
        'municipio' => 'Estelí',
        'direccion' => 'Casa', // < 5 caracteres
        'metodo_pago' => 'efectivo',
        'items' => [
            [
                'plato_id' => 1,
                'cantidad' => 1,
            ],
        ],
    ];

    $response = $this->postJson('/restaurante/pedido', $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['direccion']);
});

test('falla si el correo electronico no es valido', function () {
    $payload = [
        'nombre' => 'Juan Pérez',
        'email' => 'correo-invalido',
        'telefono' => '+505 8899 0011',
        'departamento' => 'Estelí',
        'municipio' => 'Estelí',
        'direccion' => 'Del Parque 1c al norte',
        'metodo_pago' => 'efectivo',
        'items' => [
            [
                'plato_id' => 1,
                'cantidad' => 1,
            ],
        ],
    ];

    $response = $this->postJson('/restaurante/pedido', $payload);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['email']);
});
