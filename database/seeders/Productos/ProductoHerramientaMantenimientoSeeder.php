<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Herramientas de Mantenimiento (CAT_PRO_MANT).
 *
 * Migra 1:1 el bloque original, los equipos de servicios generales y los 50
 * productos técnicos (SKUs generados idénticos para idempotencia).
 */
final class ProductoHerramientaMantenimientoSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_MANT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Taladro percutor',
            'Taladro 650W con maletín',
            2,
            [
                ['codigo' => 'TAL-500', 'nombre' => 'Taladro percutor 500W', 'atributos' => ['potencia' => '500W', 'tipo' => 'básico'], 'peso' => 2500],
                ['codigo' => 'TAL-650', 'nombre' => 'Taladro percutor 650W', 'atributos' => ['potencia' => '650W', 'tipo' => 'estándar'], 'peso' => 2800],
                ['codigo' => 'TAL-800', 'nombre' => 'Taladro percutor 800W profesional', 'atributos' => ['potencia' => '800W', 'tipo' => 'profesional'], 'peso' => 3200],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_MANT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Juego de destornilladores',
            'Set 8 piezas',
            2,
            [
                ['codigo' => 'JD-8P', 'nombre' => 'Juego destornilladores 8 piezas', 'atributos' => ['piezas' => '8', 'tipo' => 'básico'], 'peso' => 300],
                ['codigo' => 'JD-16P', 'nombre' => 'Juego destornilladores 16 piezas', 'atributos' => ['piezas' => '16', 'tipo' => 'completo'], 'peso' => 600],
                ['codigo' => 'JD-32P', 'nombre' => 'Juego destornilladores 32 piezas profesional', 'atributos' => ['piezas' => '32', 'tipo' => 'profesional'], 'peso' => 1200],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_MANT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Llave inglesa 12"',
            'Llave ajustable profesional',
            2,
            [
                ['codigo' => 'LL-8', 'nombre' => 'Llave inglesa 8 pulgadas', 'atributos' => ['tamaño' => '8"', 'material' => 'acero cromado'], 'peso' => 200],
                ['codigo' => 'LL-12', 'nombre' => 'Llave inglesa 12 pulgadas', 'atributos' => ['tamaño' => '12"', 'material' => 'acero cromado'], 'peso' => 350],
                ['codigo' => 'LL-16', 'nombre' => 'Llave inglesa 16 pulgadas profesional', 'atributos' => ['tamaño' => '16"', 'material' => 'acero cromado'], 'peso' => 500],
            ]
        );

        $this->crearEquiposServiciosGenerales();

        $this->crearProductosTecnicos();

        $this->crearEquiposMayores();
    }

    /**
     * Equipos de servicios generales (industriales: -A/-B/-C, "capacidad").
     */
    private function crearEquiposServiciosGenerales(): void
    {
        $equipos = [
            ['nombre' => 'Generador Eléctrico', 'desc' => 'Planta de emergencia 150KVA', 'v1' => '50 KVA (Respaldos)', 'v2' => '100 KVA', 'v3' => '150 KVA (Full Hotel)'],
            ['nombre' => 'Bomba Hidroneumática', 'desc' => 'Sistema presión constante', 'v1' => '2 HP', 'v2' => '5 HP', 'v3' => '10 HP (Edificio)'],
            ['nombre' => 'Calentador Industrial', 'desc' => 'Boiler de alta recuperación', 'v1' => '80 Galones', 'v2' => '120 Galones', 'v3' => 'Solar Industrial'],
            ['nombre' => 'Cortacésped Tractor', 'desc' => 'Podadora giro cero', 'v1' => 'Manual 20"', 'v2' => 'Tractor 42"', 'v3' => 'Tractor 54"'],
        ];

        foreach ($equipos as $item) {
            $pfx = $this->prefijo($item['nombre']);
            $hsh = strtoupper(substr(md5($item['nombre']), 0, 4));
            $this->crearProductoConVariante(
                (int) $this->catalogoIds['CAT_PRO_MANT'],
                null,
                (int) $this->catalogoIds['UNI_UD'],
                $item['nombre'],
                $item['desc'].' (Uso: SERVICIOS_GENERALES)',
                2,
                [
                    ['codigo' => "{$pfx}-{$hsh}-A", 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['capacidad' => $item['v1']]],
                    ['codigo' => "{$pfx}-{$hsh}-B", 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['capacidad' => $item['v2']]],
                    ['codigo' => "{$pfx}-{$hsh}-C", 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['capacidad' => $item['v3']]],
                ]
            );
        }
    }

    /**
     * Generación masiva de 50 productos técnicos de mantenimiento
     * (migración idéntica del seeder original).
     */
    private function crearProductosTecnicos(): void
    {
        $mantenimientoData = [
            'ELÉCTRICO' => [
                'items' => [
                    ['nombre' => 'Interruptor Simple', 'desc' => 'Interruptor de pared 10A', 'v1' => 'Blanco', 'v2' => 'Marfil', 'v3' => 'Negro'],
                    ['nombre' => 'Tomacorriente Doble', 'desc' => 'Toma con tierra 15A', 'v1' => 'Estándar', 'v2' => 'Con USB', 'v3' => 'GFCI (Baño)'],
                    ['nombre' => 'Cable Eléctrico #12', 'desc' => 'Cable de cobre THHN', 'v1' => 'Rojo (Fase)', 'v2' => 'Negro (Fase)', 'v3' => 'Verde (Tierra)'],
                    ['nombre' => 'Bombilla LED 9W', 'desc' => 'Foco ahorrador E27', 'v1' => 'Luz Cálida', 'v2' => 'Luz Fría', 'v3' => 'Luz Neutra'],
                    ['nombre' => 'Breaker de Riel', 'desc' => 'Disyuntor termo-magnético', 'v1' => '15 Amperios', 'v2' => '20 Amperios', 'v3' => '30 Amperios'],
                    ['nombre' => 'Cinta Aislante', 'desc' => 'Cinta PVC profesional', 'v1' => 'Negra', 'v2' => 'Roja', 'v3' => 'Azul'],
                    ['nombre' => 'Caja Rectangular PVC', 'desc' => 'Caja empotrar 2x4', 'v1' => 'Estándar', 'v2' => 'Profunda', 'v3' => 'Intemperie'],
                    ['nombre' => 'Tubo Conduit 1/2"', 'desc' => 'Tubería eléctrica PVC', 'v1' => 'Liviano', 'v2' => 'Pesado', 'v3' => 'Flexible'],
                    ['nombre' => 'Sensor de Movimiento', 'desc' => 'Detector para pasillos', 'v1' => 'Pared', 'v2' => 'Techo', 'v3' => 'Inalámbrico'],
                    ['nombre' => 'Lámpara de Emergencia', 'desc' => 'Doble foco autonomía 90min', 'v1' => 'Básica', 'v2' => 'LED Pro', 'v3' => 'Estanca'],
                ],
            ],
            'PLOMERÍA' => [
                'items' => [
                    ['nombre' => 'Tubo PVC 1/2"', 'desc' => 'Tubería agua potable', 'v1' => 'SDR 13.5', 'v2' => 'SDR 17', 'v3' => 'SDR 21'],
                    ['nombre' => 'Codo PVC 90° 1/2"', 'desc' => 'Accesorio unión agua', 'v1' => 'Presión', 'v2' => 'Rosca', 'v3' => 'Inserto Metálico'],
                    ['nombre' => 'Válvula de Paso', 'desc' => 'Llave de esfera bronce', 'v1' => '1/2 Pulgada', 'v2' => '3/4 Pulgada', 'v3' => '1 Pulgada'],
                    ['nombre' => 'Teflón Industrial', 'desc' => 'Cinta selladora de roscas', 'v1' => '12mm x 10m', 'v2' => '19mm x 10m', 'v3' => 'Alta Densidad'],
                    ['nombre' => 'Pegamento PVC', 'desc' => 'Cemento solvente', 'v1' => 'Transparente 4oz', 'v2' => 'Azul Rápido 8oz', 'v3' => 'Dorado CPVC 16oz'],
                    ['nombre' => 'Sifón de Lavabo', 'desc' => 'Trampa para desagüe', 'v1' => 'Plástico Blanco', 'v2' => 'Cromado', 'v3' => 'Flexible'],
                    ['nombre' => 'Válvula de Inodoro', 'desc' => 'Kit de descarga completo', 'v1' => 'Universal', 'v2' => 'Doble Descarga', 'v3' => 'Presión Alta'],
                    ['nombre' => 'Manguera de Abasto', 'desc' => 'Conector flexible trenzado', 'v1' => 'Lavabo (12")', 'v2' => 'Inodoro (12")', 'v3' => 'Fregadero (18")'],
                    ['nombre' => 'Grifería Monomando', 'desc' => 'Mezcladora para baño', 'v1' => 'Cromo Pulido', 'v2' => 'Níquel Satinado', 'v3' => 'Negro Mate'],
                    ['nombre' => 'Flotador para Tanque', 'desc' => 'Válvula de llenado superior', 'v1' => 'Boya Plástica', 'v2' => 'Boya Cobre', 'v3' => 'Vertical'],
                ],
            ],
            'PINTURA' => [
                'items' => [
                    ['nombre' => 'Pintura Látex Interior', 'desc' => 'Pintura acrílica base agua', 'v1' => 'Blanco Hielo', 'v2' => 'Arena Suave', 'v3' => 'Gris Perla'],
                    ['nombre' => 'Esmalte Anticorrosivo', 'desc' => 'Pintura base aceite metal', 'v1' => 'Negro Brillante', 'v2' => 'Blanco Mate', 'v3' => 'Rojo Óxido'],
                    ['nombre' => 'Brocha Profesional', 'desc' => 'Cerda sintética alta calidad', 'v1' => '2 Pulgadas', 'v2' => '3 Pulgadas', 'v3' => '4 Pulgadas'],
                    ['nombre' => 'Rodillo de Felpa', 'desc' => 'Maneral + felpa 9"', 'v1' => 'Pared Lisa', 'v2' => 'Pared Rugosa', 'v3' => 'Microfibra'],
                    ['nombre' => 'Thinner Corriente', 'desc' => 'Solvente para limpieza', 'v1' => '1 Litro', 'v2' => 'Galón', 'v3' => 'Cubeta 19L'],
                    ['nombre' => 'Masilla para Pared', 'desc' => 'Resanador de grietas', 'v1' => 'Pasta 1kg', 'v2' => 'Galón Ready Mix', 'v3' => 'Saco 20kg'],
                    ['nombre' => 'Lija de Agua', 'desc' => 'Papel lija profesional', 'v1' => 'Grano 80', 'v2' => 'Grano 120', 'v3' => 'Grano 240'],
                    ['nombre' => 'Cinta Masking', 'desc' => 'Cinta para pintor', 'v1' => '1/2 Pulgada', 'v2' => '1 Pulgada', 'v3' => '2 Pulgadas'],
                    ['nombre' => 'Barniz para Madera', 'desc' => 'Protector poliuretano', 'v1' => 'Brillante', 'v2' => 'Mate', 'v3' => 'Satinado'],
                    ['nombre' => 'Bandeja para Pintar', 'desc' => 'Bandeja plástica reforzada', 'v1' => 'Pequeña', 'v2' => 'Grande', 'v3' => 'Con Rejilla Metálica'],
                ],
            ],
            'FERRETERÍA' => [
                'items' => [
                    ['nombre' => 'Cerradura de Pomo', 'desc' => 'Cerradura para puertas', 'v1' => 'Baño (Sin llave)', 'v2' => 'Recámara', 'v3' => 'Entrada Principal'],
                    ['nombre' => 'Bisagra de Acero', 'desc' => 'Bisagra libro 3.5"', 'v1' => 'Cromada', 'v2' => 'Latón', 'v3' => 'Anticorro'],
                    ['nombre' => 'Tornillo Madera', 'desc' => 'Tornillo Phillips zincado', 'v1' => '1 Pulgada', 'v2' => '1.5 Pulgadas', 'v3' => '2 Pulgadas'],
                    ['nombre' => 'Taquete Plástico', 'desc' => 'Anclaje para pared', 'v1' => '1/4 Pulgada', 'v2' => '5/16 Pulgada', 'v3' => '3/8 Pulgada'],
                    ['nombre' => 'Silicona Multiusos', 'desc' => 'Sellador acético', 'v1' => 'Transparente', 'v2' => 'Blanco', 'v3' => 'Negro'],
                    ['nombre' => 'Candado de Seguridad', 'desc' => 'Cuerpo de latón macizo', 'v1' => '30 mm', 'v2' => '40 mm', 'v3' => '50 mm'],
                    ['nombre' => 'Malla Mosquitera', 'desc' => 'Malla fibra de vidrio', 'v1' => 'Gris (Rollo)', 'v2' => 'Negra (Rollo)', 'v3' => 'Aluminio'],
                    ['nombre' => 'Resorte para Puerta', 'desc' => 'Cierrapuertas hidráulico', 'v1' => 'Liviano', 'v2' => 'Medio', 'v3' => 'Pesado'],
                    ['nombre' => 'Manija de Palanca', 'desc' => 'Herraje ergonómico', 'v1' => 'Níquel', 'v2' => 'Cobre Viejo', 'v3' => 'Moderno'],
                    ['nombre' => 'Tope de Puerta', 'desc' => 'Protector de pared', 'v1' => 'Piso', 'v2' => 'Pared', 'v3' => 'Adhesivo'],
                ],
            ],
            'HERRAMIENTAS' => [
                'items' => [
                    ['nombre' => 'Martillo de Uña', 'desc' => 'Martillo 16oz mango fibra', 'v1' => 'Básico', 'v2' => 'Profesional', 'v3' => 'Antivibración'],
                    ['nombre' => 'Alicate Universal', 'desc' => 'Pinza multiuso 8"', 'v1' => 'Estándar', 'v2' => 'Aislado 1000V', 'v3' => 'Heavy Duty'],
                    ['nombre' => 'Flexómetro', 'desc' => 'Cinta métrica retráctil', 'v1' => '3 Metros', 'v2' => '5 Metros', 'v3' => '8 Metros'],
                    ['nombre' => 'Nivel de Burbuja', 'desc' => 'Herramienta nivelación', 'v1' => '12 Pulgadas', 'v2' => '24 Pulgadas', 'v3' => 'Magnético'],
                    ['nombre' => 'Cutter Industrial', 'desc' => 'Cuchilla retráctil', 'v1' => 'Plástico', 'v2' => 'Metálico', 'v3' => 'Auto-carga'],
                    ['nombre' => 'Llave Allen (Set)', 'desc' => 'Juego de llaves hexagonales', 'v1' => 'Milimétricas', 'v2' => 'Pulgadas', 'v3' => 'Tipo Navaja'],
                    ['nombre' => 'Gafas de Seguridad', 'desc' => 'Protección ocular ANSI', 'v1' => 'Transparentes', 'v2' => 'Oscuras', 'v3' => 'Antiempañante'],
                    ['nombre' => 'Guantes de Trabajo', 'desc' => 'Protección de manos', 'v1' => 'Látex Rugoso', 'v2' => 'Cuero', 'v3' => 'Anticorte'],
                    ['nombre' => 'Escalera de Tijera', 'desc' => 'Escalera de aluminio', 'v1' => '3 Peldaños', 'v2' => '5 Peldaños', 'v3' => '7 Peldaños'],
                    ['nombre' => 'Multímetro Digital', 'desc' => 'Tester eléctrico', 'v1' => 'Básico', 'v2' => 'Autorango', 'v3' => 'Profesional'],
                ],
            ],
        ];

        foreach ($mantenimientoData as $grupo => $info) {
            foreach ($info['items'] as $item) {
                $pfx = $this->prefijo($item['nombre']);
                $hsh = strtoupper(substr(md5($item['nombre']), 0, 4));
                $this->crearProductoConVariante(
                    (int) $this->catalogoIds['CAT_PRO_MANT'],
                    null,
                    (int) $this->catalogoIds['UNI_UD'],
                    $item['nombre'],
                    $item['desc']." (Categoría: $grupo)",
                    2,
                    [
                        ['codigo' => "{$pfx}-{$hsh}-A", 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['especificacion' => $item['v1']]],
                        ['codigo' => "{$pfx}-{$hsh}-B", 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['especificacion' => $item['v2']]],
                        ['codigo' => "{$pfx}-{$hsh}-C", 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['especificacion' => $item['v3']]],
                    ]
                );
            }
        }
    }

    private function crearEquiposMayores(): void
    {
        $categoria = (int) $this->catalogoIds['CAT_PRO_MANT'];

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Compresor de aire',
            'Compresor para taller de mantenimiento',
            3,
            'COMP-AIRE-50',
            ['capacidad' => '50L']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Escalera de extensión',
            'Escalera de aluminio extensible',
            3,
            'ESC-EXT-6M',
            ['altura' => '6m']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Soldadora inverter',
            'Soldadora eléctrica 200A',
            3,
            'SOLD-200A',
            ['corriente' => '200A']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Pulidora angular',
            'Esmeril angular 7"',
            3,
            'PUL-7-1500',
            ['potencia' => '1500W']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Andamio plegable',
            'Andamio móvil de trabajo',
            3,
            'ANDAM-1.5M',
            ['altura' => '1.5m']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Hidrolavadora',
            'Lavadora a presión',
            3,
            'HIDRO-1600',
            ['presión' => '1600 PSI']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Motosierra',
            'Motosierra para jardinería',
            3,
            'MOTO-SIERRA-18',
            ['espada' => '18"']
        );

        $this->crearProductoConVariante(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Taladro inalámbrico',
            'Taladro recargable de 18V',
            2,
            [
                ['codigo' => 'TAL-IN-12V', 'nombre' => 'Taladro inalámbrico 12V', 'atributos' => ['voltaje' => '12V']],
                ['codigo' => 'TAL-IN-18V', 'nombre' => 'Taladro inalámbrico 18V', 'atributos' => ['voltaje' => '18V']],
            ]
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Juego de llaves mixtas',
            'Set de llaves combinadas',
            2,
            'LL-MIX-12P',
            ['piezas' => '12']
        );

        $this->crearProductoSimple(
            $categoria,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Linterna recargable',
            'Linterna de mano recargable',
            2,
            'LINT-RECARGA',
            ['tipo' => 'mano']
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
