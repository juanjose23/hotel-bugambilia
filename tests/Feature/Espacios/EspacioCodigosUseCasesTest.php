<?php

declare(strict_types=1);

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Models\Espacios\Espacio;
use App\UseCases\Espacios\Mutations\GenerarCodigosMasivos;
use App\UseCases\Espacios\Mutations\GenerarCodigoSubEspacio;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

// ─── GenerarCodigosMasivos ───────────────────────────────────────────────────

describe('GenerarCodigosMasivos', function () {

    it('genera códigos secuenciales para mesas con prefijo MESA', function () {
        $resultados = app(GenerarCodigosMasivos::class)->execute(
            tipo: TipoEspacio::MESA,
            cantidad: 3,
        );

        expect($resultados)->toHaveCount(3);
        expect($resultados[0]['codigo'])->toMatch('/^MESA-\d{4}$/');
        expect($resultados[1]['codigo'])->toMatch('/^MESA-\d{4}$/');
        expect($resultados[2]['codigo'])->toMatch('/^MESA-\d{4}$/');

        expect(Espacio::count())->toBe(3);
        expect(Espacio::first()->tipo)->toBe(TipoEspacio::MESA);
    });

    it('asigna capacidad_personas por defecto según el tipo de espacio', function () {
        $mesa = app(GenerarCodigosMasivos::class)->execute(
            tipo: TipoEspacio::MESA,
            cantidad: 1,
        );
        expect(Espacio::find($mesa[0]['id'])->capacidad_personas)->toBe(4);

        $salon = app(GenerarCodigosMasivos::class)->execute(
            tipo: TipoEspacio::SALON,
            cantidad: 1,
        );
        expect(Espacio::find($salon[0]['id'])->capacidad_personas)->toBe(100);
    });

    it('asigna padre_id cuando se provee', function () {
        $padre = Espacio::create([
            'codigo' => 'REST-0001',
            'nombre' => 'Restaurante Principal',
            'tipo' => TipoEspacio::RESTAURANTE,
            'capacidad_personas' => 80,
            'estado' => 1,
        ]);

        $resultados = app(GenerarCodigosMasivos::class)->execute(
            tipo: TipoEspacio::MESA,
            cantidad: 2,
            padre_id: $padre->id,
        );

        foreach ($resultados as $r) {
            expect(Espacio::find($r['id'])->padre_id)->toBe($padre->id);
        }
    });

    it('crea restaurantes con código REST correcto y capacidad_personas por defecto', function () {
        $resultados = app(GenerarCodigosMasivos::class)->execute(
            tipo: TipoEspacio::RESTAURANTE,
            cantidad: 1,
        );

        $restaurante = Espacio::find($resultados[0]['id']);
        expect($restaurante->codigo)->toMatch('/^REST-\d{4}$/')
            ->and($restaurante->tipo)->toBe(TipoEspacio::RESTAURANTE)
            ->and($restaurante->capacidad_personas)->toBe(80);
    });

    it('lanza InvalidArgumentException cuando cantidad es menor a 1', function () {
        expect(fn () => app(GenerarCodigosMasivos::class)->execute(
            tipo: TipoEspacio::MESA,
            cantidad: 0,
        ))->toThrow(InvalidArgumentException::class, 'debe estar entre 1 y 100');
    });

    it('lanza InvalidArgumentException cuando cantidad es mayor a 100', function () {
        expect(fn () => app(GenerarCodigosMasivos::class)->execute(
            tipo: TipoEspacio::MESA,
            cantidad: 101,
        ))->toThrow(InvalidArgumentException::class, 'debe estar entre 1 y 100');
    });

    it('continúa la numeración desde el último código existente', function () {
        Espacio::create([
            'codigo' => 'MESA-0005',
            'nombre' => 'Mesa 5',
            'tipo' => TipoEspacio::MESA,
            'capacidad_personas' => 4,
            'estado' => 1,
            'orden' => 5,
        ]);

        $resultados = app(GenerarCodigosMasivos::class)->execute(
            tipo: TipoEspacio::MESA,
            cantidad: 2,
        );

        expect($resultados[0]['codigo'])->toBe('MESA-0006');
        expect($resultados[1]['codigo'])->toBe('MESA-0007');
    });

    it('retorna id, codigo y nombre en cada resultado', function () {
        $resultados = app(GenerarCodigosMasivos::class)->execute(
            tipo: TipoEspacio::GYM,
            cantidad: 1,
        );

        expect($resultados[0])->toHaveKeys(['id', 'codigo', 'nombre']);
        expect($resultados[0]['nombre'])->toContain('Gimnasio');
    });
});

// ─── GenerarCodigoSubEspacio ─────────────────────────────────────────────────

describe('GenerarCodigoSubEspacio', function () {

    it('genera el primer código MESA-0001 cuando no hay mesas', function () {
        $codigo = app(GenerarCodigoSubEspacio::class)->execute(TipoEspacio::MESA);

        expect($codigo)->toBe('MESA-0001');
    });

    it('genera el siguiente código secuencial', function () {
        Espacio::create([
            'codigo' => 'MESA-0003',
            'nombre' => 'Mesa 3',
            'tipo' => TipoEspacio::MESA,
            'capacidad_personas' => 4,
            'estado' => 1,
            'orden' => 3,
        ]);

        $codigo = app(GenerarCodigoSubEspacio::class)->execute(TipoEspacio::MESA);

        expect($codigo)->toBe('MESA-0004');
    });

    it('genera códigos para diferentes tipos de espacio', function () {
        $salon = app(GenerarCodigoSubEspacio::class)->execute(TipoEspacio::SALON);
        expect($salon)->toBe('SALON-0001');

        $spa = app(GenerarCodigoSubEspacio::class)->execute(TipoEspacio::SPA);
        expect($spa)->toBe('SPA-0001');

        $piscina = app(GenerarCodigoSubEspacio::class)->execute(TipoEspacio::PISCINA);
        expect($piscina)->toBe('PISC-0001');
    });

    it('ignora espacios soft-deleted para la numeración secuencial', function () {
        $espacio = Espacio::create([
            'codigo' => 'MESA-0010',
            'nombre' => 'Mesa 10',
            'tipo' => TipoEspacio::MESA,
            'capacidad_personas' => 4,
            'estado' => 1,
            'orden' => 10,
        ]);
        $espacio->delete();

        $codigo = app(GenerarCodigoSubEspacio::class)->execute(TipoEspacio::MESA);

        expect($codigo)->toBe('MESA-0011');
    });
});
