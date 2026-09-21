<?php

declare(strict_types=1);

namespace Database\Seeders\Productos\Base;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Base compartida para los seeders de productos por categoría.
 *
 * Provee el catálogo de IDs (código → id) y los helpers de creación de
 * productos con variantes y productos simples (una sola variante "Estándar").
 */
abstract class CreaProductosSeeder extends Seeder
{
    /** @var array<string, int> */
    protected array $catalogoIds = [];

    final public function run(): void
    {
        /** @var array<string, int> $catalogoIds */
        $catalogoIds = DB::table('catalogos')->pluck('id', 'codigo')->all();

        $this->catalogoIds = $catalogoIds;

        $this->seed();
    }

    abstract protected function seed(): void;

    /**
     * Crea o actualiza un producto con sus variantes (migrado 1:1 del seeder
     * original; idempotente por nombre + categoría y por código/nombre de variante).
     *
     * @param  array<int, array<string, mixed>>  $variantes
     */
    protected function crearProductoConVariante(
        int $categoriaId,
        ?int $marcaId,
        ?int $unidadBaseId,
        string $nombre,
        string $descripcion,
        int $tipo,
        array $variantes
    ): void {
        $producto = DB::table('productos')
            ->where('nombre', $nombre)
            ->where('categoria_id', $categoriaId)
            ->first();

        if ($producto) {
            $productoId = $producto->id;
            DB::table('productos')->where('id', $productoId)->update([
                'marca_id' => $marcaId,
                'descripcion' => $descripcion,
                'unidad_medida_id' => $unidadBaseId,
                'tipo' => $tipo,
                'estado' => 1,
                'updated_at' => now(),
            ]);
        } else {
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
        }

        foreach ($variantes as $v) {
            $varianteExistente = DB::table('producto_variantes')
                ->where('producto_id', $productoId)
                ->where(function ($query) use ($v) {
                    $query->where('codigo', $v['codigo'])
                        ->orWhere('nombre_variante', $v['nombre']);
                })
                ->first();

            if ($varianteExistente) {
                DB::table('producto_variantes')->where('id', $varianteExistente->id)->update([
                    'codigo' => $v['codigo'],
                    'nombre_variante' => $v['nombre'],
                    'atributos' => ! empty($v['atributos']) ? json_encode($v['atributos']) : null,
                    'unidad_medida_id' => $v['unidad_medida_id'] ?? $unidadBaseId,
                    'peso' => $v['peso'] ?? null,
                    'volumen' => $v['volumen'] ?? null,
                    'estado' => 1,
                    'updated_at' => now(),
                ]);
            } else {
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

    /**
     * Crea un producto sin variantes (una sola variante "Estándar").
     *
     * @param  array<string, mixed>  $atributos
     */
    protected function crearProductoSimple(
        int $categoriaId,
        ?int $marcaId,
        ?int $unidadBaseId,
        string $nombre,
        string $descripcion,
        int $tipo,
        string $codigoVariante,
        array $atributos = [],
        ?int $peso = null,
        ?int $volumen = null
    ): void {
        $this->crearProductoConVariante(
            $categoriaId,
            $marcaId,
            $unidadBaseId,
            $nombre,
            $descripcion,
            $tipo,
            [
                [
                    'codigo' => $codigoVariante,
                    'nombre' => $nombre.' (Estándar)',
                    'atributos' => $atributos,
                    'peso' => $peso,
                    'volumen' => $volumen,
                ],
            ]
        );
    }
}
