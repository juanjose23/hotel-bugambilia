<?php

use App\Models\Compras\Proveedor;
use App\Models\Personas\Persona;
use App\Models\Personas\PersonaNatural;
use App\Models\User;
use App\UseCases\Compras\Proveedores\Mutations\ActualizarProveedor;
use App\UseCases\Compras\Proveedores\Mutations\CrearProveedor;
use App\UseCases\Compras\Proveedores\Queries\GenerarCodigoProveedor;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

describe('CrearProveedor', function () {
    it('crea proveedor tipo persona natural', function () {
        $proveedor = app(CrearProveedor::class)->execute([
            'codigo' => 'PROV-TEST-001',
            'persona' => [
                'primer_nombre' => 'Juan',
                'segundo_nombre' => 'Carlos',
                'tipo_persona' => 'natural',
                'telefono' => '555-0100',
                'direccion' => 'Calle 123',
            ],
            'personaNatural' => [
                'primer_apellido' => 'Perez',
                'segundo_apellido' => 'Garcia',
            ],
            'estado' => 1,
        ]);

        expect($proveedor)->toBeInstanceOf(Proveedor::class);
        expect($proveedor->codigo)->toBe('PROV-TEST-001');
        expect($proveedor->persona)->not->toBeNull();
        expect($proveedor->persona->tipo_persona)->toBe('natural');
        expect($proveedor->persona->personaNatural)->not->toBeNull();
        expect($proveedor->persona->personaJuridica)->toBeNull();
    });

    it('crea proveedor tipo persona juridica', function () {
        $proveedor = app(CrearProveedor::class)->execute([
            'codigo' => 'PROV-TEST-002',
            'tipo_persona' => 'juridica',
            'persona' => [
                'primer_nombre' => 'Empresa',
                'segundo_nombre' => 'Proveedora',
                'telefono' => '555-0200',
                'direccion' => 'Av. Industrial 456',
            ],
            'personaJuridica' => [
                'razon_social' => 'Proveedora S.A. de C.V.',
                'nombre_comercial' => 'Proveedora',
                'rfc' => 'PROV-123456',
            ],
            'estado' => 1,
        ]);

        expect($proveedor)->toBeInstanceOf(Proveedor::class);
        expect($proveedor->persona->tipo_persona)->toBe('juridica');
        expect($proveedor->persona->personaJuridica)->not->toBeNull();
        expect($proveedor->persona->personaJuridica->razon_social)->toBe('Proveedora S.A. de C.V.');
        expect($proveedor->persona->personaNatural)->toBeNull();
    });

    it('usa persona natural por defecto si no se especifica tipo', function () {
        $proveedor = app(CrearProveedor::class)->execute([
            'codigo' => 'PROV-TEST-003',
            'persona' => [
                'primer_nombre' => 'Default',
                'telefono' => '555-0300',
            ],
            'personaNatural' => [
                'primer_apellido' => 'Apellido',
            ],
            'estado' => 1,
        ]);

        expect($proveedor->persona->tipo_persona)->toBe('natural');
    });
});

describe('ActualizarProveedor', function () {
    it('actualiza datos del proveedor y persona', function () {
        $persona = Persona::create([
            'primer_nombre' => 'Original',
            'tipo_persona' => 'natural',
        ]);
        $persona->personaNatural()->create(['primer_apellido' => 'Apellido']);
        $proveedor = Proveedor::factory()->create(['persona_id' => $persona->id]);

        $resultado = app(ActualizarProveedor::class)->execute($proveedor, [
            'codigo' => 'PROV-UPDATED',
            'persona' => [
                'primer_nombre' => 'Updated',
                'telefono' => '555-9999',
            ],
            'estado' => 1,
        ]);

        expect($resultado->fresh()->codigo)->toBe('PROV-UPDATED');
        expect($resultado->persona->fresh()->primer_nombre)->toBe('Updated');
    });

    it('cambia de persona natural a juridica', function () {
        $persona = Persona::create([
            'primer_nombre' => 'Natural',
            'tipo_persona' => 'natural',
        ]);
        $persona->personaNatural()->create(['primer_apellido' => 'Apellido']);
        $proveedor = Proveedor::factory()->create(['persona_id' => $persona->id]);

        expect($proveedor->persona->tipo_persona)->toBe('natural');
        $personaNaturalId = $proveedor->persona->personaNatural->id;

        $resultado = app(ActualizarProveedor::class)->execute($proveedor, [
            'tipo_persona' => 'juridica',
            'personaJuridica' => [
                'razon_social' => 'Nueva S.A.',
                'nombre_comercial' => 'Nueva SA',
                'rfc' => 'RFC-123',
            ],
            'persona' => [
                'tipo_persona' => 'juridica',
            ],
        ]);

        expect($resultado->persona->fresh()->tipo_persona)->toBe('juridica');
        expect($resultado->persona->fresh()->personaJuridica)->not->toBeNull();
        expect($resultado->persona->fresh()->personaJuridica->razon_social)->toBe('Nueva S.A.');
        expect(PersonaNatural::find($personaNaturalId))->toBeNull();
    });

    it('cambia de persona juridica a natural', function () {
        $persona = Persona::create([
            'primer_nombre' => 'Empresa',
            'tipo_persona' => 'juridica',
            'telefono' => '555-0001',
            'direccion' => 'Dir',
        ]);

        $persona->personaJuridica()->create([
            'razon_social' => 'Vieja S.A.',
            'nombre_comercial' => 'Vieja SA',
            'rfc' => 'RFC-OLD',
        ]);

        $proveedor = Proveedor::factory()->create([
            'persona_id' => $persona->id,
        ]);

        $personaJuridicaId = $persona->personaJuridica->id;

        app(ActualizarProveedor::class)->execute($proveedor, [
            'tipo_persona' => 'natural',
            'personaNatural' => [
                'primer_apellido' => 'Natural',
            ],
            'persona' => [
                'tipo_persona' => 'natural',
            ],
        ]);

        expect($persona->fresh()->tipo_persona)->toBe('natural');
        expect($persona->fresh()->personaNatural)->not->toBeNull();
        expect($persona->fresh()->personaNatural->primer_apellido)->toBe('Natural');
    });
});

describe('GenerarCodigoProveedor', function () {
    it('genera PROV-0001 cuando no hay proveedores', function () {
        Proveedor::whereNotNull('id')->delete();

        $codigo = app(GenerarCodigoProveedor::class)->ejecutar();

        expect($codigo)->toBe('PROV-0001');
    });

    it('genera codigo correlativo basado en el ultimo proveedor', function () {
        Proveedor::whereNotNull('id')->delete();

        Proveedor::factory()->create(['codigo' => 'PROV-0005']);

        $codigo = app(GenerarCodigoProveedor::class)->ejecutar();

        expect($codigo)->toBe('PROV-0006');
    });

    it('genera codigo mas alla de 4 digitos cuando corresponde', function () {
        Proveedor::whereNotNull('id')->delete();

        Proveedor::factory()->create(['codigo' => 'PROV-9999']);

        $codigo = app(GenerarCodigoProveedor::class)->ejecutar();

        expect($codigo)->toBe('PROV-10000');
    });
});
