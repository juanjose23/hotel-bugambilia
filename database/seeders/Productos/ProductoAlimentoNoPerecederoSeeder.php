<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Alimentos No Perecederos (CAT_PRO_ALIM_NOPER).
 */
final class ProductoAlimentoNoPerecederoSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            (int) $this->catalogoIds['MARC_PG'],
            (int) $this->catalogoIds['UNI_KG'],
            'Café molido',
            'Café 100% arábica',
            2,
            [
                ['codigo' => 'CAF-1KG-SUAVE', 'nombre' => 'Café molido suave 1kg', 'atributos' => ['tipo' => 'suave', 'tostado' => 'medio'], 'peso' => 1000],
                ['codigo' => 'CAF-1KG-FUERTE', 'nombre' => 'Café molido fuerte 1kg', 'atributos' => ['tipo' => 'fuerte', 'tostado' => 'oscuro'], 'peso' => 1000],
                ['codigo' => 'CAF-500G-PREMIUM', 'nombre' => 'Café molido premium 500g', 'atributos' => ['tipo' => 'premium', 'tostado' => 'medio'], 'peso' => 500],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            (int) $this->catalogoIds['MARC_GEN'],
            (int) $this->catalogoIds['UNI_KG'],
            'Azúcar blanca',
            'Azúcar refinada',
            2,
            [
                ['codigo' => 'AZU-500G', 'nombre' => 'Azúcar blanca 500g bolsa', 'atributos' => ['peso' => '500g', 'empaque' => 'bolsa'], 'peso' => 500],
                ['codigo' => 'AZU-1KG', 'nombre' => 'Azúcar blanca 1kg bolsa', 'atributos' => ['peso' => '1kg', 'empaque' => 'bolsa'], 'peso' => 1000],
                ['codigo' => 'AZU-5KG', 'nombre' => 'Azúcar blanca 5kg bolsa', 'atributos' => ['peso' => '5kg', 'empaque' => 'bolsa'], 'peso' => 5000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_CAJA'],
            'Cereal de desayuno',
            'Cereal de maíz en caja 500g',
            2,
            [
                ['codigo' => 'CER-500-REGULAR', 'nombre' => 'Cereal Corn Flakes regular 500g', 'atributos' => ['tipo' => 'regular', 'peso' => '500g'], 'peso' => 500],
                ['codigo' => 'CER-500-CHOCOL', 'nombre' => 'Cereal Corn Flakes chocolate 500g', 'atributos' => ['tipo' => 'chocolate', 'peso' => '500g'], 'peso' => 500],
                ['codigo' => 'CER-500-MIEL', 'nombre' => 'Cereal Corn Flakes miel 500g', 'atributos' => ['tipo' => 'miel', 'peso' => '500g'], 'peso' => 500],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Agua embotellada',
            'Agua embotellada sin gas',
            2,
            [
                ['codigo' => 'AG-500-BOT', 'nombre' => 'Agua embotellada 500ml', 'atributos' => ['volumen' => '500ml', 'formato' => 'botella']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Arroz blanco',
            'Arroz de grano largo',
            2,
            [
                ['codigo' => 'ARR-1KG', 'nombre' => 'Arroz blanco 1kg', 'atributos' => ['peso' => '1kg'], 'peso' => 1000],
                ['codigo' => 'ARR-5KG', 'nombre' => 'Arroz blanco 5kg', 'atributos' => ['peso' => '5kg'], 'peso' => 5000],
                ['codigo' => 'ARR-25KG', 'nombre' => 'Arroz blanco saco 25kg', 'atributos' => ['peso' => '25kg'], 'peso' => 25000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Frijoles rojos',
            'Frijoles seleccionados',
            2,
            [
                ['codigo' => 'FRI-1KG', 'nombre' => 'Frijoles rojos 1kg', 'atributos' => ['peso' => '1kg'], 'peso' => 1000],
                ['codigo' => 'FRI-5KG', 'nombre' => 'Frijoles rojos 5kg', 'atributos' => ['peso' => '5kg'], 'peso' => 5000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Aceite de cocina',
            'Aceite vegetal neutro para cocina',
            2,
            [
                ['codigo' => 'ACE-1L', 'nombre' => 'Aceite de cocina 1L', 'atributos' => ['volumen' => '1L'], 'volumen' => 1000],
                ['codigo' => 'ACE-5L', 'nombre' => 'Aceite de cocina 5L', 'atributos' => ['volumen' => '5L'], 'volumen' => 5000],
                ['codigo' => 'ACE-20L', 'nombre' => 'Aceite de cocina 20L', 'atributos' => ['volumen' => '20L'], 'volumen' => 20000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Sal de mesa',
            'Sal refinada para cocina',
            2,
            [
                ['codigo' => 'SAL-500G', 'nombre' => 'Sal de mesa 500g', 'atributos' => ['peso' => '500g'], 'peso' => 500],
                ['codigo' => 'SAL-1KG', 'nombre' => 'Sal de mesa 1kg', 'atributos' => ['peso' => '1kg'], 'peso' => 1000],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_GR'],
            'Pimienta negra',
            'Pimienta molida fina',
            2,
            'PIM-100G',
            ['peso' => '100g'],
            100
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Pasta larga',
            'Espagueti para banquetería',
            2,
            'PASTA-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_CAJA'],
            'Atún en lata',
            'Atún sólido en aceite',
            2,
            'ATUN-140G',
            ['presentación' => 'lata 140g']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_PAQ'],
            'Galletas de soda',
            'Galletas saladas para minibar',
            2,
            'GSODA-100G',
            ['peso' => '100g']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Harina de trigo',
            'Harina para panadería y repostería',
            2,
            'HAR-1KG',
            ['peso' => '1kg'],
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Sopa instantánea',
            'Sopa deshidratada rápida',
            2,
            'SOPA-INST-01',
            ['presentación' => 'sobre']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Chocolate en polvo',
            'Cacao para bebidas calientes',
            2,
            'CHOC-500G',
            ['peso' => '500g'],
            500
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_CAJA'],
            'Té en sobres',
            'Té de sabores para habitación',
            2,
            [
                ['codigo' => 'TE-BOLSA-50', 'nombre' => 'Té negro caja 50 sobres', 'atributos' => ['tipo' => 'negro', 'sobres' => '50']],
                ['codigo' => 'TE-MANZ-50', 'nombre' => 'Té de manzanilla caja 50', 'atributos' => ['tipo' => 'manzanilla', 'sobres' => '50']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Vinagre blanco',
            'Vinagre de alcohol para cocina',
            2,
            'VIN-1L',
            ['volumen' => '1L'],
            null,
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_CAJA'],
            'Salsa de tomate',
            'Salsa de tomate en lata',
            2,
            'SAR-400G',
            ['presentación' => 'lata 400g']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Salsa de soya',
            'Salsa de soya para cocina asiática',
            2,
            'SOYA-500ML',
            ['volumen' => '500ml']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_PAQ'],
            'Snack de papas',
            'Papas fritas de bolsa para minibar',
            2,
            'SNK-PAPA-40G',
            ['peso' => '40g']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            (int) $this->catalogoIds['UNI_PAQ'],
            'Mezcla de frutos secos',
            'Nueces y pasas para minibar',
            2,
            'SNK-MIX-50G',
            ['peso' => '50g']
        );
    }
}
