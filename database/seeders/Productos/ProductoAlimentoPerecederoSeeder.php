<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Alimentos Perecederos (CAT_PRO_ALIM_PEREC).
 */
final class ProductoAlimentoPerecederoSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            (int) $this->catalogoIds['MARC_GEN'],
            (int) $this->catalogoIds['UNI_LIT'],
            'Leche fresca',
            'Leche entera pasteurizada',
            1,
            [
                ['codigo' => 'LEC-500ML', 'nombre' => 'Leche entera 500 ml', 'atributos' => ['volumen' => '500ml', 'tipo' => 'entera'], 'volumen' => 500],
                ['codigo' => 'LEC-1L', 'nombre' => 'Leche entera 1 litro', 'atributos' => ['volumen' => '1L', 'tipo' => 'entera'], 'volumen' => 1000],
                ['codigo' => 'LEC-2L', 'nombre' => 'Leche entera 2 litros', 'atributos' => ['volumen' => '2L', 'tipo' => 'entera'], 'volumen' => 2000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Pan de caja',
            'Pan de molde blanco',
            1,
            [
                ['codigo' => 'PAN-CAJ-BCO', 'nombre' => 'Pan de caja blanco 500g', 'atributos' => ['tipo' => 'blanco', 'peso' => '500g'], 'peso' => 500],
                ['codigo' => 'PAN-CAJ-INTEGRAL', 'nombre' => 'Pan de caja integral 500g', 'atributos' => ['tipo' => 'integral', 'peso' => '500g'], 'peso' => 500],
                ['codigo' => 'PAN-CAJ-MULTIGRANO', 'nombre' => 'Pan de caja multigrano 500g', 'atributos' => ['tipo' => 'multigrano', 'peso' => '500g'], 'peso' => 500],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Frutas variadas',
            'Frutas de temporada',
            1,
            [
                ['codigo' => 'FRUT-TROPICALES', 'nombre' => 'Frutas tropicales mix 1kg', 'atributos' => ['tipo' => 'tropicales', 'peso' => '1kg'], 'peso' => 1000],
                ['codigo' => 'FRUT-CITRICOS', 'nombre' => 'Frutas cítricos mix 1kg', 'atributos' => ['tipo' => 'cítricos', 'peso' => '1kg'], 'peso' => 1000],
                ['codigo' => 'FRUT-BERRIES', 'nombre' => 'Berries variados 1kg', 'atributos' => ['tipo' => 'berries', 'peso' => '1kg'], 'peso' => 1000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Huevos de granja',
            'Huevos frescos seleccionados',
            1,
            [
                ['codigo' => 'HUE-CUB-12', 'nombre' => 'Huevos de granja docena', 'atributos' => ['unidades' => '12'], 'peso' => 600],
                ['codigo' => 'HUE-CUB-30', 'nombre' => 'Huevos de granja charola 30', 'atributos' => ['unidades' => '30'], 'peso' => 1500],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Queso fresco',
            'Queso de mesa suave',
            1,
            [
                ['codigo' => 'QF-500G', 'nombre' => 'Queso fresco 500g', 'atributos' => ['peso' => '500g'], 'peso' => 500],
                ['codigo' => 'QF-1KG', 'nombre' => 'Queso fresco 1kg', 'atributos' => ['peso' => '1kg'], 'peso' => 1000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Jamón de pechuga de pavo',
            'Jamón bajo en grasa',
            1,
            [
                ['codigo' => 'JAMX-100G', 'nombre' => 'Jamón de pavo 100g', 'atributos' => ['peso' => '100g'], 'peso' => 100],
                ['codigo' => 'JAMX-300G', 'nombre' => 'Jamón de pavo 300g', 'atributos' => ['peso' => '300g'], 'peso' => 300],
                ['codigo' => 'JAMX-500G', 'nombre' => 'Jamón de pavo 500g', 'atributos' => ['peso' => '500g'], 'peso' => 500],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Yogur natural',
            'Yogur sin azúcar',
            1,
            [
                ['codigo' => 'YOG-125ML', 'nombre' => 'Yogur natural 125 ml', 'atributos' => ['volumen' => '125ml'], 'volumen' => 125],
                ['codigo' => 'YOG-500ML', 'nombre' => 'Yogur natural 500 ml', 'atributos' => ['volumen' => '500ml'], 'volumen' => 500],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_GR'],
            'Mermelada de frutas',
            'Mermelada de fresa y piña',
            1,
            [
                ['codigo' => 'MER-30G', 'nombre' => 'Mermelada individual 30g', 'atributos' => ['formato' => 'individual', 'peso' => '30g'], 'peso' => 30],
                ['codigo' => 'MER-500G', 'nombre' => 'Mermelada 500g tarro', 'atributos' => ['formato' => 'tarro', 'peso' => '500g'], 'peso' => 500],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_GR'],
            'Miel de abeja',
            'Miel pura de apicultura',
            1,
            [
                ['codigo' => 'MIEL-250G', 'nombre' => 'Miel de abeja 250g', 'atributos' => ['peso' => '250g'], 'peso' => 250],
                ['codigo' => 'MIEL-1KG', 'nombre' => 'Miel de abeja 1kg', 'atributos' => ['peso' => '1kg'], 'peso' => 1000],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Manjar de leche',
            'Dulce de leche para banquetería',
            1,
            'MANJ-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Hortalizas surtidas',
            'Verduras frescas de temporada',
            1,
            [
                ['codigo' => 'HOT-CAJA-1KG', 'nombre' => 'Hortalizas en caja 1kg', 'atributos' => ['presentación' => 'caja', 'peso' => '1kg'], 'peso' => 1000],
                ['codigo' => 'HOT-BOLSA-1KG', 'nombre' => 'Hortalizas en bolsa 1kg', 'atributos' => ['presentación' => 'bolsa', 'peso' => '1kg'], 'peso' => 1000],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Papas de temporada',
            'Papas blancas seleccionadas',
            1,
            'PAPAS-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Cebollas de cocina',
            'Cebolla blanca para cocina',
            1,
            'CEBOLLA-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Zanahorias frescas',
            'Zanahoria para cocina y buffet',
            1,
            'ZANA-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Plátanos verdes y maduros',
            'Plátanos mixtos para cocina',
            1,
            'PLAT-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Carne de res seleccionada',
            'Corte para guisos y plancha',
            1,
            'RES-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Pollo entero',
            'Pollo entero congelado de granja',
            1,
            'POLLO-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Pescado fresco del día',
            'Pescado blanco para restaurante',
            1,
            'PESC-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Langostinos congelados',
            'Mariscos congelados premium',
            1,
            'LANG-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Pan francés',
            'Baguette fresca para desayuno',
            1,
            'PFR-001-STD',
            ['presentación' => 'pieza']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Pan de hamburguesa',
            'Pan suave para burgers',
            1,
            'PHB-001-STD',
            ['presentación' => 'unidad']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Tortillas de maíz hecho a mano',
            'Tortilla fresca de maíz',
            1,
            'TMAZ-50-UD',
            ['unidades' => '50']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Crema de leche',
            'Crema para salsas y repostería',
            1,
            'CREM-LEC-1L',
            ['volumen' => '1L'],
            null,
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Mantequilla de cocina',
            'Mantequilla sin sal en bloque',
            1,
            'MANT-COC-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Postres de repostería',
            'Pastelitos variados para boda y eventos',
            1,
            'POST-VAR-01',
            ['sabor' => 'variado']
        );
    }
}
