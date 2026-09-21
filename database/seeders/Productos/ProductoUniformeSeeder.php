<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Uniformes de personal (CAT_PRO_UNIFORMES).
 *
 * Cubre todas las áreas del hotel con tallas S/M/L/XL (y XS/XXL donde aplica).
 */
final class ProductoUniformeSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $cat = (int) $this->catalogoIds['CAT_PRO_UNIFORMES'];
        $uni = (int) $this->catalogoIds['UNI_UD'];

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Uniforme de camarista',
            'Conjunto blusa y mandil de ama de llaves',
            2,
            [
                ['codigo' => 'U-CAM-S', 'nombre' => 'Uniforme camarista talla S', 'atributos' => ['talla' => 'S', 'área' => 'camera']],
                ['codigo' => 'U-CAM-M', 'nombre' => 'Uniforme camarista talla M', 'atributos' => ['talla' => 'M', 'área' => 'camera']],
                ['codigo' => 'U-CAM-L', 'nombre' => 'Uniforme camarista talla L', 'atributos' => ['talla' => 'L', 'área' => 'camera']],
                ['codigo' => 'U-CAM-XL', 'nombre' => 'Uniforme camarista talla XL', 'atributos' => ['talla' => 'XL', 'área' => 'camera']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Uniforme de cocina',
            'Chamarra y pantalón para cocina',
            2,
            [
                ['codigo' => 'U-COC-S', 'nombre' => 'Uniforme cocina talla S', 'atributos' => ['talla' => 'S', 'área' => 'cocina']],
                ['codigo' => 'U-COC-M', 'nombre' => 'Uniforme cocina talla M', 'atributos' => ['talla' => 'M', 'área' => 'cocina']],
                ['codigo' => 'U-COC-L', 'nombre' => 'Uniforme cocina talla L', 'atributos' => ['talla' => 'L', 'área' => 'cocina']],
                ['codigo' => 'U-COC-XL', 'nombre' => 'Uniforme cocina talla XL', 'atributos' => ['talla' => 'XL', 'área' => 'cocina']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Uniforme de mesero',
            'Camisa y pantalón formal para restaurante',
            2,
            [
                ['codigo' => 'U-MES-S', 'nombre' => 'Uniforme mesero talla S', 'atributos' => ['talla' => 'S', 'área' => 'restaurante']],
                ['codigo' => 'U-MES-M', 'nombre' => 'Uniforme mesero talla M', 'atributos' => ['talla' => 'M', 'área' => 'restaurante']],
                ['codigo' => 'U-MES-L', 'nombre' => 'Uniforme mesero talla L', 'atributos' => ['talla' => 'L', 'área' => 'restaurante']],
                ['codigo' => 'U-MES-XL', 'nombre' => 'Uniforme mesero talla XL', 'atributos' => ['talla' => 'XL', 'área' => 'restaurante']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Uniforme de recepción',
            'Conjunto elegante de front desk',
            2,
            [
                ['codigo' => 'U-REC-S', 'nombre' => 'Uniforme recepción talla S', 'atributos' => ['talla' => 'S', 'área' => 'recepción']],
                ['codigo' => 'U-REC-M', 'nombre' => 'Uniforme recepción talla M', 'atributos' => ['talla' => 'M', 'área' => 'recepción']],
                ['codigo' => 'U-REC-L', 'nombre' => 'Uniforme recepción talla L', 'atributos' => ['talla' => 'L', 'área' => 'recepción']],
                ['codigo' => 'U-REC-XL', 'nombre' => 'Uniforme recepción talla XL', 'atributos' => ['talla' => 'XL', 'área' => 'recepción']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Uniforme de mantenimiento',
            'Overol y playera técnica resistente',
            2,
            [
                ['codigo' => 'U-MANT-S', 'nombre' => 'Uniforme mantenimiento talla S', 'atributos' => ['talla' => 'S', 'área' => 'mantenimiento']],
                ['codigo' => 'U-MANT-M', 'nombre' => 'Uniforme mantenimiento talla M', 'atributos' => ['talla' => 'M', 'área' => 'mantenimiento']],
                ['codigo' => 'U-MANT-L', 'nombre' => 'Uniforme mantenimiento talla L', 'atributos' => ['talla' => 'L', 'área' => 'mantenimiento']],
                ['codigo' => 'U-MANT-XL', 'nombre' => 'Uniforme mantenimiento talla XL', 'atributos' => ['talla' => 'XL', 'área' => 'mantenimiento']],
                ['codigo' => 'U-MANT-XXL', 'nombre' => 'Uniforme mantenimiento talla XXL', 'atributos' => ['talla' => 'XXL', 'área' => 'mantenimiento']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Uniforme de seguridad',
            'Camisa de manga larga y pantalón',
            2,
            [
                ['codigo' => 'U-SEG-S', 'nombre' => 'Uniforme seguridad talla S', 'atributos' => ['talla' => 'S', 'área' => 'seguridad']],
                ['codigo' => 'U-SEG-M', 'nombre' => 'Uniforme seguridad talla M', 'atributos' => ['talla' => 'M', 'área' => 'seguridad']],
                ['codigo' => 'U-SEG-L', 'nombre' => 'Uniforme seguridad talla L', 'atributos' => ['talla' => 'L', 'área' => 'seguridad']],
                ['codigo' => 'U-SEG-XL', 'nombre' => 'Uniforme seguridad talla XL', 'atributos' => ['talla' => 'XL', 'área' => 'seguridad']],
                ['codigo' => 'U-SEG-XXL', 'nombre' => 'Uniforme seguridad talla XXL', 'atributos' => ['talla' => 'XXL', 'área' => 'seguridad']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Uniforme de chef',
            'Chaqueta doble botonadura de chef',
            2,
            [
                ['codigo' => 'U-CHF-S', 'nombre' => 'Chaqueta chef talla S', 'atributos' => ['talla' => 'S', 'área' => 'chef']],
                ['codigo' => 'U-CHF-M', 'nombre' => 'Chaqueta chef talla M', 'atributos' => ['talla' => 'M', 'área' => 'chef']],
                ['codigo' => 'U-CHF-L', 'nombre' => 'Chaqueta chef talla L', 'atributos' => ['talla' => 'L', 'área' => 'chef']],
                ['codigo' => 'U-CHF-XL', 'nombre' => 'Chaqueta chef talla XL', 'atributos' => ['talla' => 'XL', 'área' => 'chef']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Uniforme de gerencia',
            'Traje ejecutivo con camisa blanca',
            2,
            [
                ['codigo' => 'U-GER-S', 'nombre' => 'Uniforme gerencia talla S', 'atributos' => ['talla' => 'S', 'área' => 'gerencia']],
                ['codigo' => 'U-GER-M', 'nombre' => 'Uniforme gerencia talla M', 'atributos' => ['talla' => 'M', 'área' => 'gerencia']],
                ['codigo' => 'U-GER-L', 'nombre' => 'Uniforme gerencia talla L', 'atributos' => ['talla' => 'L', 'área' => 'gerencia']],
                ['codigo' => 'U-GER-XL', 'nombre' => 'Uniforme gerencia talla XL', 'atributos' => ['talla' => 'XL', 'área' => 'gerencia']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Uniforme de spa',
            'Túnica y pantalón de spa',
            2,
            [
                ['codigo' => 'U-SPA-S', 'nombre' => 'Uniforme spa talla S', 'atributos' => ['talla' => 'S', 'área' => 'spa']],
                ['codigo' => 'U-SPA-M', 'nombre' => 'Uniforme spa talla M', 'atributos' => ['talla' => 'M', 'área' => 'spa']],
                ['codigo' => 'U-SPA-L', 'nombre' => 'Uniforme spa talla L', 'atributos' => ['talla' => 'L', 'área' => 'spa']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Delantal de cocina',
            'Delantal azul de tiras cruzadas',
            2,
            [
                ['codigo' => 'DEL-COC-S', 'nombre' => 'Delantal cocina talla S', 'atributos' => ['talla' => 'S', 'área' => 'cocina']],
                ['codigo' => 'DEL-COC-M', 'nombre' => 'Delantal cocina talla M', 'atributos' => ['talla' => 'M', 'área' => 'cocina']],
                ['codigo' => 'DEL-COC-L', 'nombre' => 'Delantal cocina talla L', 'atributos' => ['talla' => 'L', 'área' => 'cocina']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            $uni,
            'Camisa de trabajo',
            'Playera de poliéster con logo',
            2,
            [
                ['codigo' => 'PLAY-S', 'nombre' => 'Playera labor talla S', 'atributos' => ['talla' => 'S']],
                ['codigo' => 'PLAY-M', 'nombre' => 'Playera labor talla M', 'atributos' => ['talla' => 'M']],
                ['codigo' => 'PLAY-L', 'nombre' => 'Playera labor talla L', 'atributos' => ['talla' => 'L']],
                ['codigo' => 'PLAY-XL', 'nombre' => 'Playera labor talla XL', 'atributos' => ['talla' => 'XL']],
            ]
        );
    }
}
