<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Herramientas de limpieza (CAT_PRO_LIMP_HERR).
 */
final class ProductoLimpiezaHerramientaSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Escoba',
            'Escoba de cerda suave',
            2,
            [
                ['codigo' => 'ESC-01-SUAVE', 'nombre' => 'Escoba cerda suave', 'atributos' => ['tipo' => 'suave', 'material' => 'cerda']],
                ['codigo' => 'ESC-01-DURA', 'nombre' => 'Escoba cerda dura', 'atributos' => ['tipo' => 'dura', 'material' => 'cerda']],
                ['codigo' => 'ESC-01-PLASTICO', 'nombre' => 'Escoba plástico', 'atributos' => ['tipo' => 'estándar', 'material' => 'plástico']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Trapeador',
            'Trapeador de microfibra 40cm',
            2,
            [
                ['codigo' => 'TRAP-MF-BLANCO', 'nombre' => 'Trapeador microfibra blanco', 'atributos' => ['color' => 'blanco', 'tamaño' => '40cm']],
                ['codigo' => 'TRAP-MF-GRIS', 'nombre' => 'Trapeador microfibra gris', 'atributos' => ['color' => 'gris', 'tamaño' => '40cm']],
                ['codigo' => 'TRAP-MF-AZUL', 'nombre' => 'Trapeador microfibra azul', 'atributos' => ['color' => 'azul', 'tamaño' => '40cm']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Cubeta con escurridor',
            'Cubeta 20 litros con carro',
            2,
            [
                ['codigo' => 'CUB-ESC-BLANCA', 'nombre' => 'Cubeta escurridor blanca 20L', 'atributos' => ['color' => 'blanco', 'capacidad' => '20L'], 'volumen' => 20000],
                ['codigo' => 'CUB-ESC-GRIS', 'nombre' => 'Cubeta escurridor gris 20L', 'atributos' => ['color' => 'gris', 'capacidad' => '20L'], 'volumen' => 20000],
                ['codigo' => 'CUB-ESC-ROJA', 'nombre' => 'Cubeta escurridor roja 20L', 'atributos' => ['color' => 'rojo', 'capacidad' => '20L'], 'volumen' => 20000],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Jalador de agua',
            'Jalador de cauche para piso',
            2,
            'JAL-40CM',
            ['ancho' => '40cm']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Recogedor de piso',
            'Recogedor plástico reforzado',
            2,
            'REC-01-PLAST',
            ['material' => 'plástico']
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Cepillo para inodoro',
            'Cepillo con base',
            2,
            [
                ['codigo' => 'CIN-01-BCO', 'nombre' => 'Cepillo inodoro blanco', 'atributos' => ['color' => 'blanco']],
                ['codigo' => 'CIN-01-NEG', 'nombre' => 'Cepillo inodoro negro', 'atributos' => ['color' => 'negro']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_PAQ'],
            'Franela de microfibra',
            'Paño absorbente para pulido',
            2,
            'FRAN-MF-10',
            ['material' => 'microfibra', 'piezas' => '10']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_PAQ'],
            'Esponja de cocina',
            'Esponja doble cara',
            2,
            'ESP-DOBLE-20',
            ['piezas' => '20']
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_PAQ'],
            'Guantes de limpieza',
            'Guantes de hule lavar',
            2,
            [
                ['codigo' => 'GUANT-S', 'nombre' => 'Guantes de hule talla S', 'atributos' => ['talla' => 'S']],
                ['codigo' => 'GUANT-M', 'nombre' => 'Guantes de hule talla M', 'atributos' => ['talla' => 'M']],
                ['codigo' => 'GUANT-L', 'nombre' => 'Guantes de hule talla L', 'atributos' => ['talla' => 'L']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Balde plástico',
            'Balde 12 litros',
            2,
            'BALDE-12L',
            ['capacidad' => '12L']
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Dispensador de jabón',
            'Dispensador de pared para baño',
            2,
            [
                ['codigo' => 'DISP-500ML', 'nombre' => 'Dispensador jabón 500ml', 'atributos' => ['capacidad' => '500ml']],
                ['codigo' => 'DISP-1L', 'nombre' => 'Dispensador jabón 1L', 'atributos' => ['capacidad' => '1L']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Carro de limpieza',
            'Carro housekeeping básico',
            2,
            'CCARRO-KP',
            ['compartimentos' => '3']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Escobillón de exterior',
            'Escobillón fuerte para terrazas',
            2,
            'EXT-01-DURO',
            ['material' => 'cerda dura']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Atomizador de limpieza',
            'Spray desechable para químicos',
            2,
            'ATOM-01',
            ['capacidad' => '750ml']
        );
    }
}
