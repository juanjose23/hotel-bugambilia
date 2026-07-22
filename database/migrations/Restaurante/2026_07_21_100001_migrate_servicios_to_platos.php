<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $restaurantCategoryIds = DB::table('catalogos')
            ->whereIn('codigo', ['REST_ENTRADAS', 'REST_PLATOS', 'REST_POSTRES', 'REST_BEBIDAS', 'RESTAURANTE'])
            ->pluck('id')
            ->toArray();

        $servicios = DB::table('servicios')
            ->whereIn('categoria_id', $restaurantCategoryIds)
            ->whereNull('deleted_at')
            ->get();

        foreach ($servicios as $servicio) {
            $productoReceta = DB::table('productos')
                ->where('nombre', 'like', 'Receta: '.$servicio->nombre.'%')
                ->whereNull('deleted_at')
                ->first();

            DB::table('platos')->insert([
                'codigo' => $servicio->codigo,
                'nombre' => $servicio->nombre,
                'categoria_id' => $servicio->categoria_id,
                'producto_receta_id' => $productoReceta?->id,
                'descripcion' => $servicio->descripcion,
                'web' => $servicio->web ?? false,
                'estado' => $servicio->estado,
                'tiempo_preparacion' => null,
                'created_at' => $servicio->created_at,
                'updated_at' => $servicio->updated_at,
            ]);
        }

        // Migrate polymorphic precios
        DB::table('precios')
            ->where('priceable_type', 'App\\Repository\\Models\\Servicios\\Servicio')
            ->whereIn('priceable_id', $servicios->pluck('id')->toArray())
            ->update(['priceable_type' => 'App\\Repository\\Models\\Restaurante\\Plato']);

        // Migrate polymorphic imagenes
        DB::table('imagenes')
            ->where('imagenable_type', 'App\\Repository\\Models\\Servicios\\Servicio')
            ->whereIn('imagenable_id', $servicios->pluck('id')->toArray())
            ->update(['imagenable_type' => 'App\\Repository\\Models\\Restaurante\\Plato']);

        // Migrate polymorphic politicas
        if (Schema::hasTable('politicaable')) {
            DB::table('politicaable')
                ->where('politicaable_type', 'App\\Repository\\Models\\Servicios\\Servicio')
                ->whereIn('politicaable_id', $servicios->pluck('id')->toArray())
                ->update(['politicaable_type' => 'App\\Repository\\Models\\Restaurante\\Plato']);
        }
    }

    public function down(): void
    {
        // Reverse polymorphic migrations
        DB::table('precios')
            ->where('priceable_type', 'App\\Repository\\Models\\Restaurante\\Plato')
            ->update(['priceable_type' => 'App\\Repository\\Models\\Servicios\\Servicio']);

        DB::table('imagenes')
            ->where('imagenable_type', 'App\\Repository\\Models\\Restaurante\\Plato')
            ->update(['imagenable_type' => 'App\\Repository\\Models\\Servicios\\Servicio']);

        if (Schema::hasTable('politicaable')) {
            DB::table('politicaable')
                ->where('politicaable_type', 'App\\Repository\\Models\\Restaurante\\Plato')
                ->update(['politicaable_type' => 'App\\Repository\\Models\\Servicios\\Servicio']);
        }

        DB::table('platos')->truncate();
    }
};
