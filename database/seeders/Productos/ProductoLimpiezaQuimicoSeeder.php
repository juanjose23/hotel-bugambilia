<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Químicos de limpieza (CAT_PRO_LIMP_QUIM).
 */
final class ProductoLimpiezaQuimicoSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            (int) $this->catalogoIds['MARC_ECOLAB'],
            (int) $this->catalogoIds['UNI_LIT'],
            'Detergente líquido',
            'Detergente multiusos concentrado',
            2,
            [
                ['codigo' => 'DET-1L', 'nombre' => 'Detergente 1 Litro', 'atributos' => ['tamaño' => '1L', 'concentración' => 'normal'], 'volumen' => 1000],
                ['codigo' => 'DET-5L', 'nombre' => 'Detergente 5 Litros garrafa', 'atributos' => ['tamaño' => '5L', 'concentración' => 'concentrado'], 'volumen' => 5000],
                ['codigo' => 'DET-20L', 'nombre' => 'Detergente 20 Litros bidón', 'atributos' => ['tamaño' => '20L', 'concentración' => 'concentrado'], 'volumen' => 20000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            (int) $this->catalogoIds['MARC_ECOLAB'],
            (int) $this->catalogoIds['UNI_LIT'],
            'Desinfectante',
            'Desinfectante para superficies',
            2,
            [
                ['codigo' => 'DES-500ML', 'nombre' => 'Desinfectante 500 ml spray', 'atributos' => ['tamaño' => '500ml', 'formato' => 'spray'], 'volumen' => 500],
                ['codigo' => 'DES-5L', 'nombre' => 'Desinfectante 5 Litros', 'atributos' => ['tamaño' => '5L', 'formato' => 'garrafa'], 'volumen' => 5000],
                ['codigo' => 'DES-20L', 'nombre' => 'Desinfectante 20 Litros concentrado', 'atributos' => ['tamaño' => '20L', 'formato' => 'bidón'], 'volumen' => 20000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            (int) $this->catalogoIds['MARC_GEN'],
            (int) $this->catalogoIds['UNI_ML'],
            'Limpiavidrios',
            'Limpiador para cristales 500ml',
            2,
            [
                ['codigo' => 'LV-500-SPRAY', 'nombre' => 'Limpiavidrios spray 500ml', 'atributos' => ['formato' => 'spray', 'volumen' => '500ml'], 'volumen' => 500],
                ['codigo' => 'LV-500-CONCENT', 'nombre' => 'Limpiavidrios concentrado 500ml', 'atributos' => ['formato' => 'concentrado', 'volumen' => '500ml'], 'volumen' => 500],
                ['codigo' => 'LV-5L', 'nombre' => 'Limpiavidrios 5 Litros garrafa', 'atributos' => ['formato' => 'garrafa', 'volumen' => '5L'], 'volumen' => 5000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Cloro',
            'Cloro concentrado para desinfección',
            2,
            [
                ['codigo' => 'CLO-1L', 'nombre' => 'Cloro 1L', 'atributos' => ['tamaño' => '1L', 'concentración' => '5%'], 'volumen' => 1000],
                ['codigo' => 'CLO-5L', 'nombre' => 'Cloro 5L', 'atributos' => ['tamaño' => '5L'], 'volumen' => 5000],
                ['codigo' => 'CLO-20L', 'nombre' => 'Cloro 20L bidón', 'atributos' => ['tamaño' => '20L'], 'volumen' => 20000],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Amoníaco',
            'Amoníaco de limpieza concentrado',
            2,
            'AMO-1L',
            ['tamaño' => '1L'],
            null,
            1000
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Desengrasante',
            'Desengrasante de cocina industrial',
            2,
            [
                ['codigo' => 'DG-1L', 'nombre' => 'Desengrasante 1L', 'atributos' => ['tamaño' => '1L'], 'volumen' => 1000],
                ['codigo' => 'DG-5L', 'nombre' => 'Desengrasante 5L', 'atributos' => ['tamaño' => '5L'], 'volumen' => 5000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            (int) $this->catalogoIds['MARC_GEN'],
            (int) $this->catalogoIds['UNI_ML'],
            'Ambientador aerosol',
            'Ambientador para áreas comunes',
            2,
            [
                ['codigo' => 'AMB-360-FRES', 'nombre' => 'Ambientador 360ml frescura', 'atributos' => ['fragancia' => 'frescura', 'volumen' => '360ml']],
                ['codigo' => 'AMB-360-LAV', 'nombre' => 'Ambientador 360ml lavanda', 'atributos' => ['fragancia' => 'lavanda', 'volumen' => '360ml']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            null,
            (int) $this->catalogoIds['UNI_PAQ'],
            'Bolsas de basura negra',
            'Bolsas para botes grandes',
            2,
            'BB-90X110',
            ['calibre' => 'grueso', 'tamaño' => '90x110cm']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Alcohol antiséptico',
            'Alcohol 70% para limpieza',
            2,
            'ALC-1L',
            ['tamaño' => '1L'],
            null,
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            null,
            (int) $this->catalogoIds['UNI_ML'],
            'Jabón lavaplatos',
            'Jabón para vajilla de cocina',
            2,
            'JLP-500ML',
            ['tamaño' => '500ml'],
            null,
            500
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            null,
            (int) $this->catalogoIds['UNI_LIT'],
            'Limpiador de pisos',
            'Desinfectante astringente para piso',
            2,
            [
                ['codigo' => 'LMP-1L', 'nombre' => 'Limpiador de pisos 1L', 'atributos' => ['fragancia' => 'pino', 'tamaño' => '1L']],
                ['codigo' => 'LMP-5L', 'nombre' => 'Limpiador de pisos 5L', 'atributos' => ['fragancia' => 'pino', 'tamaño' => '5L']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            null,
            (int) $this->catalogoIds['UNI_GR'],
            'Cera para piso',
            'Cera acrílica para pisos finos',
            2,
            'CERA-1L',
            ['tamaño' => '1L'],
            null,
            1000
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_LIMP_QUIM'],
            null,
            (int) $this->catalogoIds['UNI_GR'],
            'Quitamanchas textil',
            'Removedor de manchas para tapicería',
            2,
            'QM-500ML',
            ['tamaño' => '500ml'],
            null,
            500
        );
    }
}
