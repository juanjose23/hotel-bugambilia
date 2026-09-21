<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Productos de Amenidades de Baño (CAT_PRO_AMEN_BANIO).
 *
 * Incluye la migración 1:1 del seeder original (SKUs intactos para
 * StockInicialPackSeeder, LimpiezaStockSeeder y KitSeeder) y ampliación.
 */
final class ProductoAmenidadBanoSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            (int) $this->catalogoIds['MARC_PG'],
            (int) $this->catalogoIds['UNI_ML'],
            'Shampoo',
            'Shampoo suave para hotel',
            2,
            [
                ['codigo' => 'SH-030-S', 'nombre' => 'Shampoo 30 ml sobre', 'atributos' => ['tamaño' => '30ml', 'formato' => 'sobre'], 'volumen' => 30],
                ['codigo' => 'SH-060-S', 'nombre' => 'Shampoo 60 ml sobre', 'atributos' => ['tamaño' => '60ml', 'formato' => 'sobre'], 'volumen' => 60],
                ['codigo' => 'SH-200-B', 'nombre' => 'Shampoo 200 ml botella', 'atributos' => ['tamaño' => '200ml', 'formato' => 'botella'], 'volumen' => 200],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            (int) $this->catalogoIds['MARC_PG'],
            (int) $this->catalogoIds['UNI_ML'],
            'Acondicionador',
            'Acondicionador hidratante',
            2,
            [
                ['codigo' => 'AC-030-S', 'nombre' => 'Acondicionador 30 ml sobre', 'atributos' => ['tamaño' => '30ml', 'formato' => 'sobre'], 'volumen' => 30],
                ['codigo' => 'AC-060-S', 'nombre' => 'Acondicionador 60 ml sobre', 'atributos' => ['tamaño' => '60ml', 'formato' => 'sobre'], 'volumen' => 60],
                ['codigo' => 'AC-200-B', 'nombre' => 'Acondicionador 200 ml botella', 'atributos' => ['tamaño' => '200ml', 'formato' => 'botella'], 'volumen' => 200],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            (int) $this->catalogoIds['MARC_GEN'],
            (int) $this->catalogoIds['UNI_GR'],
            'Jabón de tocador',
            'Jabón neutro en pastilla',
            2,
            [
                ['codigo' => 'JB-015-P', 'nombre' => 'Jabón 15g pastilla blanco', 'atributos' => ['tamaño' => '15g', 'tipo' => 'blanco'], 'peso' => 15],
                ['codigo' => 'JB-015-ROSA', 'nombre' => 'Jabón 15g pastilla rosa', 'atributos' => ['tamaño' => '15g', 'tipo' => 'rosa'], 'peso' => 15],
                ['codigo' => 'JB-025-P', 'nombre' => 'Jabón 25g pastilla hotel', 'atributos' => ['tamaño' => '25g', 'tipo' => 'premium'], 'peso' => 25],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            (int) $this->catalogoIds['MARC_GEN'],
            (int) $this->catalogoIds['UNI_ML'],
            'Gel de ducha',
            'Gel corporal refrescante',
            2,
            [
                ['codigo' => 'GEL-030-S', 'nombre' => 'Gel de ducha 30 ml sobre', 'atributos' => ['tamaño' => '30ml', 'formato' => 'sobre'], 'volumen' => 30],
                ['codigo' => 'GEL-200-B', 'nombre' => 'Gel de ducha 200 ml botella', 'atributos' => ['tamaño' => '200ml', 'formato' => 'botella'], 'volumen' => 200],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            (int) $this->catalogoIds['MARC_PG'],
            (int) $this->catalogoIds['UNI_ML'],
            'Loción corporal',
            'Loción humectante de cuerpo',
            2,
            [
                ['codigo' => 'LOC-060-T', 'nombre' => 'Loción corporal 60 ml tubo', 'atributos' => ['tamaño' => '60ml', 'formato' => 'tubo'], 'volumen' => 60],
                ['codigo' => 'LOC-200-B', 'nombre' => 'Loción corporal 200 ml botella', 'atributos' => ['tamaño' => '200ml', 'formato' => 'botella'], 'volumen' => 200],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            (int) $this->catalogoIds['MARC_GEN'],
            (int) $this->catalogoIds['UNI_ML'],
            'Jabón líquido de manos',
            'Jabón antibacterial para dispensador',
            2,
            [
                ['codigo' => 'JLM-500-P', 'nombre' => 'Jabón líquido 500 ml dispensador', 'atributos' => ['formato' => 'dispensador', 'volumen' => '500ml'], 'volumen' => 500],
                ['codigo' => 'JLM-1L-R', 'nombre' => 'Jabón líquido 1 litro recarga', 'atributos' => ['formato' => 'recarga', 'volumen' => '1L'], 'volumen' => 1000],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Gorro de baño',
            'Gorro plástico descartable',
            2,
            [
                ['codigo' => 'GB-001-BCO', 'nombre' => 'Gorro de baño blanco', 'atributos' => ['color' => 'blanco', 'tamaño' => 'único']],
                ['codigo' => 'GB-001-AZUL', 'nombre' => 'Gorro de baño azul', 'atributos' => ['color' => 'azul', 'tamaño' => 'único']],
                ['codigo' => 'GB-001-ROSA', 'nombre' => 'Gorro de baño rosa', 'atributos' => ['color' => 'rosa', 'tamaño' => 'único']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            (int) $this->catalogoIds['MARC_PG'],
            (int) $this->catalogoIds['UNI_ML'],
            'Crema corporal',
            'Crema corporal hidratante',
            2,
            [
                ['codigo' => 'CR-030-S', 'nombre' => 'Crema corporal 30 ml sobre', 'atributos' => ['tamaño' => '30ml', 'formato' => 'sobre'], 'volumen' => 30],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Papel higiénico',
            'Papel higiénico para baño',
            2,
            [
                ['codigo' => 'PH-ROLLO-STD', 'nombre' => 'Papel higiénico estándar', 'atributos' => ['formato' => 'rollo']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Kit dental',
            'Cepillo + pasta dental mini',
            2,
            [
                ['codigo' => 'KD-001-EST', 'nombre' => 'Kit dental estándar blanco', 'atributos' => ['tipo' => 'estándar', 'color' => 'blanco']],
                ['codigo' => 'KD-001-PREMIUM', 'nombre' => 'Kit dental premium azul', 'atributos' => ['tipo' => 'premium', 'color' => 'azul']],
                ['codigo' => 'KD-001-KIDS', 'nombre' => 'Kit dental niños rosa', 'atributos' => ['tipo' => 'niños', 'color' => 'rosa']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Peine de viaje',
            'Peine compacto para huéspedes',
            2,
            'PEI-VIA-STD',
            ['tamaño' => 'único']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Cepillo de dientes',
            'Cepillo dental desechable',
            2,
            'CD-DES-STD',
            ['tipo' => 'desechable']
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Vaso de baño',
            'Vaso acrílico reutilizable para tocador',
            2,
            [
                ['codigo' => 'VB-BCO-STD', 'nombre' => 'Vaso de baño blanco', 'atributos' => ['color' => 'blanco', 'tamaño' => '250ml']],
                ['codigo' => 'VB-AZUL-STD', 'nombre' => 'Vaso de baño azul', 'atributos' => ['color' => 'azul', 'tamaño' => '250ml']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            (int) $this->catalogoIds['MARC_KIMBERLY'],
            (int) $this->catalogoIds['UNI_UD'],
            'Algodón desmaquillante',
            'Bola de algodón higiénica en bolsa',
            2,
            'ALG-BOL-40',
            ['piezas' => '40']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Hisopos',
            'Hisopos de algodón en caja',
            2,
            'HISO-CAJA-100',
            ['piezas' => '100']
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Kit de afeitado',
            'Maquinilla + gel de afeitar',
            2,
            [
                ['codigo' => 'KA-001-BASICO', 'nombre' => 'Kit afeitado básico', 'atributos' => ['nivel' => 'básico', 'piezas' => '2']],
                ['codigo' => 'KA-001-PREMIUM', 'nombre' => 'Kit afeitado premium', 'atributos' => ['nivel' => 'premium', 'piezas' => '4']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Botas desechables',
            'Botas plásticas para uso individual',
            2,
            [
                ['codigo' => 'BD-001-BCO', 'nombre' => 'Botas desechables blancas', 'atributos' => ['color' => 'blanco', 'talla' => 'única']],
                ['codigo' => 'BD-001-GRIS', 'nombre' => 'Botas desechables grises', 'atributos' => ['color' => 'gris', 'talla' => 'única']],
            ]
        );

        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Zapatillas de baño',
            'Zapatillas toilette de tela suave',
            2,
            [
                ['codigo' => 'ZP-001-BCO', 'nombre' => 'Zapatillas de baño blancas', 'atributos' => ['color' => 'blanco', 'material' => 'tela']],
                ['codigo' => 'ZP-001-NAV', 'nombre' => 'Zapatillas de baño navy', 'atributos' => ['color' => 'navy', 'material' => 'tela']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Toallita facial',
            'Toallita desmaquillante individual',
            2,
            'TF-IND-001',
            ['formato' => 'individual']
        );
    }
}
