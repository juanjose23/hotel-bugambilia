<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Equipos Electrónicos (CAT_PRO_ELECTRO).
 *
 * Migra 1:1 el bloque original + activos de habitaciones, seguridad,
 * lavandería y sistemas (SKUs generados idénticos para idempotencia).
 */
final class ProductoEquipoElectronicoSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ELECTRO'],
            (int) $this->catalogoIds['MARC_SAMSUNG'],
            (int) $this->catalogoIds['UNI_UD'],
            'Televisor 43"',
            'Smart TV Samsung 43" UHD',
            3,
            [
                ['codigo' => 'TV-S43-2022', 'nombre' => 'Samsung TV 43" UHD 2022', 'atributos' => ['tamaño' => '43"', 'año' => '2022', 'resolución' => '4K']],
                ['codigo' => 'TV-S43-2023', 'nombre' => 'Samsung TV 43" UHD 2023', 'atributos' => ['tamaño' => '43"', 'año' => '2023', 'resolución' => '4K']],
                ['codigo' => 'TV-S43-2024', 'nombre' => 'Samsung TV 43" UHD 2024', 'atributos' => ['tamaño' => '43"', 'año' => '2024', 'resolución' => '4K']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ELECTRO'],
            (int) $this->catalogoIds['MARC_GEN'],
            (int) $this->catalogoIds['UNI_UD'],
            'Secador de pelo',
            'Secador profesional 2200W',
            3,
            [
                ['codigo' => 'SEC-PRO-1800', 'nombre' => 'Secador profesional 1800W', 'atributos' => ['potencia' => '1800W', 'nivel' => 'estándar']],
                ['codigo' => 'SEC-PRO-2200', 'nombre' => 'Secador profesional 2200W', 'atributos' => ['potencia' => '2200W', 'nivel' => 'premium']],
                ['codigo' => 'SEC-PRO-3000', 'nombre' => 'Secador profesional 3000W', 'atributos' => ['potencia' => '3000W', 'nivel' => 'ultra']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ELECTRO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Teléfono de habitación',
            'Teléfono analógico simple',
            3,
            [
                ['codigo' => 'TEL-HAB-BLANCO', 'nombre' => 'Teléfono blanco', 'atributos' => ['color' => 'blanco', 'tipo' => 'analógico']],
                ['codigo' => 'TEL-HAB-NEGRO', 'nombre' => 'Teléfono negro', 'atributos' => ['color' => 'negro', 'tipo' => 'analógico']],
                ['codigo' => 'TEL-HAB-BEIGE', 'nombre' => 'Teléfono beige', 'atributos' => ['color' => 'beige', 'tipo' => 'analógico']],
            ]
        );

        $this->crearActivosElectronicos();

        $this->crearEquiposAdicionales();
    }

    /**
     * Activos de habitaciones, seguridad, lavandería y sistemas
     * (migración idéntica del seeder original).
     */
    private function crearActivosElectronicos(): void
    {
        // HABITACIONES (infraestructura: -X/-Y/-Z, "modelo")
        $habitaciones = [
            ['nombre' => 'Minibar Silencioso', 'desc' => 'Minibar 40L para habitación', 'v1' => '30 Litros (Compacto)', 'v2' => '40 Litros (Estándar)', 'v3' => '60 Litros (Premium)'],
            ['nombre' => 'Caja Fuerte Digital', 'desc' => 'Caja fuerte con código y llave', 'v1' => 'Teclado Estándar', 'v2' => 'Biométrica (Huella)', 'v3' => 'Tamaño Laptop 15"'],
            ['nombre' => 'Aire Acondicionado', 'desc' => 'Split Inverter Frío/Calor', 'v1' => '12,000 BTU', 'v2' => '18,000 BTU', 'v3' => '24,000 BTU (Suites)'],
            ['nombre' => 'Cerradura RFID', 'desc' => 'Cerradura electrónica para tarjetas', 'v1' => 'Lector Proximidad', 'v2' => 'Bluetooth / App', 'v3' => 'Acero Reforzado'],
            ['nombre' => 'Cafetera de Habitación', 'desc' => 'Cafetera de goteo/cápsula', 'v1' => 'Goteo (Básica)', 'v2' => 'Cápsulas (Nespresso)', 'v3' => 'Combo Té/Café'],
        ];

        foreach ($habitaciones as $item) {
            $pfx = $this->prefijo($item['nombre']);
            $hsh = strtoupper(substr(md5($item['nombre']), 0, 4));
            $this->crearProductoConVariante(
                (int) $this->catalogoIds['CAT_PRO_ELECTRO'],
                null,
                (int) $this->catalogoIds['UNI_UD'],
                $item['nombre'],
                $item['desc'].' (Uso: HABITACIONES)',
                2,
                [
                    ['codigo' => "{$pfx}-{$hsh}-X", 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['modelo' => $item['v1']]],
                    ['codigo' => "{$pfx}-{$hsh}-Y", 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['modelo' => $item['v2']]],
                    ['codigo' => "{$pfx}-{$hsh}-Z", 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['modelo' => $item['v3']]],
                ]
            );
        }

        // SEGURIDAD (adicionales: -V1/-V2/-V3, "espec")
        $seguridad = [
            ['nombre' => 'Cámara de Vigilancia', 'desc' => 'Cámara CCTV alta resolución', 'v1' => 'Domo 4K IP', 'v2' => 'PTZ Exterior', 'v3' => 'Ojo de Pez 360°'],
            ['nombre' => 'Grabador NVR', 'desc' => 'Grabador de video en red', 'v1' => '8 Canales (1TB)', 'v2' => '16 Canales (4TB)', 'v3' => '32 Canales (RAID)'],
            ['nombre' => 'Sistema Incendio', 'desc' => 'Detector y alarma central', 'v1' => 'Detector Humo', 'v2' => 'Estación Manual', 'v3' => 'Sirena con Estrobo'],
        ];

        foreach ($seguridad as $item) {
            $pfx = $this->prefijo($item['nombre']);
            $hsh = strtoupper(substr(md5($item['nombre']), 0, 4));
            $this->crearProductoConVariante(
                (int) $this->catalogoIds['CAT_PRO_ELECTRO'],
                null,
                (int) $this->catalogoIds['UNI_UD'],
                $item['nombre'],
                $item['desc'].' (Uso: SEGURIDAD)',
                2,
                [
                    ['codigo' => "{$pfx}-{$hsh}-V1", 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['espec' => $item['v1']]],
                    ['codigo' => "{$pfx}-{$hsh}-V2", 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['espec' => $item['v2']]],
                    ['codigo' => "{$pfx}-{$hsh}-V3", 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['espec' => $item['v3']]],
                ]
            );
        }

        // LAVANDERÍA (industriales: -A/-B/-C, "capacidad")
        $lavanderia = [
            ['nombre' => 'Lavadora Industrial', 'desc' => 'Carga frontal 25kg/50kg', 'v1' => '25 Kg (Estándar)', 'v2' => '50 Kg (Alta Carga)', 'v3' => 'Centrifugado Pro'],
            ['nombre' => 'Secadora Industrial', 'desc' => 'Secadora alto flujo vapor', 'v1' => 'Eléctrica', 'v2' => 'Gas LP', 'v3' => 'Vapor Directo'],
            ['nombre' => 'Calandria de Rodillo', 'desc' => 'Planchadora de sábanas industrial', 'v1' => '1.5 Metros', 'v2' => '2 Metros', 'v3' => '3 Metros (Suites)'],
        ];

        foreach ($lavanderia as $item) {
            $pfx = $this->prefijo($item['nombre']);
            $hsh = strtoupper(substr(md5($item['nombre']), 0, 4));
            $this->crearProductoConVariante(
                (int) $this->catalogoIds['CAT_PRO_ELECTRO'],
                null,
                (int) $this->catalogoIds['UNI_UD'],
                $item['nombre'],
                $item['desc'].' (Uso: LAVANDERÍA)',
                2,
                [
                    ['codigo' => "{$pfx}-{$hsh}-A", 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['capacidad' => $item['v1']]],
                    ['codigo' => "{$pfx}-{$hsh}-B", 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['capacidad' => $item['v2']]],
                    ['codigo' => "{$pfx}-{$hsh}-C", 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['capacidad' => $item['v3']]],
                ]
            );
        }

        // SISTEMAS (industriales: -A/-B/-C, "capacidad")
        $sistemas = [
            ['nombre' => 'Servidor de Datos', 'desc' => 'Servidor Rack 1U Xeon', 'v1' => 'Básico (8GB RAM)', 'v2' => 'Medio (32GB RAM)', 'v3' => 'Pro (64GB + SSD)'],
            ['nombre' => 'Laptop Administrativa', 'desc' => 'Equipo 15" para oficina', 'v1' => 'Core i3 / 8GB', 'v2' => 'Core i5 / 16GB', 'v3' => 'Core i7 / 32GB'],
            ['nombre' => 'Multifuncional Láser', 'desc' => 'Impresora/Escáner alto volumen', 'v1' => 'Blanco y Negro', 'v2' => 'Color (Oficina)', 'v3' => 'Color (Artes Gráficas)'],
        ];

        foreach ($sistemas as $item) {
            $pfx = $this->prefijo($item['nombre']);
            $hsh = strtoupper(substr(md5($item['nombre']), 0, 4));
            $this->crearProductoConVariante(
                (int) $this->catalogoIds['CAT_PRO_ELECTRO'],
                null,
                (int) $this->catalogoIds['UNI_UD'],
                $item['nombre'],
                $item['desc'].' (Uso: SISTEMAS)',
                2,
                [
                    ['codigo' => "{$pfx}-{$hsh}-A", 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['capacidad' => $item['v1']]],
                    ['codigo' => "{$pfx}-{$hsh}-B", 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['capacidad' => $item['v2']]],
                    ['codigo' => "{$pfx}-{$hsh}-C", 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['capacidad' => $item['v3']]],
                ]
            );
        }
    }

    private function crearEquiposAdicionales(): void
    {
        $categoria = (int) $this->catalogoIds['CAT_PRO_ELECTRO'];

        $this->crearProductoConVariante(
            $categoria,
            (int) $this->catalogoIds['MARC_LG'],
            (int) $this->catalogoIds['UNI_UD'],
            'Smart TV 4K 65"',
            'Smart TV para suites y salones',
            3,
            [
                ['codigo' => 'TV65-LG-2024', 'nombre' => 'LG 65" 4K 2024', 'atributos' => ['marca' => 'LG', 'tamaño' => '65"', 'año' => '2024']],
                ['codigo' => 'TV65-SAMSUNG-2024', 'nombre' => 'Samsung 65" 4K 2024', 'atributos' => ['marca' => 'Samsung', 'tamaño' => '65"', 'año' => '2024']],
            ]
        );

        $this->crearProductoConVariante(
            $categoria,
            (int) $this->catalogoIds['MARC_GEN'],
            (int) $this->catalogoIds['UNI_UD'],
            'Plancha de ropa',
            'Plancha de vapor para planchado',
            3,
            [
                ['codigo' => 'PLAN-VAP-STD', 'nombre' => 'Plancha vapor estándar', 'atributos' => ['tipo' => 'vapor']],
                ['codigo' => 'PLAN-VAP-PRO', 'nombre' => 'Plancha vapor profesional', 'atributos' => ['tipo' => 'vapor', 'nivel' => 'profesional']],
            ]
        );

        $this->crearProductoConVariante(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Router WiFi',
            'Router de acceso inalámbrico',
            3,
            [
                ['codigo' => 'ROUTER-AC', 'nombre' => 'Router WiFi AC1200', 'atributos' => ['estándar' => 'AC1200']],
                ['codigo' => 'ROUTER-AX', 'nombre' => 'Router WiFi AX3000', 'atributos' => ['estándar' => 'AX3000']],
            ]
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Punto de acceso WiFi',
            'Access point de techo',
            3,
            'AP-TECHO-01',
            ['tipo' => 'techo']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Terminal punto de venta POS',
            'POS con lector de tarjetas',
            3,
            'POS-TERM-01',
            ['tipo' => 'contactless']
        );

        $this->crearProductoConVariante(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'UPS para computación',
            'Respaldo de energía para equipo',
            3,
            [
                ['codigo' => 'UPS-650VA', 'nombre' => 'UPS 650VA', 'atributos' => ['capacidad' => '650VA']],
                ['codigo' => 'UPS-1200VA', 'nombre' => 'UPS 1200VA', 'atributos' => ['capacidad' => '1200VA']],
            ]
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Aspiradora industrial',
            'Aspiradora seco/húmedo',
            3,
            'ASPI-SH-01',
            ['capacidad' => '30L']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Licuadora de bar',
            'Licuadora de alta velocidad',
            3,
            'LIC-BAR-01',
            ['potencia' => '1.5HP']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Batidora industrial',
            'Batidora de repostería',
            3,
            'BAT-IND-01',
            ['capacidad' => '10L']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Hervidor de agua',
            'Hervidor eléctrico para habitación',
            3,
            'HERV-1L',
            ['capacidad' => '1L']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Microondas de cocina',
            'Microondas convencional',
            3,
            'MICRO-20L',
            ['capacidad' => '20L']
        );

        $this->crearProductoConVariante(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Ventilador de pedestal',
            'Ventilador para áreas comunes',
            3,
            [
                ['codigo' => 'VENT-PED-STD', 'nombre' => 'Ventilador pedestal estándar', 'atributos' => ['tamaño' => '18"']],
                ['codigo' => 'VENT-PED-PRO', 'nombre' => 'Ventilador pedestal pro', 'atributos' => ['tamaño' => '20"']],
            ]
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Monitor de PC 24"',
            'Monitor para recepción y oficinas',
            3,
            'MON-24-FHD',
            ['resolución' => 'Full HD']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Impresora de tickets',
            'Impresora térmica para POS',
            3,
            'IMP-TK-58',
            ['ancho' => '58mm']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Timbre de habitación',
            'Timbre electrónico de puerta',
            3,
            'TIMB-HAB-01',
            ['tipo' => 'electrónico']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Abridor de puertas automático',
            'Sensor de apertura de puerta',
            3,
            'ABR-PUERTA-01',
            ['tipo' => 'automático']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Extractor de cocina',
            'Campana extractora industrial',
            3,
            'EXTR-COCINA-01',
            ['capacidad' => 'alta']
        );
    }

    /**
     * Prefijo SKU idéntico al generador original del seeder.
     */
    private function prefijo(string $nombre): string
    {
        return strtoupper(str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', ' '],
            ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', ''],
            mb_substr($nombre, 0, 3)
        ));
    }
}
