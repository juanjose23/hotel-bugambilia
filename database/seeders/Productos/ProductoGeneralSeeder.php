<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Productos generales (CAT_PRO_GENERAL).
 *
 * Artículos de oficina, papelería, pilas, botiquín y suministros varios
 * que no pertenecen a otro árbol de categorías.
 */
final class ProductoGeneralSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $cat = (int) $this->catalogoIds['CAT_PRO_GENERAL'];
        $uni = (int) $this->catalogoIds['UNI_UD'];
        $uniPaq = (int) $this->catalogoIds['UNI_PAQ'];
        $uniCaja = (int) $this->catalogoIds['UNI_CAJA'];

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Resma de papel bond',
            'Papel bond tamaño carta',
            2,
            [
                ['codigo' => 'PAP-CARTA-75', 'nombre' => 'Papel bond carta 75g', 'atributos' => ['tamaño' => 'carta', 'gramaje' => '75g']],
                ['codigo' => 'PAP-CARTA-90', 'nombre' => 'Papel bond carta 90g', 'atributos' => ['tamaño' => 'carta', 'gramaje' => '90g']],
            ]
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uniCaja,
            'Bolígrafos',
            'Caja de bolígrafos azules',
            2,
            'BOLI-AZUL-CAJA',
            ['piezas' => '12']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uniCaja,
            'Marcadores permanentes',
            'Caja de marcadores punta fina',
            2,
            'MARC-PERM-CAJA',
            ['piezas' => '12']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uni,
            'Pila AA',
            'Pilas alcalinas AA',
            2,
            'PILA-AA-2',
            ['paquete' => '2']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uni,
            'Pila AAA',
            'Pilas alcalinas AAA',
            2,
            'PILA-AAA-2',
            ['paquete' => '2']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uni,
            'Pila 9V',
            'Pila alcalina de 9 voltios',
            2,
            'PILA-9V-1',
            ['voltaje' => '9V']
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uniPaq,
            'Botiquín de primeros auxilios',
            'Botiquín surtido para comunes y cocina',
            2,
            [
                ['codigo' => 'BOTIQ-COMUN', 'nombre' => 'Botiquín estándar para común', 'atributos' => ['tipo' => 'estándar', 'piezas' => '20']],
                ['codigo' => 'BOTIQ-COCINA', 'nombre' => 'Botiquín reforzado cocina', 'atributos' => ['tipo' => 'reforzado', 'piezas' => '32']],
            ]
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uniPaq,
            'Curación básica',
            'Gasas, vendas y cinta adhesiva',
            2,
            'CURACION-BASICA',
            ['piezas' => '10']
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Grapadora de escritorio',
            'Grapadora metálica estándar',
            2,
            [
                ['codigo' => 'GRAP-EST', 'nombre' => 'Grapadora estándar', 'atributos' => ['tipo' => 'estándar']],
                ['codigo' => 'GRAP-COSIDA', 'nombre' => 'Grapadora tipo costura', 'atributos' => ['tipo' => 'costura']],
            ]
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uniPaq,
            'Cinta adhesiva de embalaje',
            'Cinta canela resistente',
            2,
            'CINTA-EMBALAJE',
            ['ancho' => '48mm']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uniPaq,
            'Sobres manila',
            'Sobres tamaño carta y oficio',
            2,
            'SOBRE-MANILA',
            ['mezcla' => 'carta/oficio']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uni,
            'Tóner impresora',
            'Cartucho tóner negro genérico',
            2,
            'TONER-NEGRO',
            ['rendimiento' => '1500 páginas']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uni,
            'Tinta impresora',
            'Pack de tinta negra y color',
            2,
            'TINTA-4C',
            ['color' => 'negra + 3 color']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uniPaq,
            'Puntillas para lápiz',
            'Caja de minas para portaminas',
            2,
            'PORTAMINAS-05',
            ['grosor' => '0.5mm']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uni,
            'Agenda de recepción',
            'Agenda de trabajo diario',
            2,
            'AGENDA-RECEP',
            ['tipo' => 'diaria']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            $uni,
            'Tablero de notas',
            'Pizarrón blanco de pared',
            2,
            'PIZARRA-60-80',
            ['tamaño' => '60x80cm']
        );
    }
}
