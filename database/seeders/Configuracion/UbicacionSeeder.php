<?php

declare(strict_types=1);

namespace Database\Seeders\Configuracion;

use App\Repository\Models\Catalogos\Ubicacion;
use Illuminate\Database\Seeder;

/**
 * Estructura física real del Hotel Bugambilias.
 *
 * Jerarquía: edificio → piso → sector → zona.
 * Preserva los nombres clave consumidos por otros seeders:
 * - "Planta Baja" / "Planta Alta" (EspacioSeeder)
 * - "Ala Norte" / "Ala Sur" (HabitacionSeeder)
 * - "Cocina" (MenuRestauranteSeeder)
 */
class UbicacionSeeder extends Seeder
{
    public function run(): void
    {
        // ═══════════════════════════════════════════════════════════════
        // 1. ESTRUCTURA FÍSICA DEL HOTEL
        // ═══════════════════════════════════════════════════════════════
        $edificio = $this->crear(
            tipo: 'edificio',
            nombre: 'Edificio Principal',
            orden: 1,
            descripcion: 'Edificio principal del Hotel Bugambilias',
        );

        // ─── Planta Baja ───────────────────────────────────────────────
        $plantaBaja = $this->crear(
            tipo: 'piso',
            nombre: 'Planta Baja',
            orden: 1,
            padreId: $edificio->id,
            descripcion: 'Recepción, restaurante, cocina, piscina y áreas comunes',
        );

        // Recepción y Lobby
        $recepcion = $this->crear(
            tipo: 'sector',
            nombre: 'Recepción',
            orden: 1,
            padreId: $plantaBaja->id,
            descripcion: 'Área de recepción y bienvenida al huésped',
        );
        $this->crear('zona', 'Mostrador', 1, $recepcion->id, 'Mostrador principal de atención');
        $this->crear('zona', 'Conserjería y Concierge', 2, $recepcion->id);
        $this->crear('zona', 'Sala de Estar / Lobby', 3, $recepcion->id);
        $this->crear('zona', 'Maletería / Pañol de Equipaje', 4, $recepcion->id);

        // Restaurante
        $restaurante = $this->crear(
            tipo: 'sector',
            nombre: 'Restaurante',
            orden: 2,
            padreId: $plantaBaja->id,
            descripcion: 'Restaurante Bugambilias y comedor oficial del hotel',
        );
        $this->crear('zona', 'Salón Principal', 1, $restaurante->id);
        $this->crear('zona', 'Terraza del Comedor', 2, $restaurante->id);
        $this->crear('zona', 'Barra de Servicio', 3, $restaurante->id);

        // Cocina
        $cocina = $this->crear(
            tipo: 'sector',
            nombre: 'Cocina',
            orden: 3,
            padreId: $plantaBaja->id,
            descripcion: 'Cocina central de alimentos y bebidas',
        );
        $this->crear('zona', 'Zona de preparación', 1, $cocina->id);
        $this->crear('zona', 'Zona de Lavado y Vajilla', 2, $cocina->id);
        $this->crear('zona', 'Cámara Frigorífica', 3, $cocina->id);
        $this->crear('zona', 'Despensa de Secos', 4, $cocina->id);

        // Bar Principal
        $bar = $this->crear(
            tipo: 'sector',
            nombre: 'Bar Principal',
            orden: 4,
            padreId: $plantaBaja->id,
        );
        $this->crear('zona', 'Barra Central', 1, $bar->id);
        $this->crear('zona', 'Zona Lounge', 2, $bar->id);

        // Salón de Eventos / Convenciones
        $eventos = $this->crear(
            tipo: 'sector',
            nombre: 'Salón de Eventos',
            orden: 5,
            padreId: $plantaBaja->id,
            descripcion: 'Salones para convenciones, bodas y eventos sociales',
        );
        $this->crear('zona', 'Salón Bugambilias A', 1, $eventos->id);
        $this->crear('zona', 'Salón Jacaranda B', 2, $eventos->id);
        $this->crear('zona', 'Foyer / Antesala', 3, $eventos->id);

        // Piscina
        $piscina = $this->crear(
            tipo: 'sector',
            nombre: 'Área de Piscina',
            orden: 6,
            padreId: $plantaBaja->id,
            descripcion: 'Piscina al aire libre y zona recreativa para huéspedes',
        );
        $this->crear('zona', 'Piscina Principal', 1, $piscina->id);
        $this->crear('zona', 'Deck y Camastros', 2, $piscina->id);
        $this->crear('zona', 'Bar de Piscina', 3, $piscina->id);
        $this->crear('zona', 'Duchas Exteriores', 4, $piscina->id);
        $this->crear('zona', 'Vestidores', 5, $piscina->id);

        // Spa y Bienestar
        $spa = $this->crear(
            tipo: 'sector',
            nombre: 'Spa y Bienestar',
            orden: 7,
            padreId: $plantaBaja->id,
        );
        $this->crear('zona', 'Gimnasio', 1, $spa->id);
        $this->crear('zona', 'Sala de Masajes', 2, $spa->id);
        $this->crear('zona', 'Sauna y Jacuzzi', 3, $spa->id);
        $this->crear('zona', 'Zona de Relajación', 4, $spa->id);

        // Jardines y Exterior
        $jardines = $this->crear(
            tipo: 'sector',
            nombre: 'Jardines y Exterior',
            orden: 8,
            padreId: $plantaBaja->id,
            descripcion: 'Áreas verdes y terrazas al aire libre',
        );
        $this->crear('zona', 'Jardín de Bugambilias', 1, $jardines->id);
        $this->crear('zona', 'Terraza de Eventos al Aire Libre', 2, $jardines->id);
        $this->crear('zona', 'Zona de Fogatas / BBQ', 3, $jardines->id);

        // Lavandería y Lencería
        $lavanderia = $this->crear(
            tipo: 'lavanderia',
            nombre: 'Lavandería y Lencería',
            orden: 9,
            padreId: $plantaBaja->id,
        );
        $this->crear('blancos_sucios', 'Depósito de Blancos Sucios', 1, $lavanderia->id);
        $this->crear('zona', 'Área de Lavado y Secado', 2, $lavanderia->id);
        $this->crear('zona', 'Área de Planchado', 3, $lavanderia->id);
        $this->crear('blancos_limpios', 'Almacén de Blancos Limpios', 4, $lavanderia->id);

        // Área de Servicio
        $servicio = $this->crear(
            tipo: 'sector',
            nombre: 'Área de Servicio',
            orden: 10,
            padreId: $plantaBaja->id,
        );
        $this->crear('zona', 'Cuarto de Máquinas', 1, $servicio->id);
        $this->crear('zona', 'Cuarto de Basura / Reciclaje', 2, $servicio->id);
        $this->crear('zona', 'Andén de Carga y Descarga', 3, $servicio->id);

        // Estacionamiento
        $estacionamiento = $this->crear(
            tipo: 'sector',
            nombre: 'Estacionamiento',
            orden: 11,
            padreId: $plantaBaja->id,
        );
        $this->crear('zona', 'Estacionamiento de Huéspedes', 1, $estacionamiento->id);
        $this->crear('zona', 'Estacionamiento de Empleados', 2, $estacionamiento->id);
        $this->crear('zona', 'Zona Valet Parking', 3, $estacionamiento->id);

        // ─── Planta Alta ───────────────────────────────────────────────
        $plantaAlta = $this->crear(
            tipo: 'piso',
            nombre: 'Planta Alta',
            orden: 2,
            padreId: $edificio->id,
            descripcion: 'Habitaciones, ala norte, ala sur y área ejecutiva',
        );

        $alaNorte = $this->crear(
            tipo: 'sector',
            nombre: 'Ala Norte',
            orden: 1,
            padreId: $plantaAlta->id,
        );
        $this->crear('zona', 'Pasillo Norte', 1, $alaNorte->id);
        $this->crear('zona', 'Cuarto de Lencería Norte', 2, $alaNorte->id);

        $alaSur = $this->crear(
            tipo: 'sector',
            nombre: 'Ala Sur',
            orden: 2,
            padreId: $plantaAlta->id,
        );
        $this->crear('zona', 'Pasillo Sur', 1, $alaSur->id);
        $this->crear('zona', 'Cuarto de Lencería Sur', 2, $alaSur->id);

        $areaEjecutiva = $this->crear(
            tipo: 'sector',
            nombre: 'Área Ejecutiva',
            orden: 3,
            padreId: $plantaAlta->id,
        );
        $this->crear('zona', 'Suites Presidenciales', 1, $areaEjecutiva->id);
        $this->crear('zona', 'Sala Ejecutiva / Juntas', 2, $areaEjecutiva->id);

        // ═══════════════════════════════════════════════════════════════
        // 2. ESTRUCTURA DE INVENTARIO (almacén → estante → nivel → posición)
        // ═══════════════════════════════════════════════════════════════
        $this->crearAlmacenGeneral();
    }

