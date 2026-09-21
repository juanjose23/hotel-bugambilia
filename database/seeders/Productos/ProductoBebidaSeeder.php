<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Bebidas y Bar (CAT_PRO_BEBIDAS).
 */
final class ProductoBebidaSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Agua con gas',
            'Agua mineral carbonatada',
            2,
            [
                ['codigo' => 'AG-GAS-500ML', 'nombre' => 'Agua con gas 500ml', 'atributos' => ['volumen' => '500ml'], 'volumen' => 500],
                ['codigo' => 'AG-GAS-1L', 'nombre' => 'Agua con gas 1L', 'atributos' => ['volumen' => '1L'], 'volumen' => 1000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Refresco de cola',
            'Refresco gaseoso de cola',
            2,
            [
                ['codigo' => 'COLA-355ML', 'nombre' => 'Cola 355ml lata', 'atributos' => ['volumen' => '355ml', 'formato' => 'lata']],
                ['codigo' => 'COLA-500ML', 'nombre' => 'Cola 500ml botella', 'atributos' => ['volumen' => '500ml', 'formato' => 'botella']],
                ['codigo' => 'COLA-1L', 'nombre' => 'Cola 1L', 'atributos' => ['volumen' => '1L', 'formato' => 'botella']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Jugo de frutas',
            'Jugo natural concentrado',
            2,
            [
                ['codigo' => 'JUG-NAR-1L', 'nombre' => 'Jugo de naranja 1L', 'atributos' => ['sabor' => 'naranja', 'volumen' => '1L'], 'volumen' => 1000],
                ['codigo' => 'JUG-PIN-1L', 'nombre' => 'Jugo de piña 1L', 'atributos' => ['sabor' => 'piña', 'volumen' => '1L'], 'volumen' => 1000],
                ['codigo' => 'JUG-GUA-1L', 'nombre' => 'Jugo de guayaba 1L', 'atributos' => ['sabor' => 'guayaba', 'volumen' => '1L'], 'volumen' => 1000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Cerveza nacional',
            'Cerveza ligera sin alcohol opcional',
            2,
            [
                ['codigo' => 'CER-NAC-355', 'nombre' => 'Cerveza nacional 355ml', 'atributos' => ['volumen' => '355ml', 'tipo' => 'ligera']],
                ['codigo' => 'CER-NAC-940', 'nombre' => 'Cerveza nacional 940ml', 'atributos' => ['volumen' => '940ml', 'tipo' => 'ligera']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Vino tinto',
            'Vino tinto para restaurante',
            2,
            [
                ['codigo' => 'VTINTO-750ML', 'nombre' => 'Vino tinto 750ml', 'atributos' => ['volumen' => '750ml', 'tipo' => 'tinto']],
                ['codigo' => 'VTINTO-1L', 'nombre' => 'Vino tinto 1L', 'atributos' => ['volumen' => '1L', 'tipo' => 'tinto']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Vino blanco',
            'Vino blanco seco',
            2,
            [
                ['codigo' => 'VBLAN-750ML', 'nombre' => 'Vino blanco 750ml', 'atributos' => ['volumen' => '750ml', 'tipo' => 'blanco']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Ron premium',
            'Ron para coctelería',
            2,
            [
                ['codigo' => 'RON-BLAN-750', 'nombre' => 'Ron blanco 750ml', 'atributos' => ['tipo' => 'blanco', 'volumen' => '750ml']],
                ['codigo' => 'RON-ANEJO-750', 'nombre' => 'Ron añejo 750ml', 'atributos' => ['tipo' => 'añejo', 'volumen' => '750ml']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Whisky estándar',
            'Whisky de bar',
            2,
            'WHI-STD-750',
            ['volumen' => '750ml']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Vodka estándar',
            'Vodka para coctelería',
            2,
            'VOD-STD-750',
            ['volumen' => '750ml']
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Gaseosa de naranja',
            'Refresco sabor naranja',
            2,
            [
                ['codigo' => 'NAR-355ML', 'nombre' => 'Naranja 355ml lata', 'atributos' => ['volumen' => '355ml', 'formato' => 'lata']],
                ['codigo' => 'NAR-500ML', 'nombre' => 'Naranja 500ml', 'atributos' => ['volumen' => '500ml', 'formato' => 'botella']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Soda de limón',
            'Gaseosa sabor limón',
            2,
            'LIM-355ML',
            ['volumen' => '355ml', 'formato' => 'lata']
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Café de barra',
            'Café expreso para servicio',
            2,
            [
                ['codigo' => 'BAR-EXPRESO', 'nombre' => 'Expreso porción 30ml', 'atributos' => ['tipo' => 'expreso']],
                ['codigo' => 'BAR-CAPPUCCINO', 'nombre' => 'Cappuccino porción 60ml', 'atributos' => ['tipo' => 'cappuccino']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Bebida láctea saborizada',
            'Leche sabor chocolate y vainilla',
            2,
            [
                ['codigo' => 'LD-CHOC-200', 'nombre' => 'Bebida láctea chocolate 200ml', 'atributos' => ['sabor' => 'chocolate', 'volumen' => '200ml']],
                ['codigo' => 'LD-VAIN-200', 'nombre' => 'Bebida láctea vainilla 200ml', 'atributos' => ['sabor' => 'vainilla', 'volumen' => '200ml']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Agua de coco',
            'Agua de coco natural',
            2,
            'COCO-330ML',
            ['volumen' => '330ml']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_BEBIDAS'],
            null,
            (int) $this->catalogoIds['UNI_CAJA'],
            'Mix de barra (junior)',
            'Set mini botellas para minibar',
            2,
            'MIX-BARRA-4',
            ['botellas' => '4']
        );
    }
}
