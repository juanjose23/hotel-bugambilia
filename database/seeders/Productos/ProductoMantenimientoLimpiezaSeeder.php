<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Insumos de Lavandería (CAT_PRO_LIMP_LAVANDERIA).
 */
final class ProductoMantenimientoLimpiezaSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_LAVANDERIA'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Jabón en polvo lavandería',
            'Detergente industrial de lavandería',
            2,
            [
                ['codigo' => 'JPL-5KG', 'nombre' => 'Jabón lavandería 5kg', 'atributos' => ['presentación' => 'bolsa', 'peso' => '5kg'], 'peso' => 5000],
                ['codigo' => 'JPL-25KG', 'nombre' => 'Jabón lavandería 25kg', 'atributos' => ['presentación' => 'tambor', 'peso' => '25kg'], 'peso' => 25000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_LAVANDERIA'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Suavizante de telas',
            'Suavizante concentrado para textiles',
            2,
            [
                ['codigo' => 'SUA-5L', 'nombre' => 'Suavizante 5L', 'atributos' => ['tamaño' => '5L'], 'volumen' => 5000],
                ['codigo' => 'SUA-20L', 'nombre' => 'Suavizante 20L', 'atributos' => ['tamaño' => '20L'], 'volumen' => 20000],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_LAVANDERIA'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Blanqueador de ropa',
            'Quitamanchas y blanqueador textil',
            2,
            'BLQ-5L',
            ['tamaño' => '5L'],
            null,
            5000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_LAVANDERIA'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Quitamanchas de grasa',
            'Tratamiento previo para manchas',
            2,
            'QMLAV-1L',
            ['tamaño' => '1L'],
            null,
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_LAVANDERIA'],
            null,
            (int) $this->catalogoIds['UNI_PAQ'],
            'Bolsas de lavandería',
            'Bolsas de malla para lavado',
            2,
            'BOL-LAV-MALLA',
            ['material' => 'malla', 'piezas' => '10']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_LAVANDERIA'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Canasta de lavandería',
            'Canasta de carga de ropa',
            2,
            'CAN-LAV-01',
            ['capacidad' => '50L']
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_LAVANDERIA'],
            null,
            (int) $this->catalogoIds['UNI_PAQ'],
            'Etiquetas de lavandería',
            'Etiquetas para inventario de lencería',
            2,
            [
                ['codigo' => 'ETI-NUM-100', 'nombre' => 'Etiquetas numeradas 100 uds', 'atributos' => ['tipo' => 'numeradas', 'piezas' => '100']],
                ['codigo' => 'ETI-COLOR-100', 'nombre' => 'Etiquetas por color 100 uds', 'atributos' => ['tipo' => 'color', 'piezas' => '100']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_LAVANDERIA'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Perfume de tela',
            'Aromatizante post lavado',
            2,
            'PERF-TELA-1L',
            ['tamaño' => '1L'],
            null,
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_LAVANDERIA'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Carro de servicio de lavandería',
            'Carro para transporte de ropa',
            2,
            'CAR-LAV-GRANDE',
            ['capacidad' => 'grande']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_LAVANDERIA'],
            null,
            (int) $this->catalogoIds['UNI_KG'],
            'Almidón de lavandería',
            'Almidón para planchado',
            2,
            'ALM-5KG',
            ['peso' => '5kg'],
            5000
        );
    }
}
