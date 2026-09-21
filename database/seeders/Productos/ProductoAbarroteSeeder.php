<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Abarrotes y Despensa (CAT_PRO_ABARROTES).
 */
final class ProductoAbarroteSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Frijol enlatado',
            'Frijol negro pre-cocido en lata',
            2,
            'FRI-LATA-425G',
            ['presentación' => 'lata 425g']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Conserva de verduras',
            'Verduras variadas en conserva',
            2,
            'VER-CONS-400G',
            ['presentación' => 'lata 400g']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Harina de maíz',
            'Harina de maíz para tortillas',
            2,
            'MAIZ-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Sopas y cremas envasadas',
            'Caldo listo para cocina',
            2,
            [
                ['codigo' => 'SOB-500G', 'nombre' => 'Caldo de res en caja 500g', 'atributos' => ['tipo' => 'res', 'peso' => '500g'], 'peso' => 500],
                ['codigo' => 'SOB-POLLO-500G', 'nombre' => 'Caldo de pollo en caja 500g', 'atributos' => ['tipo' => 'pollo', 'peso' => '500g'], 'peso' => 500],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_CAJA'],
            'Salsa inglesa',
            'Salsa sazonadora para cocina',
            2,
            'SAL-ING-200ML',
            ['volumen' => '200ml']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_CAJA'],
            'Mayonesa',
            'Mayonesa en frasco',
            2,
            'MAY-400G',
            ['presentación' => 'frasco 400g']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_CAJA'],
            'Mostaza',
            'Mostaza amarilla de mesa',
            2,
            'MOS-250G',
            ['presentación' => 'frasco 250g']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_CAJA'],
            'Salsa de chile',
            'Chilerío picante maggi',
            2,
            'CHIL-MAG-140ML',
            ['volumen' => '140ml']
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Granos y leguminosas',
            'Lentejas, garbanzos y arvejas',
            2,
            [
                ['codigo' => 'LEN-1KG', 'nombre' => 'Lentejas 1kg', 'atributos' => ['tipo' => 'lentejas'], 'peso' => 1000],
                ['codigo' => 'GARB-1KG', 'nombre' => 'Garbanzos 1kg', 'atributos' => ['tipo' => 'garbanzos'], 'peso' => 1000],
                ['codigo' => 'ARV-1KG', 'nombre' => 'Arvejas 1kg', 'atributos' => ['tipo' => 'arvejas'], 'peso' => 1000],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Suero en polvo',
            'Lácteo en polvo para panadería',
            2,
            'SUE-POLVO-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_PAQ'],
            'Pan tostado',
            'Pan de bolsa tostado',
            2,
            'TOS-250G',
            ['peso' => '250g']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_CAJA'],
            'Repostería semi listas',
            'Mezcla preparada de pastel',
            2,
            'PAST-MIX-500G',
            ['presentación' => 'caja 500g']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Colorante alimentario',
            'Colorante en gel para repostería',
            2,
            'COLOR-ALIM-30',
            ['presentación' => 'frasco 30ml']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ABARROTES'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Levadura seca',
            'Levadura instantánea de panadero',
            2,
            'LEV-100G',
            ['peso' => '100g'],
            100
        );
    }
}
