<?php

declare(strict_types=1);

namespace Database\Seeders\Productos;

use Database\Seeders\Productos\Base\CreaProductosSeeder;

/**
 * Productos de Amenidades de Habitación (CAT_PRO_AMEN_HABIT).
 */
final class ProductoAmenidadHabitacionSeeder extends CreaProductosSeeder
{
    protected function seed(): void
    {
        $this->crearProductoConVariante(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
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
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
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
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Kit de costura',
            'Aguja e hilos básicos',
            2,
            [
                ['codigo' => 'KC-001-BASICO', 'nombre' => 'Kit costura básico 10 piezas', 'atributos' => ['nivel' => 'básico', 'piezas' => '10']],
                ['codigo' => 'KC-001-COMPLETO', 'nombre' => 'Kit costura completo 20 piezas', 'atributos' => ['nivel' => 'completo', 'piezas' => '20']],
                ['codigo' => 'KC-001-PREMIUM', 'nombre' => 'Kit costura premium 30 piezas', 'atributos' => ['nivel' => 'premium', 'piezas' => '30']],
            ]
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Bolsa de ropa de habitación',
            'Bolsa plástica para ropa sucia',
            2,
            'BOL-ROPA-STD',
            ['color' => 'transparente', 'capacidad' => '20L']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Bolsas de basura habitación',
            'Bolsas pequeñas para botes de baño',
            2,
            'BOL-BAS-15',
            ['capacidad' => '15L', 'calibre' => 'delgado']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Tarjeta de bienvenida',
            'Tarjeta personalizada del hotel',
            2,
            'TARJ-BVN-STD',
            ['tipo' => 'bienvenida']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Sobre de carpeta',
            'Sobre manila para documentación',
            2,
            'SOB-CARP-STD',
            ['tamaño' => 'carta']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Plano turístico de la ciudad',
            'Mapa con puntos de interés',
            2,
            'MAP-TUR-STD',
            ['atracciones' => 'locales']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Guía de servicios del hotel',
            'Folleto con horarios y servicios',
            2,
            'GUI-HOT-STD',
            ['idioma' => 'español/inglés']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Lámpara de bolsillo',
            'Mini lámpara LED de emergencia',
            2,
            'LAM-BOL-LED',
            ['fuente' => 'LED']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Candado para maleta',
            'Candado de combinación TSA',
            2,
            'CAN-MAL-TSA',
            ['tipo' => 'combinación TSA']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Kit de limpieza de zapatos',
            'Esponja y crema para zapatos',
            2,
            'KZ-001-STD',
            ['piezas' => '3']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Papel carta para bandeja',
            'Resma de papel para escritorio',
            2,
            'PAP-CARTA-500',
            ['formato' => 'carta', 'hojas' => '500']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Bolsa ecológica de cortesía',
            'Bolsa de tela reutilizable con logo',
            2,
            'BOL-ECO-TELA',
            ['material' => 'algodón']
        );

        $this->crearProductoSimple(
            (int) $this->catalogoIds['CAT_PRO_AMEN_HABIT'],
            null,
            (int) $this->catalogoIds['UNI_UD'],
            'Set de escritura para escritorio',
            'Porta bolígrafos + libreta',
            2,
            'SET-ESC-STD',
            ['piezas' => '3']
        );
    }
}
