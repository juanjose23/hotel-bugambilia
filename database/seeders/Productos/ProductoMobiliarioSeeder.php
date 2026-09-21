<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Mobiliario (CAT_PRO_MOB).
 *
 * Migra 1:1 el bloque original + los activos de eventos, gimnasio/pool,
 * lobby/bar y cocina (SKUs generados idénticos para idempotencia).
 */
final class ProductoMobiliarioSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_MOB'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Cama King Size',
            'Base + colchón King',
            3,
            [
                ['codigo' => 'CAM-KING-BLANCA', 'nombre' => 'Cama King blanca 200x200', 'atributos' => ['color' => 'blanco', 'tamaño' => '200x200'], 'peso' => 150000],
                ['codigo' => 'CAM-KING-NOGAL', 'nombre' => 'Cama King nogal 200x200', 'atributos' => ['color' => 'nogal', 'tamaño' => '200x200'], 'peso' => 150000],
                ['codigo' => 'CAM-KING-WENGUE', 'nombre' => 'Cama King wengué 200x200', 'atributos' => ['color' => 'wengué', 'tamaño' => '200x200'], 'peso' => 150000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_MOB'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Mesa de noche',
            'Mesa auxiliar con cajón',
            3,
            [
                ['codigo' => 'MES-NOC-BLANCA', 'nombre' => 'Mesa noche blanca 40x40x50', 'atributos' => ['color' => 'blanco', 'tamaño' => '40x40x50'], 'peso' => 25000],
                ['codigo' => 'MES-NOC-NOGAL', 'nombre' => 'Mesa noche nogal 40x40x50', 'atributos' => ['color' => 'nogal', 'tamaño' => '40x40x50'], 'peso' => 25000],
                ['codigo' => 'MES-NOC-WENGUE', 'nombre' => 'Mesa noche wengué 40x40x50', 'atributos' => ['color' => 'wengué', 'tamaño' => '40x40x50'], 'peso' => 25000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_MOB'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Escritorio ejecutivo',
            'Escritorio 120x60cm',
            3,
            [
                ['codigo' => 'ESC-EJE-BLANCA', 'nombre' => 'Escritorio ejecutivo blanco 120x60', 'atributos' => ['color' => 'blanco', 'tamaño' => '120x60'], 'peso' => 45000],
                ['codigo' => 'ESC-EJE-NOGAL', 'nombre' => 'Escritorio ejecutivo nogal 120x60', 'atributos' => ['color' => 'nogal', 'tamaño' => '120x60'], 'peso' => 45000],
                ['codigo' => 'ESC-EJE-GRIS', 'nombre' => 'Escritorio ejecutivo gris 120x60', 'atributos' => ['color' => 'gris', 'tamaño' => '120x60'], 'peso' => 45000],
            ]
        );

        $this->crearActivosMobiliario();

        $this->crearColchonesYCamas();
        $this->crearMueblesYAreasComunes();
        $this->crearCarrosYMobiliarioOperativo();
    }

    /**
     * Activos de eventos, gimnasio/pool, lobby/bar y cocina
     * (migración idéntica del seeder original).
     */
    private function crearActivosMobiliario(): void
    {
        // EVENTOS (bucle de infraestructura original: sufijos -X/-Y/-Z, atributo "modelo")
        $eventos = [
            ['nombre' => 'Proyector Láser', 'desc' => 'Proyector 4000 lúmenes UHD', 'v1' => 'Full HD 1080p', 'v2' => '4K Nativo', 'v3' => 'Tiro Corto'],
            ['nombre' => 'Sistema de Audio Salón', 'desc' => 'Kit de altavoces y consola', 'v1' => 'Pasivo 2 Altavoces', 'v2' => 'Activo Bluetooth', 'v3' => 'Line Array (Gran salón)'],
            ['nombre' => 'Silla para Banquete', 'desc' => 'Silla apilable reforzada', 'v1' => 'Tiffany (Madera)', 'v2' => 'Metálica Acolchada', 'v3' => 'Resina Premium'],
            ['nombre' => 'Mesa Circular Eventos', 'desc' => 'Mesa plegable 1.80m', 'v1' => 'Madera Plegable', 'v2' => 'Plástico HD', 'v3' => 'Estructura Aluminio'],
            ['nombre' => 'Podio de Madera', 'desc' => 'Atril para conferencistas', 'v1' => 'Clásico Nogal', 'v2' => 'Moderno Acrílico', 'v3' => 'Con Micrófono Integrado'],
        ];

        foreach ($eventos as $item) {
            $pfx = $this->prefijo($item['nombre']);
            $hsh = strtoupper(substr(md5($item['nombre']), 0, 4));
            $this->crearProductoConVariante(
                (int) $this->catalogoIds['CAT_PRO_MOB'],
                null,
                (int) $this->catalogoIds['UNI_UD'],
                $item['nombre'],
                $item['desc'].' (Uso: EVENTOS)',
                2,
                [
                    ['codigo' => "{$pfx}-{$hsh}-X", 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['modelo' => $item['v1']]],
                    ['codigo' => "{$pfx}-{$hsh}-Y", 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['modelo' => $item['v2']]],
                    ['codigo' => "{$pfx}-{$hsh}-Z", 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['modelo' => $item['v3']]],
                ]
            );
        }

        // GIMNASIO_POOL y LOBBY_BAR (bucle adicionales original: sufijos -V1/-V2/-V3, atributo "espec")
        $adicionales = [
            'GIMNASIO_POOL' => [
                ['nombre' => 'Caminadora Pro', 'desc' => 'Caminadora profesional alto tráfico', 'v1' => 'Serie 500 (Básica)', 'v2' => 'Serie 700 (Pro)', 'v3' => 'Serie 900 (Touch)'],
                ['nombre' => 'Elíptica Industrial', 'desc' => 'Máquina elíptica magnética', 'v1' => 'Auto-generada', 'v2' => 'Con Pantalla', 'v3' => 'Heavy Duty'],
                ['nombre' => 'Camastro de Piscina', 'desc' => 'Camastro resina alta resistencia', 'v1' => 'Blanco Estándar', 'v2' => 'Madera Teca', 'v3' => 'Acolchado Luxury'],
            ],
            'LOBBY_BAR' => [
                ['nombre' => 'Sofá de Lobby', 'desc' => 'Sofá diseño para áreas comunes', 'v1' => '2 Plazas Tela', 'v2' => '3 Plazas Cuero', 'v3' => 'Modular (L)'],
                ['nombre' => 'Máquina Espresso', 'desc' => 'Cafetera profesional 2 grupos', 'v1' => '1 Grupo (Compacta)', 'v2' => '2 Grupos (Pro)', 'v3' => '3 Grupos (Elite)'],
                ['nombre' => 'Molino de Café', 'desc' => 'Molino automático on-demand', 'v1' => 'Básico', 'v2' => 'Dosificador Pro', 'v3' => 'Micrométrico'],
            ],
        ];

        foreach ($adicionales as $grupo => $items) {
            foreach ($items as $item) {
                $pfx = $this->prefijo($item['nombre']);
                $hsh = strtoupper(substr(md5($item['nombre']), 0, 4));
                $this->crearProductoConVariante(
                    (int) $this->catalogoIds['CAT_PRO_MOB'],
                    null,
                    (int) $this->catalogoIds['UNI_UD'],
                    $item['nombre'],
                    $item['desc']." (Uso: $grupo)",
                    2,
                    [
                        ['codigo' => "{$pfx}-{$hsh}-V1", 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['espec' => $item['v1']]],
                        ['codigo' => "{$pfx}-{$hsh}-V2", 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['espec' => $item['v2']]],
                        ['codigo' => "{$pfx}-{$hsh}-V3", 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['espec' => $item['v3']]],
                    ]
                );
            }
        }

        // COCINA (bucle industriales original: sufijos -A/-B/-C, atributo "capacidad")
        $cocina = [
            ['nombre' => 'Estufa Industrial', 'desc' => 'Estufa 6 quemadores acero inox', 'v1' => 'Gas LP', 'v2' => 'Gas Natural', 'v3' => 'Eléctrica (Inducción)'],
            ['nombre' => 'Horno de Convección', 'desc' => 'Horno profesional 10 bandejas', 'v1' => 'Básico (Analógico)', 'v2' => 'Digital Programable', 'v3' => 'Doble Cavidad'],
            ['nombre' => 'Refrigerador Vertical', 'desc' => 'Refrigerador industrial 2 puertas', 'v1' => 'Acero Inoxidable', 'v2' => 'Puerta de Vidrio', 'v3' => 'Congelador/Refri'],
            ['nombre' => 'Lavavajillas de Capota', 'desc' => 'Lavadora platos alto volumen', 'v1' => 'Ciclo Rápido', 'v2' => 'Con Secado', 'v3' => 'Ahorro de Agua'],
        ];

        foreach ($cocina as $item) {
            $pfx = $this->prefijo($item['nombre']);
            $hsh = strtoupper(substr(md5($item['nombre']), 0, 4));
            $this->crearProductoConVariante(
                (int) $this->catalogoIds['CAT_PRO_MOB'],
                null,
                (int) $this->catalogoIds['UNI_UD'],
                $item['nombre'],
                $item['desc'].' (Uso: COCINA)',
                2,
                [
                    ['codigo' => "{$pfx}-{$hsh}-A", 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['capacidad' => $item['v1']]],
                    ['codigo' => "{$pfx}-{$hsh}-B", 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['capacidad' => $item['v2']]],
                    ['codigo' => "{$pfx}-{$hsh}-C", 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['capacidad' => $item['v3']]],
                ]
            );
        }
    }

    private function crearColchonesYCamas(): void
    {
        $categoria = (int) $this->catalogoIds['CAT_PRO_MOB'];

        $this->crearProductoConVariante(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Colchón King Size',
            'Colchón ortopédico 200x200',
            3,
            [
                ['codigo' => 'COL-KING-200', 'nombre' => 'Colchón King 200x200', 'atributos' => ['medida' => '200x200']],
                ['codigo' => 'COL-KING-HIPER', 'nombre' => 'Colchón King hiperbólico', 'atributos' => ['medida' => '200x200', 'tipo' => 'hiperbólico']],
            ]
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Colchón Queen',
            'Colchón ortopédico 160x200',
            3,
            'COL-QUEEN-160',
            ['medida' => '160x200']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Colchón Full',
            'Colchón estándar 137x190',
            3,
            'COL-FULL-137',
            ['medida' => '137x190']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Base para cama individual',
            'Base tapizada individual 90x190',
            3,
            'BASE-IND-90',
            ['medida' => '90x190']
        );

        $this->crearProductoConVariante(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Buró de hotel',
            'Buró minimalista con cajón',
            3,
            [
                ['codigo' => 'BUR-BCO-40', 'nombre' => 'Buró blanco 40cm', 'atributos' => ['color' => 'blanco', 'ancho' => '40cm']],
                ['codigo' => 'BUR-NOGAL-40', 'nombre' => 'Buró nogal 40cm', 'atributos' => ['color' => 'nogal', 'ancho' => '40cm']],
            ]
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Cama adicional plegable',
            'Cama de campaña para habitación',
            3,
            'CAM-CAMP-01',
            ['tipo' => 'plegable']
        );
    }

    private function crearMueblesYAreasComunes(): void
    {
        $categoria = (int) $this->catalogoIds['CAT_PRO_MOB'];

        $this->crearProductoConVariante(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Sillón de lectura',
            'Sillón individual para habitación',
            3,
            [
                ['codigo' => 'SIL-LECT-TELA', 'nombre' => 'Sillón lectura tela gris', 'atributos' => ['material' => 'tela', 'color' => 'gris']],
                ['codigo' => 'SIL-LECT-CUERO', 'nombre' => 'Sillón lectura cuero', 'atributos' => ['material' => 'cuero', 'color' => 'café']],
            ]
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Sofá cama',
            'Sofá cama para suites familiares',
            3,
            'SOF-CAMA-2P',
            ['plazas' => '2']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Silla ergonómica de oficina',
            'Silla ejecutiva con respaldo',
            3,
            'SILLA-ERG-01',
            ['tipo' => 'ergonómica']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Silla de comedor',
            'Silla de madera para restaurante',
            3,
            'SILLA-COM-01',
            ['material' => 'madera']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Escritorio de recepción',
            'Escritorio de mostrador para lobby',
            3,
            'ESC-REC-01',
            ['medida' => '160x80cm']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Mostrador de recepción',
            'Mostrador modular de lobby',
            3,
            'MOS-REC-01',
            ['material' => 'melamina']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Mesa de sala de juntas',
            'Mesa ovalada para juntas',
            3,
            'MESA-JUNTA-01',
            ['capacidad' => '10 personas']
        );

        $this->crearProductoConVariante(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Mesa plegable para eventos',
            'Mesa plegable de 1.80m',
            3,
            [
                ['codigo' => 'MES-PLEG-ALUM', 'nombre' => 'Mesa plegable aluminio', 'atributos' => ['material' => 'aluminio']],
                ['codigo' => 'MES-PLEG-MAD', 'nombre' => 'Mesa plegable madera', 'atributos' => ['material' => 'madera']],
            ]
        );

        $this->crearProductoConVariante(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Lámpara de mesa',
            'Lámpara decorativa para buró',
            3,
            [
                ['codigo' => 'LAM-MES-BCO', 'nombre' => 'Lámpara mesa blanca', 'atributos' => ['color' => 'blanco']],
                ['codigo' => 'LAM-MES-NOG', 'nombre' => 'Lámpara mesa nogal', 'atributos' => ['color' => 'nogal']],
            ]
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Lámpara de pie',
            'Lámpara de piso para sala',
            3,
            'LAM-PIE-01',
            ['tipo' => 'de pie']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Lámpara de techo',
            'Plafón LED para habitación',
            3,
            'LAM-TECHO-LED',
            ['fuente' => 'LED']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Espejo de baño',
            'Espejo antiniebla de baño',
            3,
            'ESP-BANO-01',
            ['tipo' => 'antiniebla']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Ropero empotrado',
            'Armario para habitación',
            3,
            'ROP-EMP-01',
            ['tipo' => 'empotrado']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Cajonera de hotel',
            'Cajonera baja con 4 cajones',
            3,
            'CAJ-HOTEL-4C',
            ['cajones' => '4']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Puff de sala de espera',
            'Asiento tapizado para lobby',
            3,
            'PUFF-LOBBY-01',
            ['color' => 'gris']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Mesa de terraza',
            'Mesa de exterior resistente',
            3,
            'MESA-TERRAZA-01',
            ['material' => 'resina']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Sombrilla de terraza',
            'Sombrilla con base para exterior',
            3,
            'SOMB-TERRAZA-2',
            ['diámetro' => '2.5m']
        );
    }

    private function crearCarrosYMobiliarioOperativo(): void
    {
        $categoria = (int) $this->catalogoIds['CAT_PRO_MOB'];

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Carro room service',
            'Carro de servicio a habitación',
            3,
            'CARRO-RS-01',
            ['material' => 'acero inoxidable']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Carro para banquete',
            'Carro con bandejas para salón',
            3,
            'CARRO-BANQ-01',
            ['bandejas' => '3']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Cabecero de cama tapizado',
            'Cabecero acolchado para cama',
            3,
            'CAB-TAP-160',
            ['ancho' => '160cm']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Escritorio de conferencias',
            'Mesa de trabajo para salón eventos',
            3,
            'ESC-CONF-01',
            ['medida' => '200x100cm']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Base de TV de pared',
            'Soporte de TV de pared fijo',
            3,
            'SOP-TV-PARED',
            ['tipo' => 'fijo']
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
