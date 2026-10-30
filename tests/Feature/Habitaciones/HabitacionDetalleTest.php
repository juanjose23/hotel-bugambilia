<?php

declare(strict_types=1);

namespace Tests\Feature\Habitaciones;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Enums\Promociones\TipoBeneficioCliente;
use App\Enums\Shared\EstadoGeneral;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Promociones\PromocionBeneficio;
use App\Repository\Models\User;

describe('Detalle de Habitación Web', function (): void {
    test('un visitante puede ver el detalle de una habitación sin errores de lazy loading', function (): void {
        $habitacion = Habitacion::query()->where('estado', EstadoEspacio::Activa)->first();

        if (! $habitacion) {
            $habitacion = Habitacion::factory()->create(['estado' => EstadoEspacio::Activa]);
        }

        $response = $this->get("/habitaciones/{$habitacion->slug}");

        $response->assertOk();
    });

    test('un cliente autenticado con beneficios asignados puede ver el detalle sin LazyLoadingViolationException en promocion', function (): void {
        $habitacion = Habitacion::query()->where('estado', EstadoEspacio::Activa)->first();

        if (! $habitacion) {
            $habitacion = Habitacion::factory()->create(['estado' => EstadoEspacio::Activa]);
        }

        $user = User::factory()->create();
        $persona = Persona::create([
            'primer_nombre' => 'Test',
            'tipo_persona' => 'natural',
            'telefono' => '+505 8888 1234',
        ]);
        $user->update(['persona_id' => $persona->id]);

        $tipoCliente = CatalogoTipo::query()->firstOrCreate(
            ['codigo' => 'TIPO-CLIENTE'],
            ['nombre' => 'Tipo de Cliente', 'estado' => EstadoGeneral::Activo]
        );

        $catalogo = Catalogo::query()->firstOrCreate(
            ['codigo' => 'REGULAR'],
            [
                'catalogo_tipo_id' => $tipoCliente->id,
                'nombre' => 'Cliente Regular',
                'estado' => EstadoGeneral::Activo,
            ]
        );

        $cliente = Cliente::create([
            'persona_id' => $persona->id,
            'catalogo_id' => $catalogo->id,
            'estado' => 1,
        ]);

        $promocion = Promocion::query()->create([
            'codigo' => 'PROM-TEST-DETALLE',
            'nombre' => 'Promo Especial Descuento',
            'descripcion' => 'Descuento exclusivo para clientes',
            'precio_paquete' => 100,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDays(30)->toDateString(),
            'estado' => 1,
            'web' => true,
        ]);

        PromocionBeneficio::create([
            'promocion_id' => $promocion->id,
            'segmento_cliente_id' => null,
            'codigo' => 'BENEF-TEST-001',
            'nombre' => 'Descuento 10%',
            'tipo' => TipoBeneficioCliente::DescuentoReserva,
            'valor' => 10.00,
            'es_porcentaje' => true,
            'combinable' => false,
            'activo' => true,
            'fecha_inicio' => now()->subDay()->toDateString(),
            'fecha_fin' => now()->addDays(30)->toDateString(),
        ]);

        $response = $this->actingAs($user)->get("/habitaciones/{$habitacion->slug}");

        $response->assertOk();
    });
});
