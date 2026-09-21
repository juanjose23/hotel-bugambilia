<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Otros textiles y lencería (CAT_PRO_BLAN_OTROS).
 *
 * Migra 1:1 la cortina de baño original (CORT-BAN-*) y amplía con cortinas
 * de habitación, tapetes, mantelería de banquete y blancos de restaurante.
 */
final class ProductoBlancoOtroSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $cat = (int) $this->catalogoIds['CAT_PRO_BLAN_OTROS'];

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Cortina de baño',
            'Cortina impermeable 180x180cm',
            2,
            [
                ['codigo' => 'CORT-BAN-BCO', 'nombre' => 'Cortina de baño blanca', 'atributos' => ['color' => 'blanco', 'tamaño' => '180x180']],
                ['codigo' => 'CORT-BAN-TRANSP', 'nombre' => 'Cortina de baño transparente', 'atributos' => ['color' => 'transparente', 'tamaño' => '180x180']],
                ['codigo' => 'CORT-BAN-NEGRA', 'nombre' => 'Cortina de baño negra', 'atributos' => ['color' => 'negro', 'tamaño' => '180x180']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Cortina blackout habitación',
            'Cortina termoacústica con riel',
            2,
            [
                ['codigo' => 'CORT-BLK-BCO', 'nombre' => 'Cortina blackout blanca', 'atributos' => ['color' => 'blanco', 'tipo' => 'blackout']],
                ['codigo' => 'CORT-BLK-BEIGE', 'nombre' => 'Cortina blackout beige', 'atributos' => ['color' => 'beige', 'tipo' => 'blackout']],
                ['codigo' => 'CORT-BLK-GRIS', 'nombre' => 'Cortina blackout gris', 'atributos' => ['color' => 'gris', 'tipo' => 'blackout']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Visillo de habitación',
            'Tela ligera translúcida',
            2,
            [
                ['codigo' => 'VIS-HAB-BCO', 'nombre' => 'Visillo blanco', 'atributos' => ['color' => 'blanco']],
                ['codigo' => 'VIS-HAB-IVO', 'nombre' => 'Visillo marfil', 'atributos' => ['color' => 'marfil']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Tapete de baño',
            'Tapete antiderrapante de felpa',
            2,
            [
                ['codigo' => 'TAP-BAN-BCO', 'nombre' => 'Tapete de baño blanco', 'atributos' => ['color' => 'blanco', 'tamaño' => '50x80']],
                ['codigo' => 'TAP-BAN-CREMA', 'nombre' => 'Tapete de baño crema', 'atributos' => ['color' => 'crema', 'tamaño' => '50x80']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Mantel de banquete',
            'Mantelería elegante para eventos',
            2,
            [
                ['codigo' => 'MANT-BQ-BCO', 'nombre' => 'Mantel banquete blanco', 'atributos' => ['color' => 'blanco', 'tamaño' => '2.4m']],
                ['codigo' => 'MANT-BQ-CREMA', 'nombre' => 'Mantel banquete crema', 'atributos' => ['color' => 'crema', 'tamaño' => '2.4m']],
                ['codigo' => 'MANT-BQ-NEGRO', 'nombre' => 'Mantel banquete negro', 'atributos' => ['color' => 'negro', 'tamaño' => '2.4m']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Mantel de restaurante',
            'Mantelería resistente para comedor',
            2,
            [
                ['codigo' => 'MANT-REST-BCO', 'nombre' => 'Mantel restaurante blanco', 'atributos' => ['color' => 'blanco', 'tamaño' => '1.6m']],
                ['codigo' => 'MANT-REST-ROJO', 'nombre' => 'Mantel restaurante rojo vino', 'atributos' => ['color' => 'rojo', 'tamaño' => '1.6m']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Servilleta de tela',
            'Servilleta de restaurante 45x45',
            2,
            [
                ['codigo' => 'SERV-TELA-BCO', 'nombre' => 'Servilleta tela blanca', 'atributos' => ['color' => 'blanco', 'tamaño' => '45x45']],
                ['codigo' => 'SERV-TELA-BURG', 'nombre' => 'Servilleta tela burdeos', 'atributos' => ['color' => 'burdeos', 'tamaño' => '45x45']],
            ]
        );

        $this->crearProductoSimple(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Faldón para mesa de banquetes',
            'Faldón de tela acanalada con velcro',
            2,
            'FALDA-BQ-BCO',
            ['color' => 'blanco', 'largo' => 'carrito']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Funda de closet',
            'Funda protectora para ropa',
            2,
            'FUNDA-CLOSET',
            ['material' => 'no tejido']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Colcha base cama',
            'Colcha de algodón base para cama King',
            2,
            'COL-BASE-KING',
            ['tamaño' => '200x200']
        );
    }
}
