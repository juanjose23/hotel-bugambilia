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

        // =========================================================================
        // 1. ESTRUCTURA FÍSICA DEL HOTEL (edificio → piso → sector → zona)
        //    Se usa para limpieza, asignación de activos y referencia de espacios.
        // =========================================================================
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

        $plantaBaja = Ubicacion::create([
            'padre_id' => $edificio->id,
            'tipo' => 'piso',
            'nombre' => 'Planta Baja',
            'descripcion' => 'Recepción, cocina, salones',
            'orden' => 1,
            'estado' => 1,
        ]);

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

        // Zonas finales del hotel
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

        // =========================================================================
        // 2. ESTRUCTURA DE INVENTARIO (almacén → estante → nivel → posición)
        //    Almacén General con estantería profesional para control de stock.
        //    Se usa en PutawayPolicy, recepciones, traslados y lotes.
        // =========================================================================
        $this->crearAlmacenGeneral($ahora);
    }

    /**
     * Crea el Almacén General con su jerarquía completa de estantería.
     * Estructura: almacen → estante → nivel → posicion
     */
    private function crearAlmacenGeneral(Carbon $ahora): void
    {
        $almacen = Ubicacion::create([
            'padre_id' => null,
            'tipo' => 'almacen',
            'nombre' => 'Almacén General',
            'descripcion' => 'Almacén central para inventario de insumos y consumibles del hotel',
            'orden' => 10,
            'estado' => 1,
            'created_at' => $ahora,
            'updated_at' => $ahora,
        ]);

        // Estante A — Secos y abarrotes
        $this->crearEstante($almacen, 'Estante A', 'Secos y abarrotes no perecederos', 1, [
            ['nombre' => 'Nivel 1', 'orden' => 1, 'posiciones' => ['Posición 1', 'Posición 2', 'Posición 3']],
            ['nombre' => 'Nivel 2', 'orden' => 2, 'posiciones' => ['Posición 1', 'Posición 2']],
            ['nombre' => 'Nivel 3', 'orden' => 3, 'posiciones' => ['Posición 1']],
        ]);

        // Estante B — Enlatados y conservas
        $this->crearEstante($almacen, 'Estante B', 'Enlatados, conservas y alimentos procesados', 2, [
            ['nombre' => 'Nivel 1', 'orden' => 1, 'posiciones' => ['Posición 1', 'Posición 2']],
            ['nombre' => 'Nivel 2', 'orden' => 2, 'posiciones' => ['Posición 1']],
        ]);

        // Estante C — Bebidas y botellas
        $this->crearEstante($almacen, 'Estante C', 'Bebidas embotelladas, jugos y aguas', 3, [
            ['nombre' => 'Nivel 1', 'orden' => 1, 'posiciones' => ['Posición 1', 'Posición 2', 'Posición 3']],
            ['nombre' => 'Nivel 2', 'orden' => 2, 'posiciones' => ['Posición 1', 'Posición 2']],
            ['nombre' => 'Nivel 3', 'orden' => 3, 'posiciones' => ['Posición 1']],
        ]);

        // Estante D — Limpieza y químicos
        $this->crearEstante($almacen, 'Estante D', 'Productos de limpieza, desinfectantes y químicos', 4, [
            ['nombre' => 'Nivel 1', 'orden' => 1, 'posiciones' => ['Posición 1', 'Posición 2']],
            ['nombre' => 'Nivel 2', 'orden' => 2, 'posiciones' => ['Posición 1']],
        ]);

        // Refrigerador 1 — Lácteos y huevos
        $this->crearEstante($almacen, 'Refrigerador 1', 'Refrigeración para lácteos, huevos y derivados (4°C)', 5, [
            ['nombre' => 'Nivel 1', 'orden' => 1, 'posiciones' => ['Posición 1 (Lácteos)', 'Posición 2 (Huevos)']],
            ['nombre' => 'Nivel 2', 'orden' => 2, 'posiciones' => ['Posición 1 (Yogures)', 'Posición 2 (Quesos)']],
        ]);

        // Refrigerador 2 — Carnes y mariscos
        $this->crearEstante($almacen, 'Refrigerador 2', 'Cámara de refrigeración para carnes y mariscos (2°C)', 6, [
            ['nombre' => 'Nivel 1', 'orden' => 1, 'posiciones' => ['Posición 1 (Res)', 'Posición 2 (Cerdo)', 'Posición 3 (Pollo)']],
            ['nombre' => 'Nivel 2', 'orden' => 2, 'posiciones' => ['Posición 1 (Mariscos)', 'Posición 2 (Pescado)']],
        ]);

        // Congelador 1 — Congelados
        $this->crearEstante($almacen, 'Congelador 1', 'Congelador para verduras, frutas y alimentos congelados (-18°C)', 7, [
            ['nombre' => 'Nivel 1', 'orden' => 1, 'posiciones' => ['Posición 1 (Verduras)', 'Posición 2 (Frutas)']],
            ['nombre' => 'Nivel 2', 'orden' => 2, 'posiciones' => ['Posición 1 (Pan congelado)', 'Posición 2 (Helados)']],
        ]);

        // Zona de Merma (dependencia directa del almacén)
        Ubicacion::create([
            'padre_id' => $almacen->id,
            'tipo' => 'zona',
            'nombre' => 'Zona de Merma',
            'descripcion' => 'Ubicación especial para productos vencidos, dañados o rechazados',
            'orden' => 99,
            'estado' => 1,
        ]);
    }

    /**
     * @param  array<int, array{nombre: string, orden: int, posiciones: string[]}>  $niveles
     */
    private function crearEstante(Ubicacion $padre, string $nombre, string $descripcion, int $orden, array $niveles): void
    {
        $estante = Ubicacion::create([
            'padre_id' => $padre->id,
            'tipo' => 'estante',
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'orden' => $orden,
            'estado' => 1,
        ]);

        foreach ($niveles as $nivelData) {
            $nivel = Ubicacion::create([
                'padre_id' => $estante->id,
                'tipo' => 'nivel',
                'nombre' => $nivelData['nombre'],
                'orden' => $nivelData['orden'],
                'estado' => 1,
            ]);

            foreach ($nivelData['posiciones'] as $i => $posNombre) {
                Ubicacion::create([
                    'padre_id' => $nivel->id,
                    'tipo' => 'posicion',
                    'nombre' => $posNombre,
                    'orden' => $i + 1,
                    'estado' => 1,
                ]);
            }
        }
    }
}
