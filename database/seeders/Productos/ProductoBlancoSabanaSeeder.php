<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Sábanas y lencería de cama (CAT_PRO_BLAN_SABANAS).
 *
 * Migra 1:1 el bloque original (SB-KING-BCO, SE-KING-BCO y FA-50-70-BCO
 * intactos para StockInicialPackSeeder, LimpiezaStockSeeder y KitSeeder)
 * y amplía con sábanas de otros tamaños, protectores y cobertores.
 */
final class ProductoBlancoSabanaSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $cat = (int) $this->catalogoIds['CAT_PRO_BLAN_SABANAS'];

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Sábana bajera King',
            'Sábana ajustable 200x200+30cm',
            2,
            [
                ['codigo' => 'SB-KING-BCO', 'nombre' => 'Sábana bajera King blanca', 'atributos' => ['color' => 'blanco', 'material' => 'algodón 100%'], 'peso' => 250],
                ['codigo' => 'SB-KING-CREMA', 'nombre' => 'Sábana bajera King crema', 'atributos' => ['color' => 'crema', 'material' => 'algodón 100%'], 'peso' => 250],
                ['codigo' => 'SB-KING-GRIS', 'nombre' => 'Sábana bajera King gris', 'atributos' => ['color' => 'gris perla', 'material' => 'algodón 100%'], 'peso' => 250],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Sábana encimera King',
            'Sábana plana 280x300cm',
            2,
            [
                ['codigo' => 'SE-KING-BCO', 'nombre' => 'Sábana encimera King blanca', 'atributos' => ['color' => 'blanco', 'material' => 'algodón 100%'], 'peso' => 280],
                ['codigo' => 'SE-KING-CREMA', 'nombre' => 'Sábana encimera King crema', 'atributos' => ['color' => 'crema', 'material' => 'algodón 100%'], 'peso' => 280],
                ['codigo' => 'SE-KING-GRIS', 'nombre' => 'Sábana encimera King gris', 'atributos' => ['color' => 'gris perla', 'material' => 'algodón 100%'], 'peso' => 280],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Funda de almohada',
            '50x70cm algodón',
            2,
            [
                ['codigo' => 'FA-50-70-BCO', 'nombre' => 'Funda almohada 50x70 blanca', 'atributos' => ['tamaño' => '50x70', 'color' => 'blanco'], 'peso' => 50],
                ['codigo' => 'FA-50-70-CREMA', 'nombre' => 'Funda almohada 50x70 crema', 'atributos' => ['tamaño' => '50x70', 'color' => 'crema'], 'peso' => 50],
                ['codigo' => 'FA-60-60-BCO', 'nombre' => 'Funda almohada 60x60 blanca', 'atributos' => ['tamaño' => '60x60', 'color' => 'blanco'], 'peso' => 55],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Sábana bajera Queen',
            'Sábana ajustable 160x200+30cm',
            2,
            [
                ['codigo' => 'SB-QUEEN-BCO', 'nombre' => 'Sábana bajera Queen blanca', 'atributos' => ['color' => 'blanco', 'material' => 'algodón 100%'], 'peso' => 220],
                ['codigo' => 'SB-QUEEN-CREMA', 'nombre' => 'Sábana bajera Queen crema', 'atributos' => ['color' => 'crema', 'material' => 'algodón 100%'], 'peso' => 220],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Sábana encimera Queen',
            'Sábana plana 230x280cm',
            2,
            [
                ['codigo' => 'SE-QUEEN-BCO', 'nombre' => 'Sábana encimera Queen blanca', 'atributos' => ['color' => 'blanco', 'material' => 'algodón 100%'], 'peso' => 250],
                ['codigo' => 'SE-QUEEN-CREMA', 'nombre' => 'Sábana encimera Queen crema', 'atributos' => ['color' => 'crema', 'material' => 'algodón 100%'], 'peso' => 250],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Sábana bajera Matrimonial',
            'Sábana ajustable 135x190+30cm',
            2,
            [
                ['codigo' => 'SB-MAT-BCO', 'nombre' => 'Sábana bajera matrimonial blanca', 'atributos' => ['color' => 'blanco', 'material' => 'algodón 100%'], 'peso' => 200],
                ['codigo' => 'SB-MAT-CREMA', 'nombre' => 'Sábana bajera matrimonial crema', 'atributos' => ['color' => 'crema', 'material' => 'algodón 100%'], 'peso' => 200],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Sábana encimera Matrimonial',
            'Sábana plana 200x260cm',
            2,
            [
                ['codigo' => 'SE-MAT-BCO', 'nombre' => 'Sábana encimera matrimonial blanca', 'atributos' => ['color' => 'blanco', 'material' => 'algodón 100%'], 'peso' => 230],
                ['codigo' => 'SE-MAT-CREMA', 'nombre' => 'Sábana encimera matrimonial crema', 'atributos' => ['color' => 'crema', 'material' => 'algodón 100%'], 'peso' => 230],
            ]
        );

        $this->crearProductoSimple(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Protector de colchón King',
            'Funda protectora impermeable con elástico',
            2,
            'PROT-KING-BCO',
            ['color' => 'blanco', 'tamaño' => '200x200']
        );

        $this->crearProductoSimple(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Protector de colchón Queen',
            'Funda protectora impermeable con elástico',
            2,
            'PROT-QUEEN-BCO',
            ['color' => 'blanco', 'tamaño' => '160x200']
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Cobertor de cama King',
            'Cobertor acolchado reforzado',
            2,
            [
                ['codigo' => 'COB-KING-BCO', 'nombre' => 'Cobertor King blanco', 'atributos' => ['color' => 'blanco', 'material' => 'polialgodón']],
                ['codigo' => 'COB-KING-CREMA', 'nombre' => 'Cobertor King crema', 'atributos' => ['color' => 'crema', 'material' => 'polialgodón']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Cobertor de cama Queen',
            'Cobertor acolchado reforzado',
            2,
            [
                ['codigo' => 'COB-QUEEN-BCO', 'nombre' => 'Cobertor Queen blanco', 'atributos' => ['color' => 'blanco', 'material' => 'polialgodón']],
                ['codigo' => 'COB-QUEEN-CREMA', 'nombre' => 'Cobertor Queen crema', 'atributos' => ['color' => 'crema', 'material' => 'polialgodón']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Edredón King',
            'Edredón suave con relleno de fibra',
            2,
            [
                ['codigo' => 'EDR-KING-BCO', 'nombre' => 'Edredón King blanco', 'atributos' => ['color' => 'blanco', 'tamaño' => '260x240']],
                ['codigo' => 'EDR-KING-IVO', 'nombre' => 'Edredón King marfil', 'atributos' => ['color' => 'marfil', 'tamaño' => '260x240']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Almohada estándar',
            'Almohada de pluma sintética',
            2,
            [
                ['codigo' => 'ALM-50-70-STD', 'nombre' => 'Almohada estándar 50x70', 'atributos' => ['tamaño' => '50x70', 'relleno' => 'fibra']],
                ['codigo' => 'ALM-50-70-SUAVE', 'nombre' => 'Almohada extra suave 50x70', 'atributos' => ['tamaño' => '50x70', 'relleno' => 'fibra suave']],
            ]
        );

        $this->crearProductoConVariante(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Almohada premium',
            'Almohada de soporte para huéspedes exigentes',
            2,
            [
                ['codigo' => 'ALM-PRE-ORTO', 'nombre' => 'Almohada premium ortopédica', 'atributos' => ['tipo' => 'ortopédica', 'relleno' => 'memory']],
                ['codigo' => 'ALM-PRE-PLUMA', 'nombre' => 'Almohada premium pluma', 'atributos' => ['tipo' => 'pluma', 'relleno' => 'ganso']],
            ]
        );

        $this->crearProductoSimple(
            $cat,
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Colchoneta de lectura',
            'Colchoneta decorativa tamaño individual',
            2,
            'COLCH-LECT-BCO',
            ['color' => 'blanco', 'tamaño' => '90x190']
        );
    }
}
