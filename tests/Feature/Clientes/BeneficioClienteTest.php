<?php

declare(strict_types=1);

use App\BusinessLogic\Promociones\EvaluarReglasBeneficioCliente;
use App\Enums\Catalogos\CatalogoTipo as CatalogoTipoEnum;
use App\Enums\Promociones\TipoBeneficioCliente;
use App\Enums\Promociones\TipoReglaBeneficioCliente;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Catalogos\CatalogoTipo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Personas\PersonaNatural;
use App\Repository\Models\Promociones\PromocionBeneficio;
use App\Repository\Models\Promociones\PromocionBeneficioRegla;
use App\Repository\Queries\Promociones\ObtenerBeneficiosClienteElegiblesQuery;
use Database\Seeders\Clientes\BeneficioClienteSeeder;
use Database\Seeders\Configuracion\CatalogoSeeder;
use Database\Seeders\Configuracion\CatalogoTipoSeeder;

test('seeder de beneficios siembra catalogo completo de beneficios y reglas', function (): void {
    $this->seed(CatalogoTipoSeeder::class);
    $this->seed(CatalogoSeeder::class);
    $this->seed(BeneficioClienteSeeder::class);

    expect(PromocionBeneficio::query()->count())->toBeGreaterThanOrEqual(10)
        ->and(PromocionBeneficioRegla::query()->count())->toBeGreaterThanOrEqual(5)
        ->and(PromocionBeneficio::query()->where('codigo', 'BEN-VIP-ALOJ15')->exists())->toBeTrue()
        ->and(PromocionBeneficio::query()->where('codigo', 'BEN-CORP-DESC10')->exists())->toBeTrue()
        ->and(PromocionBeneficio::query()->where('codigo', 'BEN-BIENV-10')->exists())->toBeTrue();
});

test('evaluador de reglas filtra beneficios correctamente por segmento y condiciones', function (): void {
    $tipo = CatalogoTipo::query()->firstOrCreate(
        ['codigo' => CatalogoTipoEnum::TIPO_CLIENTE->value],
        ['nombre' => 'Tipos de Cliente', 'estado' => 1]
    );

    $catVip = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CLI_VIP', 'catalogo_tipo_id' => $tipo->id],
        ['nombre' => 'VIP', 'estado' => 1]
    );

    $catReg = Catalogo::query()->firstOrCreate(
        ['codigo' => 'CLI_REGULAR', 'catalogo_tipo_id' => $tipo->id],
        ['nombre' => 'Regular', 'estado' => 1]
    );

    $personaVip = Persona::query()->create(['primer_nombre' => 'Carlos', 'tipo_persona' => 'natural']);
    PersonaNatural::query()->create(['persona_id' => $personaVip->id, 'primer_apellido' => 'Vip']);
    $clienteVip = Cliente::query()->create(['persona_id' => $personaVip->id, 'catalogo_id' => $catVip->id, 'estado' => 1]);

    $beneficioVip = PromocionBeneficio::query()->create([
        'codigo' => 'BEN-TEST-VIP',
        'nombre' => 'Descuento VIP',
        'segmento_cliente_id' => $catVip->id,
        'tipo' => TipoBeneficioCliente::DescuentoReserva,
        'valor' => 15.00,
        'es_porcentaje' => true,
        'activo' => true,
    ]);

    PromocionBeneficioRegla::query()->create([
        'beneficio_id' => $beneficioVip->id,
        'tipo_regla' => TipoReglaBeneficioCliente::MontoMinimo,
        'operador' => '>=',
        'valor_numerico' => 100.00,
        'obligatoria' => true,
    ]);

    $query = new ObtenerBeneficiosClienteElegiblesQuery(new EvaluarReglasBeneficioCliente);

    // Cuando el monto no alcanza el mínimo
    $elegiblesBajo = $query->paraCliente($clienteVip, ['monto' => 50.00]);
    expect($elegiblesBajo->contains('id', $beneficioVip->id))->toBeFalse();

    // Cuando el monto cumple el mínimo
    $elegiblesAlto = $query->paraCliente($clienteVip, ['monto' => 150.00]);
    expect($elegiblesAlto->contains('id', $beneficioVip->id))->toBeTrue();
});
