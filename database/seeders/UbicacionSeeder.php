<?php

namespace Database\Seeders;

use App\Models\Catalogos\Ubicacion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class UbicacionSeeder extends Seeder
{
    public function run(): void
    {
        $ahora = Carbon::now();

        // 1. Edificio principal (raíz)
        $edificio = Ubicacion::create([
            'padre_id' => null,
            'tipo' => 'edificio',
            'nombre' => 'Edificio Principal',
            'descripcion' => 'Edificio principal del hotel',
            'orden' => 1,
            'estado' => 1,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        // 2. Planta Baja (piso)
        $plantaBaja = Ubicacion::create([
            'padre_id' => $edificio->id,
            'tipo' => 'piso',
            'nombre' => 'Planta Baja',
            'descripcion' => 'Recepción, cocina, salones',
            'orden' => 1,
            'estado' => 1,
        ]);

        // 3. Planta Alta (piso)
        $plantaAlta = Ubicacion::create([
            'padre_id' => $edificio->id,
            'tipo' => 'piso',
            'nombre' => 'Planta Alta',
            'descripcion' => 'Habitaciones y baños',
            'orden' => 2,
            'estado' => 1,
        ]);

        // Sectores en Planta Baja
        $sectorRecepcion = Ubicacion::create([
            'padre_id' => $plantaBaja->id,
            'tipo' => 'sector',
            'nombre' => 'Recepción',
            'orden' => 1,
            'estado' => 1,
        ]);
        $sectorCocina = Ubicacion::create([
            'padre_id' => $plantaBaja->id,
            'tipo' => 'sector',
            'nombre' => 'Cocina',
            'orden' => 2,
            'estado' => 1,
        ]);

        // Sectores en Planta Alta
        $alaNorte = Ubicacion::create([
            'padre_id' => $plantaAlta->id,
            'tipo' => 'sector',
            'nombre' => 'Ala Norte',
            'orden' => 1,
            'estado' => 1,
        ]);
        $alaSur = Ubicacion::create([
            'padre_id' => $plantaAlta->id,
            'tipo' => 'sector',
            'nombre' => 'Ala Sur',
            'orden' => 2,
            'estado' => 1,
        ]);

        // Zonas concretas (hojas finales)
        Ubicacion::create([
            'padre_id' => $sectorRecepcion->id,
            'tipo' => 'zona',
            'nombre' => 'Mostrador',
            'orden' => 1,
            'estado' => 1,
        ]);
        Ubicacion::create([
            'padre_id' => $sectorCocina->id,
            'tipo' => 'zona',
            'nombre' => 'Zona de preparación',
            'orden' => 1,
            'estado' => 1,
        ]);
        Ubicacion::create([
            'padre_id' => $alaNorte->id,
            'tipo' => 'zona',
            'nombre' => 'Pasillo Norte',
            'orden' => 1,
            'estado' => 1,
        ]);
        Ubicacion::create([
            'padre_id' => $alaSur->id,
            'tipo' => 'zona',
            'nombre' => 'Pasillo Sur',
            'orden' => 1,
            'estado' => 1,
        ]);

        Ubicacion::create([
            'padre_id' => null,
            'tipo' => 'almacen',
            'nombre' => 'Almacén General',
            'descripcion' => 'Almacén central para inventario de insumos',
            'orden' => 1,
            'estado' => 1,
        ]);

        Ubicacion::create([
            'padre_id' => null,
            'tipo' => 'zona',
            'nombre' => 'Estante 1',
            'descripcion' => 'Estantería metálica del Almacén General',
            'orden' => 2,
            'estado' => 1,
        ]);

        Ubicacion::create([
            'padre_id' => null,
            'tipo' => 'zona',
            'nombre' => 'Refrigeradora de Cocina',
            'descripcion' => 'Refrigeradora para perecederos diarios',
            'orden' => 3,
            'estado' => 1,
        ]);

        Ubicacion::create([
            'padre_id' => null,
            'tipo' => 'zona',
            'nombre' => 'Cuarto Frío de Carnes',
            'descripcion' => 'Cámara de refrigeración de carnes y mariscos',
            'orden' => 4,
            'estado' => 1,
        ]);

        Ubicacion::create([
            'padre_id' => null,
            'tipo' => 'zona',
            'nombre' => 'Zona de Merma',
            'descripcion' => 'Ubicación especial para productos vencidos o rechazados',
            'orden' => 99,
            'estado' => 1,
        ]);
    }
}
