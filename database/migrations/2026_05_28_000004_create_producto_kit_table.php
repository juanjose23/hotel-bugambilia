<?php

declare(strict_types=1);

use App\Models\Catalogos\Producto;
use App\Models\Catalogos\ProductoVariante;
use App\Models\Inventario\Lote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('producto_kit', function (Blueprint $table) {
            $table->id()->comment('Identificador único autoincremental');
            $table->foreignIdFor(Producto::class, 'producto_padre_id')
                ->comment('Producto padre que representa el pack/kit')
                ->constrained('productos')
                ->onDelete('cascade');
            $table->foreignIdFor(ProductoVariante::class, 'producto_variante_id')
                ->comment('Variante del producto hijo incluido en el kit')
                ->constrained('producto_variantes')
                ->onDelete('cascade');
            $table->decimal('cantidad', 12, 4)->comment('Cantidad de unidades de esta variante en el kit');
            $table->foreignIdFor(Lote::class, 'lote_id')
                ->nullable()
                ->comment('Lote específico opcional (para activos individualizables)')
                ->constrained('inv_lotes')
                ->nullOnDelete();
            $table->string('talla', 20)->nullable()->comment('Talla opcional (ej. S, M, L, XL para blancos)');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['producto_padre_id', 'producto_variante_id', 'deleted_at'], 'uq_producto_kit_padre_variante');
        });

        $driver = DB::connection()->getDriverName();
        if ($driver === 'pgsql') {
            DB::statement('ALTER TABLE producto_kit DROP CONSTRAINT IF EXISTS uq_producto_kit_padre_variante');
            DB::statement('CREATE UNIQUE INDEX uq_producto_kit_padre_variante ON producto_kit (producto_padre_id, producto_variante_id) WHERE deleted_at IS NULL');
        } elseif ($driver === 'sqlite') {
            DB::statement('DROP INDEX IF EXISTS uq_producto_kit_padre_variante');
            DB::statement('CREATE UNIQUE INDEX uq_producto_kit_padre_variante ON producto_kit (producto_padre_id, producto_variante_id) WHERE deleted_at IS NULL');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('producto_kit');
    }
};
