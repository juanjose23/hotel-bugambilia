<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Toallas (CAT_PRO_BLAN_TOALLAS).
 *
 * Migra 1:1 el bloque original (T-BANIO-BCO, T-MANOS-BCO, T-PISO-BCO y
 * FA-50-70-BCO intactos) y amplía con toallas para piscina, spa y gimnasio.
 */
final class ProductoBlancoToallaSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $cat = (int) $this->catalogoIds['CAT_PRO_BLAN_TOALLAS'];

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Toalla de baño',
            '70x140cm 500gr',
            2,
            [
                ['codigo' => 'T-BANIO-BCO', 'nombre' => 'Toalla de baño blanca', 'atributos' => ['color' => 'blanco', 'peso' => '500gr'], 'peso' => 500],
                ['codigo' => 'T-BANIO-CREMA', 'nombre' => 'Toalla de baño crema', 'atributos' => ['color' => 'crema', 'peso' => '500gr'], 'peso' => 500],
                ['codigo' => 'T-BANIO-GRIS', 'nombre' => 'Toalla de baño gris', 'atributos' => ['color' => 'gris', 'peso' => '500gr'], 'peso' => 500],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Toalla de manos',
            '50x100cm 300gr',
            2,
            [
                ['codigo' => 'T-MANOS-BCO', 'nombre' => 'Toalla de manos blanca', 'atributos' => ['color' => 'blanco', 'peso' => '300gr'], 'peso' => 300],
                ['codigo' => 'T-MANOS-CREMA', 'nombre' => 'Toalla de manos crema', 'atributos' => ['color' => 'crema', 'peso' => '300gr'], 'peso' => 300],
                ['codigo' => 'T-MANOS-GRIS', 'nombre' => 'Toalla de manos gris', 'atributos' => ['color' => 'gris', 'peso' => '300gr'], 'peso' => 300],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Toalla de piso',
            '50x70cm 400gr',
            2,
            [
                ['codigo' => 'T-PISO-BCO', 'nombre' => 'Toalla de piso blanca', 'atributos' => ['color' => 'blanco', 'peso' => '400gr'], 'peso' => 400],
                ['codigo' => 'T-PISO-CREMA', 'nombre' => 'Toalla de piso crema', 'atributos' => ['color' => 'crema', 'peso' => '400gr'], 'peso' => 400],
                ['codigo' => 'T-PISO-GRIS', 'nombre' => 'Toalla de piso gris', 'atributos' => ['color' => 'gris', 'peso' => '400gr'], 'peso' => 400],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Toalla de piscina',
            'Gran formato 150x200cm de microfibra',
            2,
            [
                ['codigo' => 'T-PISC-BCO', 'nombre' => 'Toalla piscina blanca', 'atributos' => ['color' => 'blanco', 'material' => 'microfibra'], 'peso' => 800],
                ['codigo' => 'T-PISC-AZUL', 'nombre' => 'Toalla piscina azul marino', 'atributos' => ['color' => 'azul', 'material' => 'microfibra'], 'peso' => 800],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Toalla para spa',
            'Toalla suave atóxica para tratamientos',
            2,
            [
                ['codigo' => 'T-SPA-BCO', 'nombre' => 'Toalla spa blanca', 'atributos' => ['color' => 'blanco', 'uso' => 'spa']],
                ['codigo' => 'T-SPA-CAFE', 'nombre' => 'Toalla spa café', 'atributos' => ['color' => 'café', 'uso' => 'spa']],
            ]
        );

        $this->crearProductoSimple(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Toalla facial',
            'Toallita facial suave 30x30cm',
            2,
            'T-FACIAL-BCO',
            ['color' => 'blanco', 'tamaño' => '30x30']
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Toalla de gimnasio',
            'Toalla deportiva absorbente',
            2,
            [
                ['codigo' => 'T-GYM-BCO', 'nombre' => 'Toalla gym blanca', 'atributos' => ['color' => 'blanco', 'material' => 'microfibra'], 'peso' => 250],
                ['codigo' => 'T-GYM-NEGRA', 'nombre' => 'Toalla gym negra', 'atributos' => ['color' => 'negro', 'material' => 'microfibra'], 'peso' => 250],
            ]
        );

        $this->crearProductoSimple(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Toalla de playa',
            'Toalla extragrande para huéspedes',
            2,
            'T-PLAYA-BAJO',
            ['color' => 'beige', 'tamaño' => '180x180']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Toallita de bar',
            'Paño absorbente para el área de bebidas',
            2,
            'T-BAR-SERVIDA',
            ['uso' => 'bar']
        );
    }
}
