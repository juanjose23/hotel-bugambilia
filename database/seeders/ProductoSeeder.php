<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductoSeeder extends Seeder
{
    public function run(): void
    {
        // IDs de catálogos por código (asumiendo que ya ejecutaste CatalogoTipoSeeder y CatalogoSeeder)
        $catalogoIds = DB::table('catalogos')->pluck('id', 'codigo');

        // ---------- 1. AMENIDADES BAÑO ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_AMEN_BANIO'],
            $catalogoIds['MARC_PG'],
            $catalogoIds['UNI_ML'],
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
            $catalogoIds['CAT_PRO_AMEN_BANIO'],
            $catalogoIds['MARC_PG'],
            $catalogoIds['UNI_ML'],
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
            $catalogoIds['CAT_PRO_AMEN_BANIO'],
            $catalogoIds['MARC_GEN'],
            $catalogoIds['UNI_GR'],
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
            $catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            $catalogoIds['UNI_UD'],
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
            $catalogoIds['CAT_PRO_AMEN_BANIO'],
            null,
            $catalogoIds['UNI_UD'],
            'Kit dental',
            'Cepillo + pasta dental mini',
            2,
            [
                ['codigo' => 'KD-001-EST', 'nombre' => 'Kit dental estándar blanco', 'atributos' => ['tipo' => 'estándar', 'color' => 'blanco']],
                ['codigo' => 'KD-001-PREMIUM', 'nombre' => 'Kit dental premium azul', 'atributos' => ['tipo' => 'premium', 'color' => 'azul']],
                ['codigo' => 'KD-001-KIDS', 'nombre' => 'Kit dental niños rosa', 'atributos' => ['tipo' => 'niños', 'color' => 'rosa']],
            ]
        );

        // ---------- 2. AMENIDADES HABITACIÓN ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            $catalogoIds['UNI_UD'],
            'Bolígrafo',
            'Bolígrafo personalizado',
            2,
            [
                ['codigo' => 'BOL-HAB-AZUL', 'nombre' => 'Bolígrafo azul con logo', 'atributos' => ['color' => 'azul', 'tipo' => 'estándar']],
                ['codigo' => 'BOL-HAB-NEGRO', 'nombre' => 'Bolígrafo negro con logo', 'atributos' => ['color' => 'negro', 'tipo' => 'estándar']],
                ['codigo' => 'BOL-HAB-ROJO', 'nombre' => 'Bolígrafo rojo con logo', 'atributos' => ['color' => 'rojo', 'tipo' => 'estándar']],
            ]
        );

        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            $catalogoIds['UNI_UD'],
            'Bloc de notas',
            'Bloc pequeño 15x10cm',
            2,
            [
                ['codigo' => 'BLOC-NOT-BCO', 'nombre' => 'Bloc de notas blanco 15x10', 'atributos' => ['color' => 'blanco', 'tamaño' => '15x10cm']],
                ['codigo' => 'BLOC-NOT-CREMA', 'nombre' => 'Bloc de notas crema 15x10', 'atributos' => ['color' => 'crema', 'tamaño' => '15x10cm']],
                ['codigo' => 'BLOC-NOT-AMARILLO', 'nombre' => 'Bloc de notas amarillo 15x10', 'atributos' => ['color' => 'amarillo', 'tamaño' => '15x10cm']],
            ]
        );

        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            $catalogoIds['UNI_UD'],
            'Kit de costura',
            'Aguja e hilos básicos',
            2,
            [
                ['codigo' => 'KC-001-BASICO', 'nombre' => 'Kit costura básico 10 piezas', 'atributos' => ['nivel' => 'básico', 'piezas' => '10']],
                ['codigo' => 'KC-001-COMPLETO', 'nombre' => 'Kit costura completo 20 piezas', 'atributos' => ['nivel' => 'completo', 'piezas' => '20']],
                ['codigo' => 'KC-001-PREMIUM', 'nombre' => 'Kit costura premium 30 piezas', 'atributos' => ['nivel' => 'premium', 'piezas' => '30']],
            ]
        );

        // ---------- 3. ALIMENTOS PERECEDEROS ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_ALIM_PEREC'],
            $catalogoIds['MARC_GEN'],
            $catalogoIds['UNI_LIT'],
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
            $catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            $catalogoIds['UNI_UD'],
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
            $catalogoIds['CAT_PRO_ALIM_PEREC'],
            null,
            $catalogoIds['UNI_KG'],
            'Frutas variadas',
            'Frutas de temporada',
            1,
            [
                ['codigo' => 'FRUT-TROPICALES', 'nombre' => 'Frutas tropicales mix 1kg', 'atributos' => ['tipo' => 'tropicales', 'peso' => '1kg'], 'peso' => 1000],
                ['codigo' => 'FRUT-CITRICOS', 'nombre' => 'Frutas cítricos mix 1kg', 'atributos' => ['tipo' => 'cítricos', 'peso' => '1kg'], 'peso' => 1000],
                ['codigo' => 'FRUT-BERRIES', 'nombre' => 'Berries variados 1kg', 'atributos' => ['tipo' => 'berries', 'peso' => '1kg'], 'peso' => 1000],
            ]
        );

        // ---------- 4. ALIMENTOS NO PERECEDEROS ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_ALIM_NOPER'],
            $catalogoIds['MARC_PG'],
            $catalogoIds['UNI_KG'],
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
            $catalogoIds['CAT_PRO_ALIM_NOPER'],
            $catalogoIds['MARC_GEN'],
            $catalogoIds['UNI_KG'],
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
            $catalogoIds['CAT_PRO_ALIM_NOPER'],
            null,
            $catalogoIds['UNI_CAJA'],
            'Cereal de desayuno',
            'Cereal de maíz en caja 500g',
            2,
            [
                ['codigo' => 'CER-500-REGULAR', 'nombre' => 'Cereal Corn Flakes regular 500g', 'atributos' => ['tipo' => 'regular', 'peso' => '500g'], 'peso' => 500],
                ['codigo' => 'CER-500-CHOCOL', 'nombre' => 'Cereal Corn Flakes chocolate 500g', 'atributos' => ['tipo' => 'chocolate', 'peso' => '500g'], 'peso' => 500],
                ['codigo' => 'CER-500-MIEL', 'nombre' => 'Cereal Corn Flakes miel 500g', 'atributos' => ['tipo' => 'miel', 'peso' => '500g'], 'peso' => 500],
            ]
        );

        // ---------- 5. LIMPIEZA QUÍMICOS ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_LIMP_QUIM'],
            $catalogoIds['MARC_ECOLAB'],
            $catalogoIds['UNI_LIT'],
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
            $catalogoIds['CAT_PRO_LIMP_QUIM'],
            $catalogoIds['MARC_ECOLAB'],
            $catalogoIds['UNI_LIT'],
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
            $catalogoIds['CAT_PRO_LIMP_QUIM'],
            $catalogoIds['MARC_GEN'],
            $catalogoIds['UNI_ML'],
            'Limpiavidrios',
            'Limpiador para cristales 500ml',
            2,
            [
                ['codigo' => 'LV-500-SPRAY', 'nombre' => 'Limpiavidrios spray 500ml', 'atributos' => ['formato' => 'spray', 'volumen' => '500ml'], 'volumen' => 500],
                ['codigo' => 'LV-500-CONCENT', 'nombre' => 'Limpiavidrios concentrado 500ml', 'atributos' => ['formato' => 'concentrado', 'volumen' => '500ml'], 'volumen' => 500],
                ['codigo' => 'LV-5L', 'nombre' => 'Limpiavidrios 5 Litros garrafa', 'atributos' => ['formato' => 'garrafa', 'volumen' => '5L'], 'volumen' => 5000],
            ]
        );

        // ---------- 6. HERRAMIENTAS DE LIMPIEZA ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            $catalogoIds['UNI_UD'],
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
            $catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            $catalogoIds['UNI_UD'],
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
            $catalogoIds['CAT_PRO_LIMP_HERR'],
            null,
            $catalogoIds['UNI_UD'],
            'Cubeta con escurridor',
            'Cubeta 20 litros con carro',
            2,
            [
                ['codigo' => 'CUB-ESC-BLANCA', 'nombre' => 'Cubeta escurridor blanca 20L', 'atributos' => ['color' => 'blanco', 'capacidad' => '20L'], 'volumen' => 20000],
                ['codigo' => 'CUB-ESC-GRIS', 'nombre' => 'Cubeta escurridor gris 20L', 'atributos' => ['color' => 'gris', 'capacidad' => '20L'], 'volumen' => 20000],
                ['codigo' => 'CUB-ESC-ROJA', 'nombre' => 'Cubeta escurridor roja 20L', 'atributos' => ['color' => 'rojo', 'capacidad' => '20L'], 'volumen' => 20000],
            ]
        );

        // ---------- 7. ACTIVOS FIJOS MOBILIARIO ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_ACT_MOB'],
            null,
            $catalogoIds['UNI_UD'],
            'Cama King Size',
            'Base + colchón King',
            2,
            [
                ['codigo' => 'CAM-KING-BLANCA', 'nombre' => 'Cama King blanca 200x200', 'atributos' => ['color' => 'blanco', 'tamaño' => '200x200'], 'peso' => 150000],
                ['codigo' => 'CAM-KING-NOGAL', 'nombre' => 'Cama King nogal 200x200', 'atributos' => ['color' => 'nogal', 'tamaño' => '200x200'], 'peso' => 150000],
                ['codigo' => 'CAM-KING-WENGUE', 'nombre' => 'Cama King wengué 200x200', 'atributos' => ['color' => 'wengué', 'tamaño' => '200x200'], 'peso' => 150000],
            ]
        );

        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_ACT_MOB'],
            null,
            $catalogoIds['UNI_UD'],
            'Mesa de noche',
            'Mesa auxiliar con cajón',
            2,
            [
                ['codigo' => 'MES-NOC-BLANCA', 'nombre' => 'Mesa noche blanca 40x40x50', 'atributos' => ['color' => 'blanco', 'tamaño' => '40x40x50'], 'peso' => 25000],
                ['codigo' => 'MES-NOC-NOGAL', 'nombre' => 'Mesa noche nogal 40x40x50', 'atributos' => ['color' => 'nogal', 'tamaño' => '40x40x50'], 'peso' => 25000],
                ['codigo' => 'MES-NOC-WENGUE', 'nombre' => 'Mesa noche wengué 40x40x50', 'atributos' => ['color' => 'wengué', 'tamaño' => '40x40x50'], 'peso' => 25000],
            ]
        );

        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_ACT_MOB'],
            null,
            $catalogoIds['UNI_UD'],
            'Escritorio ejecutivo',
            'Escritorio 120x60cm',
            2,
            [
                ['codigo' => 'ESC-EJE-BLANCA', 'nombre' => 'Escritorio ejecutivo blanco 120x60', 'atributos' => ['color' => 'blanco', 'tamaño' => '120x60'], 'peso' => 45000],
                ['codigo' => 'ESC-EJE-NOGAL', 'nombre' => 'Escritorio ejecutivo nogal 120x60', 'atributos' => ['color' => 'nogal', 'tamaño' => '120x60'], 'peso' => 45000],
                ['codigo' => 'ESC-EJE-GRIS', 'nombre' => 'Escritorio ejecutivo gris 120x60', 'atributos' => ['color' => 'gris', 'tamaño' => '120x60'], 'peso' => 45000],
            ]
        );

        // ---------- 8. ACTIVOS FIJOS ELECTRÓNICOS ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_ACT_ELECTRO'],
            $catalogoIds['MARC_SAMSUNG'],
            $catalogoIds['UNI_UD'],
            'Televisor 43"',
            'Smart TV Samsung 43" UHD',
            2,
            [
                ['codigo' => 'TV-S43-2022', 'nombre' => 'Samsung TV 43" UHD 2022', 'atributos' => ['tamaño' => '43"', 'año' => '2022', 'resolución' => '4K']],
                ['codigo' => 'TV-S43-2023', 'nombre' => 'Samsung TV 43" UHD 2023', 'atributos' => ['tamaño' => '43"', 'año' => '2023', 'resolución' => '4K']],
                ['codigo' => 'TV-S43-2024', 'nombre' => 'Samsung TV 43" UHD 2024', 'atributos' => ['tamaño' => '43"', 'año' => '2024', 'resolución' => '4K']],
            ]
        );

        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_ACT_ELECTRO'],
            $catalogoIds['MARC_GEN'],
            $catalogoIds['UNI_UD'],
            'Secador de pelo',
            'Secador profesional 2200W',
            2,
            [
                ['codigo' => 'SEC-PRO-1800', 'nombre' => 'Secador profesional 1800W', 'atributos' => ['potencia' => '1800W', 'nivel' => 'estándar']],
                ['codigo' => 'SEC-PRO-2200', 'nombre' => 'Secador profesional 2200W', 'atributos' => ['potencia' => '2200W', 'nivel' => 'premium']],
                ['codigo' => 'SEC-PRO-3000', 'nombre' => 'Secador profesional 3000W', 'atributos' => ['potencia' => '3000W', 'nivel' => 'ultra']],
            ]
        );

        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_ACT_ELECTRO'],
            null,
            $catalogoIds['UNI_UD'],
            'Teléfono de habitación',
            'Teléfono analógico simple',
            2,
            [
                ['codigo' => 'TEL-HAB-BLANCO', 'nombre' => 'Teléfono blanco', 'atributos' => ['color' => 'blanco', 'tipo' => 'analógico']],
                ['codigo' => 'TEL-HAB-NEGRO', 'nombre' => 'Teléfono negro', 'atributos' => ['color' => 'negro', 'tipo' => 'analógico']],
                ['codigo' => 'TEL-HAB-BEIGE', 'nombre' => 'Teléfono beige', 'atributos' => ['color' => 'beige', 'tipo' => 'analógico']],
            ]
        );

        // ---------- 9. HERRAMIENTAS DE MANTENIMIENTO ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_ACT_MANT'],
            null,
            $catalogoIds['UNI_UD'],
            'Taladro percutor',
            'Taladro 650W con maletín',
            2,
            [
                ['codigo' => 'TAL-500', 'nombre' => 'Taladro percutor 500W', 'atributos' => ['potencia' => '500W', 'tipo' => 'básico'], 'peso' => 2500],
                ['codigo' => 'TAL-650', 'nombre' => 'Taladro percutor 650W', 'atributos' => ['potencia' => '650W', 'tipo' => 'estándar'], 'peso' => 2800],
                ['codigo' => 'TAL-800', 'nombre' => 'Taladro percutor 800W profesional', 'atributos' => ['potencia' => '800W', 'tipo' => 'profesional'], 'peso' => 3200],
            ]
        );

        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_ACT_MANT'],
            null,
            $catalogoIds['UNI_UD'],
            'Juego de destornilladores',
            'Set 8 piezas',
            2,
            [
                ['codigo' => 'JD-8P', 'nombre' => 'Juego destornilladores 8 piezas', 'atributos' => ['piezas' => '8', 'tipo' => 'básico'], 'peso' => 300],
                ['codigo' => 'JD-16P', 'nombre' => 'Juego destornilladores 16 piezas', 'atributos' => ['piezas' => '16', 'tipo' => 'completo'], 'peso' => 600],
                ['codigo' => 'JD-32P', 'nombre' => 'Juego destornilladores 32 piezas profesional', 'atributos' => ['piezas' => '32', 'tipo' => 'profesional'], 'peso' => 1200],
            ]
        );

        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_ACT_MANT'],
            null,
            $catalogoIds['UNI_UD'],
            'Llave inglesa 12"',
            'Llave ajustable profesional',
            2,
            [
                ['codigo' => 'LL-8', 'nombre' => 'Llave inglesa 8 pulgadas', 'atributos' => ['tamaño' => '8"', 'material' => 'acero cromado'], 'peso' => 200],
                ['codigo' => 'LL-12', 'nombre' => 'Llave inglesa 12 pulgadas', 'atributos' => ['tamaño' => '12"', 'material' => 'acero cromado'], 'peso' => 350],
                ['codigo' => 'LL-16', 'nombre' => 'Llave inglesa 16 pulgadas profesional', 'atributos' => ['tamaño' => '16"', 'material' => 'acero cromado'], 'peso' => 500],
            ]
        );

        // ---------- 10. SÁBANAS ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_BLAN_SABANAS'],
            null,
            $catalogoIds['UNI_UD'],
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
            $catalogoIds['CAT_PRO_BLAN_SABANAS'],
            null,
            $catalogoIds['UNI_UD'],
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
            $catalogoIds['CAT_PRO_BLAN_SABANAS'],
            null,
            $catalogoIds['UNI_UD'],
            'Funda de almohada',
            '50x70cm algodón',
            2,
            [
                ['codigo' => 'FA-50-70-BCO', 'nombre' => 'Funda almohada 50x70 blanca', 'atributos' => ['tamaño' => '50x70', 'color' => 'blanco'], 'peso' => 50],
                ['codigo' => 'FA-50-70-CREMA', 'nombre' => 'Funda almohada 50x70 crema', 'atributos' => ['tamaño' => '50x70', 'color' => 'crema'], 'peso' => 50],
                ['codigo' => 'FA-60-60-BCO', 'nombre' => 'Funda almohada 60x60 blanca', 'atributos' => ['tamaño' => '60x60', 'color' => 'blanco'], 'peso' => 55],
            ]
        );

        // ---------- 11. TOALLAS ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_BLAN_TOALLAS'],
            null,
            $catalogoIds['UNI_UD'],
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
            $catalogoIds['CAT_PRO_BLAN_TOALLAS'],
            null,
            $catalogoIds['UNI_UD'],
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
            $catalogoIds['CAT_PRO_BLAN_TOALLAS'],
            null,
            $catalogoIds['UNI_UD'],
            'Toalla de piso',
            '50x70cm 400gr',
            2,
            [
                ['codigo' => 'T-PISO-BCO', 'nombre' => 'Toalla de piso blanca', 'atributos' => ['color' => 'blanco', 'peso' => '400gr'], 'peso' => 400],
                ['codigo' => 'T-PISO-CREMA', 'nombre' => 'Toalla de piso crema', 'atributos' => ['color' => 'crema', 'peso' => '400gr'], 'peso' => 400],
                ['codigo' => 'T-PISO-GRIS', 'nombre' => 'Toalla de piso gris', 'atributos' => ['color' => 'gris', 'peso' => '400gr'], 'peso' => 400],
            ]
        );

        // ---------- 12. OTROS TEXTILES ----------
        $this->crearProductoConVariante(
            $catalogoIds['CAT_PRO_BLAN_OTROS'],
            null,
            $catalogoIds['UNI_UD'],
            'Cortina de baño',
            'Cortina impermeable 180x180cm',
            2,
            [
                ['codigo' => 'CORT-BAN-BCO', 'nombre' => 'Cortina de baño blanca', 'atributos' => ['color' => 'blanco', 'tamaño' => '180x180']],
                ['codigo' => 'CORT-BAN-TRANSP', 'nombre' => 'Cortina de baño transparente', 'atributos' => ['color' => 'transparente', 'tamaño' => '180x180']],
                ['codigo' => 'CORT-BAN-NEGRA', 'nombre' => 'Cortina de baño negra', 'atributos' => ['color' => 'negro', 'tamaño' => '180x180']],
            ]
        );

        // =========================================================================
        // ACTIVOS FIJOS ESPECÍFICOS (HABITACIONES Y EVENTOS)
        // =========================================================================

        $activosInfraestructura = [
            'HABITACIONES' => [
                'cat' => 'CAT_PRO_ACT_ELECTRO',
                'items' => [
                    ['nombre' => 'Minibar Silencioso', 'desc' => 'Minibar 40L para habitación', 'v1' => '30 Litros (Compacto)', 'v2' => '40 Litros (Estándar)', 'v3' => '60 Litros (Premium)'],
                    ['nombre' => 'Caja Fuerte Digital', 'desc' => 'Caja fuerte con código y llave', 'v1' => 'Teclado Estándar', 'v2' => 'Biométrica (Huella)', 'v3' => 'Tamaño Laptop 15"'],
                    ['nombre' => 'Aire Acondicionado', 'desc' => 'Split Inverter Frío/Calor', 'v1' => '12,000 BTU', 'v2' => '18,000 BTU', 'v3' => '24,000 BTU (Suites)'],
                    ['nombre' => 'Cerradura RFID', 'desc' => 'Cerradura electrónica para tarjetas', 'v1' => 'Lector Proximidad', 'v2' => 'Bluetooth / App', 'v3' => 'Acero Reforzado'],
                    ['nombre' => 'Cafetera de Habitación', 'desc' => 'Cafetera de goteo/cápsula', 'v1' => 'Goteo (Básica)', 'v2' => 'Cápsulas (Nespresso)', 'v3' => 'Combo Té/Café'],
                ],
            ],
            'EVENTOS' => [
                'cat' => 'CAT_PRO_ACT_MOB',
                'items' => [
                    ['nombre' => 'Proyector Láser', 'desc' => 'Proyector 4000 lúmenes UHD', 'v1' => 'Full HD 1080p', 'v2' => '4K Nativo', 'v3' => 'Tiro Corto'],
                    ['nombre' => 'Sistema de Audio Salón', 'desc' => 'Kit de altavoces y consola', 'v1' => 'Pasivo 2 Altavoces', 'v2' => 'Activo Bluetooth', 'v3' => 'Line Array (Gran salón)'],
                    ['nombre' => 'Silla para Banquete', 'desc' => 'Silla apilable reforzada', 'v1' => 'Tiffany (Madera)', 'v2' => 'Metálica Acolchada', 'v3' => 'Resina Premium'],
                    ['nombre' => 'Mesa Circular Eventos', 'desc' => 'Mesa plegable 1.80m', 'v1' => 'Madera Plegable', 'v2' => 'Plástico HD', 'v3' => 'Estructura Aluminio'],
                    ['nombre' => 'Podio de Madera', 'desc' => 'Atril para conferencistas', 'v1' => 'Clásico Nogal', 'v2' => 'Moderno Acrílico', 'v3' => 'Con Micrófono Integrado'],
                ],
            ],
        ];

        foreach ($activosInfraestructura as $grupo => $info) {
            foreach ($info['items'] as $item) {
                $this->crearProductoConVariante(
                    $catalogoIds[$info['cat']],
                    null,
                    $catalogoIds['UNI_UD'],
                    $item['nombre'],
                    $item['desc']." (Uso: $grupo)",
                    2,
                    [
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-X', 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['modelo' => $item['v1']]],
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-Y', 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['modelo' => $item['v2']]],
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-Z', 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['modelo' => $item['v3']]],
                    ]
                );
            }
        }

        $activosAdicionales = [
            'SEGURIDAD' => [
                'cat' => 'CAT_PRO_ACT_ELECTRO',
                'items' => [
                    ['nombre' => 'Cámara de Vigilancia', 'desc' => 'Cámara CCTV alta resolución', 'v1' => 'Domo 4K IP', 'v2' => 'PTZ Exterior', 'v3' => 'Ojo de Pez 360°'],
                    ['nombre' => 'Grabador NVR', 'desc' => 'Grabador de video en red', 'v1' => '8 Canales (1TB)', 'v2' => '16 Canales (4TB)', 'v3' => '32 Canales (RAID)'],
                    ['nombre' => 'Sistema Incendio', 'desc' => 'Detector y alarma central', 'v1' => 'Detector Humo', 'v2' => 'Estación Manual', 'v3' => 'Sirena con Estrobo'],
                ],
            ],
            'GIMNASIO_POOL' => [
                'cat' => 'CAT_PRO_ACT_MOB',
                'items' => [
                    ['nombre' => 'Caminadora Pro', 'desc' => 'Caminadora profesional alto tráfico', 'v1' => 'Serie 500 (Básica)', 'v2' => 'Serie 700 (Pro)', 'v3' => 'Serie 900 (Touch)'],
                    ['nombre' => 'Elíptica Industrial', 'desc' => 'Máquina elíptica magnética', 'v1' => 'Auto-generada', 'v2' => 'Con Pantalla', 'v3' => 'Heavy Duty'],
                    ['nombre' => 'Camastro de Piscina', 'desc' => 'Camastro resina alta resistencia', 'v1' => 'Blanco Estándar', 'v2' => 'Madera Teca', 'v3' => 'Acolchado Luxury'],
                ],
            ],
            'LOBBY_BAR' => [
                'cat' => 'CAT_PRO_ACT_MOB',
                'items' => [
                    ['nombre' => 'Sofá de Lobby', 'desc' => 'Sofá diseño para áreas comunes', 'v1' => '2 Plazas Tela', 'v2' => '3 Plazas Cuero', 'v3' => 'Modular (L)'],
                    ['nombre' => 'Máquina Espresso', 'desc' => 'Cafetera profesional 2 grupos', 'v1' => '1 Grupo (Compacta)', 'v2' => '2 Grupos (Pro)', 'v3' => '3 Grupos (Elite)'],
                    ['nombre' => 'Molino de Café', 'desc' => 'Molino automático on-demand', 'v1' => 'Básico', 'v2' => 'Dosificador Pro', 'v3' => 'Micrométrico'],
                ],
            ],
        ];

        foreach ($activosAdicionales as $grupo => $info) {
            foreach ($info['items'] as $item) {
                $this->crearProductoConVariante(
                    $catalogoIds[$info['cat']],
                    null,
                    $catalogoIds['UNI_UD'],
                    $item['nombre'],
                    $item['desc']." (Uso: $grupo)",
                    2,
                    [
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-V1', 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['espec' => $item['v1']]],
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-V2', 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['espec' => $item['v2']]],
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-V3', 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['espec' => $item['v3']]],
                    ]
                );
            }
        }

        $activosIndustriales = [
            'COCINA' => [
                'cat' => 'CAT_PRO_ACT_MOB',
                'items' => [
                    ['nombre' => 'Estufa Industrial', 'desc' => 'Estufa 6 quemadores acero inox', 'v1' => 'Gas LP', 'v2' => 'Gas Natural', 'v3' => 'Eléctrica (Inducción)'],
                    ['nombre' => 'Horno de Convección', 'desc' => 'Horno profesional 10 bandejas', 'v1' => 'Básico (Analógico)', 'v2' => 'Digital Programable', 'v3' => 'Doble Cavidad'],
                    ['nombre' => 'Refrigerador Vertical', 'desc' => 'Refrigerador industrial 2 puertas', 'v1' => 'Acero Inoxidable', 'v2' => 'Puerta de Vidrio', 'v3' => 'Congelador/Refri'],
                    ['nombre' => 'Lavavajillas de Capota', 'desc' => 'Lavadora platos alto volumen', 'v1' => 'Ciclo Rápido', 'v2' => 'Con Secado', 'v3' => 'Ahorro de Agua'],
                ],
            ],
            'LAVANDERÍA' => [
                'cat' => 'CAT_PRO_ACT_ELECTRO',
                'items' => [
                    ['nombre' => 'Lavadora Industrial', 'desc' => 'Carga frontal 25kg/50kg', 'v1' => '25 Kg (Estándar)', 'v2' => '50 Kg (Alta Carga)', 'v3' => 'Centrifugado Pro'],
                    ['nombre' => 'Secadora Industrial', 'desc' => 'Secadora alto flujo vapor', 'v1' => 'Eléctrica', 'v2' => 'Gas LP', 'v3' => 'Vapor Directo'],
                    ['nombre' => 'Calandria de Rodillo', 'desc' => 'Planchadora de sábanas industrial', 'v1' => '1.5 Metros', 'v2' => '2 Metros', 'v3' => '3 Metros (Suites)'],
                ],
            ],
            'SERVICIOS_GENERALES' => [
                'cat' => 'CAT_PRO_ACT_MANT',
                'items' => [
                    ['nombre' => 'Generador Eléctrico', 'desc' => 'Planta de emergencia 150KVA', 'v1' => '50 KVA (Respaldos)', 'v2' => '100 KVA', 'v3' => '150 KVA (Full Hotel)'],
                    ['nombre' => 'Bomba Hidroneumática', 'desc' => 'Sistema presión constante', 'v1' => '2 HP', 'v2' => '5 HP', 'v3' => '10 HP (Edificio)'],
                    ['nombre' => 'Calentador Industrial', 'desc' => 'Boiler de alta recuperación', 'v1' => '80 Galones', 'v2' => '120 Galones', 'v3' => 'Solar Industrial'],
                    ['nombre' => 'Cortacésped Tractor', 'desc' => 'Podadora giro cero', 'v1' => 'Manual 20"', 'v2' => 'Tractor 42"', 'v3' => 'Tractor 54"'],
                ],
            ],
            'SISTEMAS' => [
                'cat' => 'CAT_PRO_ACT_ELECTRO',
                'items' => [
                    ['nombre' => 'Servidor de Datos', 'desc' => 'Servidor Rack 1U Xeon', 'v1' => 'Básico (8GB RAM)', 'v2' => 'Medio (32GB RAM)', 'v3' => 'Pro (64GB + SSD)'],
                    ['nombre' => 'Laptop Administrativa', 'desc' => 'Equipo 15" para oficina', 'v1' => 'Core i3 / 8GB', 'v2' => 'Core i5 / 16GB', 'v3' => 'Core i7 / 32GB'],
                    ['nombre' => 'Multifuncional Láser', 'desc' => 'Impresora/Escáner alto volumen', 'v1' => 'Blanco y Negro', 'v2' => 'Color (Oficina)', 'v3' => 'Color (Artes Gráficas)'],
                ],
            ],
        ];

        foreach ($activosIndustriales as $grupo => $info) {
            foreach ($info['items'] as $item) {
                $this->crearProductoConVariante(
                    $catalogoIds[$info['cat']],
                    null,
                    $catalogoIds['UNI_UD'],
                    $item['nombre'],
                    $item['desc']." (Uso: $grupo)",
                    2,
                    [
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-A', 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['capacidad' => $item['v1']]],
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-B', 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['capacidad' => $item['v2']]],
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-C', 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['capacidad' => $item['v3']]],
                    ]
                );
            }
        }

        // =========================================================================
        // GENERACIÓN MASIVA DE MANTENIMIENTO (50 PRODUCTOS TÉCNICOS)
        // =========================================================================

        $mantenimientoData = [
            'ELÉCTRICO' => [
                'cat' => 'CAT_PRO_ACT_MANT',
                'items' => [
                    ['nombre' => 'Interruptor Simple', 'desc' => 'Interruptor de pared 10A', 'v1' => 'Blanco', 'v2' => 'Marfil', 'v3' => 'Negro'],
                    ['nombre' => 'Tomacorriente Doble', 'desc' => 'Toma con tierra 15A', 'v1' => 'Estándar', 'v2' => 'Con USB', 'v3' => 'GFCI (Baño)'],
                    ['nombre' => 'Cable Eléctrico #12', 'desc' => 'Cable de cobre THHN', 'v1' => 'Rojo (Fase)', 'v2' => 'Negro (Fase)', 'v3' => 'Verde (Tierra)'],
                    ['nombre' => 'Bombilla LED 9W', 'desc' => 'Foco ahorrador E27', 'v1' => 'Luz Cálida', 'v2' => 'Luz Fría', 'v3' => 'Luz Neutra'],
                    ['nombre' => 'Breaker de Riel', 'desc' => 'Disyuntor termo-magnético', 'v1' => '15 Amperios', 'v2' => '20 Amperios', 'v3' => '30 Amperios'],
                    ['nombre' => 'Cinta Aislante', 'desc' => 'Cinta PVC profesional', 'v1' => 'Negra', 'v2' => 'Roja', 'v3' => 'Azul'],
                    ['nombre' => 'Caja Rectangular PVC', 'desc' => 'Caja empotrar 2x4', 'v1' => 'Estándar', 'v2' => 'Profunda', 'v3' => 'Intemperie'],
                    ['nombre' => 'Tubo Conduit 1/2"', 'desc' => 'Tubería eléctrica PVC', 'v1' => 'Liviano', 'v2' => 'Pesado', 'v3' => 'Flexible'],
                    ['nombre' => 'Sensor de Movimiento', 'desc' => 'Detector para pasillos', 'v1' => 'Pared', 'v2' => 'Techo', 'v3' => 'Inalámbrico'],
                    ['nombre' => 'Lámpara de Emergencia', 'desc' => 'Doble foco autonomía 90min', 'v1' => 'Básica', 'v2' => 'LED Pro', 'v3' => 'Estanca'],
                ],
            ],
            'PLOMERÍA' => [
                'cat' => 'CAT_PRO_ACT_MANT',
                'items' => [
                    ['nombre' => 'Tubo PVC 1/2"', 'desc' => 'Tubería agua potable', 'v1' => 'SDR 13.5', 'v2' => 'SDR 17', 'v3' => 'SDR 21'],
                    ['nombre' => 'Codo PVC 90° 1/2"', 'desc' => 'Accesorio unión agua', 'v1' => 'Presión', 'v2' => 'Rosca', 'v3' => 'Inserto Metálico'],
                    ['nombre' => 'Válvula de Paso', 'desc' => 'Llave de esfera bronce', 'v1' => '1/2 Pulgada', 'v2' => '3/4 Pulgada', 'v3' => '1 Pulgada'],
                    ['nombre' => 'Teflón Industrial', 'desc' => 'Cinta selladora de roscas', 'v1' => '12mm x 10m', 'v2' => '19mm x 10m', 'v3' => 'Alta Densidad'],
                    ['nombre' => 'Pegamento PVC', 'desc' => 'Cemento solvente', 'v1' => 'Transparente 4oz', 'v2' => 'Azul Rápido 8oz', 'v3' => 'Dorado CPVC 16oz'],
                    ['nombre' => 'Sifón de Lavabo', 'desc' => 'Trampa para desagüe', 'v1' => 'Plástico Blanco', 'v2' => 'Cromado', 'v3' => 'Flexible'],
                    ['nombre' => 'Válvula de Inodoro', 'desc' => 'Kit de descarga completo', 'v1' => 'Universal', 'v2' => 'Doble Descarga', 'v3' => 'Presión Alta'],
                    ['nombre' => 'Manguera de Abasto', 'desc' => 'Conector flexible trenzado', 'v1' => 'Lavabo (12")', 'v2' => 'Inodoro (12")', 'v3' => 'Fregadero (18")'],
                    ['nombre' => 'Grifería Monomando', 'desc' => 'Mezcladora para baño', 'v1' => 'Cromo Pulido', 'v2' => 'Níquel Satinado', 'v3' => 'Negro Mate'],
                    ['nombre' => 'Flotador para Tanque', 'desc' => 'Válvula de llenado superior', 'v1' => 'Boya Plástica', 'v2' => 'Boya Cobre', 'v3' => 'Vertical'],
                ],
            ],
            'PINTURA' => [
                'cat' => 'CAT_PRO_ACT_MANT',
                'items' => [
                    ['nombre' => 'Pintura Látex Interior', 'desc' => 'Pintura acrílica base agua', 'v1' => 'Blanco Hielo', 'v2' => 'Arena Suave', 'v3' => 'Gris Perla'],
                    ['nombre' => 'Esmalte Anticorrosivo', 'desc' => 'Pintura base aceite metal', 'v1' => 'Negro Brillante', 'v2' => 'Blanco Mate', 'v3' => 'Rojo Óxido'],
                    ['nombre' => 'Brocha Profesional', 'desc' => 'Cerda sintética alta calidad', 'v1' => '2 Pulgadas', 'v2' => '3 Pulgadas', 'v3' => '4 Pulgadas'],
                    ['nombre' => 'Rodillo de Felpa', 'desc' => 'Maneral + felpa 9"', 'v1' => 'Pared Lisa', 'v2' => 'Pared Rugosa', 'v3' => 'Microfibra'],
                    ['nombre' => 'Thinner Corriente', 'desc' => 'Solvente para limpieza', 'v1' => '1 Litro', 'v2' => 'Galón', 'v3' => 'Cubeta 19L'],
                    ['nombre' => 'Masilla para Pared', 'desc' => 'Resanador de grietas', 'v1' => 'Pasta 1kg', 'v2' => 'Galón Ready Mix', 'v3' => 'Saco 20kg'],
                    ['nombre' => 'Lija de Agua', 'desc' => 'Papel lija profesional', 'v1' => 'Grano 80', 'v2' => 'Grano 120', 'v3' => 'Grano 240'],
                    ['nombre' => 'Cinta Masking', 'desc' => 'Cinta para pintor', 'v1' => '1/2 Pulgada', 'v2' => '1 Pulgada', 'v3' => '2 Pulgadas'],
                    ['nombre' => 'Barniz para Madera', 'desc' => 'Protector poliuretano', 'v1' => 'Brillante', 'v2' => 'Mate', 'v3' => 'Satinado'],
                    ['nombre' => 'Bandeja para Pintar', 'desc' => 'Bandeja plástica reforzada', 'v1' => 'Pequeña', 'v2' => 'Grande', 'v3' => 'Con Rejilla Metálica'],
                ],
            ],
            'FERRETERÍA' => [
                'cat' => 'CAT_PRO_ACT_MANT',
                'items' => [
                    ['nombre' => 'Cerradura de Pomo', 'desc' => 'Cerradura para puertas', 'v1' => 'Baño (Sin llave)', 'v2' => 'Recámara', 'v3' => 'Entrada Principal'],
                    ['nombre' => 'Bisagra de Acero', 'desc' => 'Bisagra libro 3.5"', 'v1' => 'Cromada', 'v2' => 'Latón', 'v3' => 'Anticorro'],
                    ['nombre' => 'Tornillo Madera', 'desc' => 'Tornillo Phillips zincado', 'v1' => '1 Pulgada', 'v2' => '1.5 Pulgadas', 'v3' => '2 Pulgadas'],
                    ['nombre' => 'Taquete Plástico', 'desc' => 'Anclaje para pared', 'v1' => '1/4 Pulgada', 'v2' => '5/16 Pulgada', 'v3' => '3/8 Pulgada'],
                    ['nombre' => 'Silicona Multiusos', 'desc' => 'Sellador acético', 'v1' => 'Transparente', 'v2' => 'Blanco', 'v3' => 'Negro'],
                    ['nombre' => 'Candado de Seguridad', 'desc' => 'Cuerpo de latón macizo', 'v1' => '30 mm', 'v2' => '40 mm', 'v3' => '50 mm'],
                    ['nombre' => 'Malla Mosquitera', 'desc' => 'Malla fibra de vidrio', 'v1' => 'Gris (Rollo)', 'v2' => 'Negra (Rollo)', 'v3' => 'Aluminio'],
                    ['nombre' => 'Resorte para Puerta', 'desc' => 'Cierrapuertas hidráulico', 'v1' => 'Liviano', 'v2' => 'Medio', 'v3' => 'Pesado'],
                    ['nombre' => 'Manija de Palanca', 'desc' => 'Herraje ergonómico', 'v1' => 'Níquel', 'v2' => 'Cobre Viejo', 'v3' => 'Moderno'],
                    ['nombre' => 'Tope de Puerta', 'desc' => 'Protector de pared', 'v1' => 'Piso', 'v2' => 'Pared', 'v3' => 'Adhesivo'],
                ],
            ],
            'HERRAMIENTAS' => [
                'cat' => 'CAT_PRO_ACT_MANT',
                'items' => [
                    ['nombre' => 'Martillo de Uña', 'desc' => 'Martillo 16oz mango fibra', 'v1' => 'Básico', 'v2' => 'Profesional', 'v3' => 'Antivibración'],
                    ['nombre' => 'Alicate Universal', 'desc' => 'Pinza multiuso 8"', 'v1' => 'Estándar', 'v2' => 'Aislado 1000V', 'v3' => 'Heavy Duty'],
                    ['nombre' => 'Flexómetro', 'desc' => 'Cinta métrica retráctil', 'v1' => '3 Metros', 'v2' => '5 Metros', 'v3' => '8 Metros'],
                    ['nombre' => 'Nivel de Burbuja', 'desc' => 'Herramienta nivelación', 'v1' => '12 Pulgadas', 'v2' => '24 Pulgadas', 'v3' => 'Magnético'],
                    ['nombre' => 'Cutter Industrial', 'desc' => 'Cuchilla retráctil', 'v1' => 'Plástico', 'v2' => 'Metálico', 'v3' => 'Auto-carga'],
                    ['nombre' => 'Llave Allen (Set)', 'desc' => 'Juego de llaves hexagonales', 'v1' => 'Milimétricas', 'v2' => 'Pulgadas', 'v3' => 'Tipo Navaja'],
                    ['nombre' => 'Gafas de Seguridad', 'desc' => 'Protección ocular ANSI', 'v1' => 'Transparentes', 'v2' => 'Oscuras', 'v3' => 'Antiempañante'],
                    ['nombre' => 'Guantes de Trabajo', 'desc' => 'Protección de manos', 'v1' => 'Látex Rugoso', 'v2' => 'Cuero', 'v3' => 'Anticorte'],
                    ['nombre' => 'Escalera de Tijera', 'desc' => 'Escalera de aluminio', 'v1' => '3 Peldaños', 'v2' => '5 Peldaños', 'v3' => '7 Peldaños'],
                    ['nombre' => 'Multímetro Digital', 'desc' => 'Tester eléctrico', 'v1' => 'Básico', 'v2' => 'Autorango', 'v3' => 'Profesional'],
                ],
            ],
        ];

        foreach ($mantenimientoData as $grupo => $info) {
            foreach ($info['items'] as $item) {
                $this->crearProductoConVariante(
                    $catalogoIds[$info['cat']],
                    null,
                    $catalogoIds['UNI_UD'],
                    $item['nombre'],
                    $item['desc']." (Categoría: $grupo)",
                    2,
                    [
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-A', 'nombre' => $item['nombre'].' - '.$item['v1'], 'atributos' => ['especificacion' => $item['v1']]],
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-B', 'nombre' => $item['nombre'].' - '.$item['v2'], 'atributos' => ['especificacion' => $item['v2']]],
                        ['codigo' => strtoupper(str_replace(['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú'], ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U'], mb_substr($item['nombre'], 0, 3))).'-'.rand(100, 999).'-C', 'nombre' => $item['nombre'].' - '.$item['v3'], 'atributos' => ['especificacion' => $item['v3']]],
                    ]
                );
            }
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $variantes
     */
    private function crearProductoConVariante(
        int $categoriaId,
        ?int $marcaId,
        ?int $unidadBaseId,
        string $nombre,
        string $descripcion,
        int $tipo,
        array $variantes
    ): void {
        $productoId = DB::table('productos')->insertGetId([
            'categoria_id' => $categoriaId,
            'marca_id' => $marcaId,
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'unidad_medida_id' => $unidadBaseId,
            'tipo' => $tipo,
            'estado' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($variantes as $v) {
            DB::table('producto_variantes')->insert([
                'producto_id' => $productoId,
                'codigo' => $v['codigo'],
                'nombre_variante' => $v['nombre'],
                'atributos' => ! empty($v['atributos']) ? json_encode($v['atributos']) : null,
                'unidad_medida_id' => $v['unidad_medida_id'] ?? $unidadBaseId,
                'peso' => $v['peso'] ?? null,
                'volumen' => $v['volumen'] ?? null,
                'estado' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