    /**
     * Crea el Almacén General con su jerarquía completa de estantería.
     */
    private function crearAlmacenGeneral(): void
    {
        $almacen = $this->crear(
            tipo: 'almacen',
            nombre: 'Almacén General',
            orden: 10,
            descripcion: 'Almacén central para inventario de insumos y consumibles del hotel',
        );

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
        $this->crear(
            tipo: 'merma',
            nombre: 'Zona de Merma',
            orden: 99,
            padreId: $almacen->id,
            descripcion: 'Ubicación especial para productos vencidos, dañados o rechazados',
        );
    }

    private function crear(
        string $tipo,
        string $nombre,
        int $orden,
        ?int $padreId = null,
        ?string $descripcion = null,
    ): Ubicacion {
        return Ubicacion::create([
            'padre_id' => $padreId,
            'tipo' => $tipo,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'orden' => $orden,
            'estado' => 1,
        ]);
    }

    /**
     * @param  array<int, array{nombre: string, orden: int, posiciones: string[]}>  $niveles
     */
    private function crearEstante(Ubicacion $padre, string $nombre, string $descripcion, int $orden, array $niveles): void
    {
        $estante = $this->crear(tipo: 'estante', nombre: $nombre, orden: $orden, padreId: $padre->id, descripcion: $descripcion);

        foreach ($niveles as $nivelData) {
            $nivel = $this->crear(
                tipo: 'nivel',
                nombre: $nivelData['nombre'],
                orden: $nivelData['orden'],
                padreId: $estante->id,
            );

            foreach ($nivelData['posiciones'] as $i => $posNombre) {
                $this->crear(
                    tipo: 'posicion',
                    nombre: $posNombre,
                    orden: $i + 1,
                    padreId: $nivel->id,
                );
            }
        }
    }
}
