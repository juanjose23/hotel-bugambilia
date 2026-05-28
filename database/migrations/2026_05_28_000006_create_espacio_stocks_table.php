<?php

declare(strict_types=1);

use App\Models\Catalogos\ProductoVariante;
use App\Models\Espacios\Espacio;
use App\Models\Inventario\Lote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('espacio_stocks', function (Blueprint $table) {
            $table->id()->comment('Identificador único autoincremental');
            $table->foreignIdFor(Espacio::class, 'espacio_id')
                ->comment('Espacio asociado (ej. carrito de limpieza, área común)')
                ->constrained('espacios')
                ->onDelete('cascade');
            $table->foreignIdFor(ProductoVariante::class, 'producto_variante_id')
                ->comment('Variante del producto en stock')
                ->constrained('producto_variantes')
                ->onDelete('cascade');
            $table->foreignIdFor(Lote::class, 'lote_id')
                ->nullable()
                ->comment('Lote específico opcional')
                ->constrained('inv_lotes')
                ->nullOnDelete();
            $table->decimal('cantidad_ideal', 12, 4)->comment('Cantidad ideal que debería tener el espacio');
            $table->decimal('cantidad_actual', 12, 4)->comment('Cantidad real actual en el espacio');
            $table->string('estado', 20)->nullable()->comment('Estado calculado: completo, faltante, sobrante');
            $table->timestamp('ultima_verificacion')->nullable()->comment('Fecha de última verificación física');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['espacio_id', 'producto_variante_id', 'deleted_at'], 'uq_espacio_stock_variante');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("
                CREATE OR REPLACE FUNCTION actualizar_estado_espacio_stock()
                RETURNS trigger AS $$
                BEGIN
                    IF NEW.cantidad_actual > NEW.cantidad_ideal THEN
                        NEW.estado := 'sobrante';
                    ELSIF NEW.cantidad_actual < NEW.cantidad_ideal THEN
                        NEW.estado := 'faltante';
                    ELSE
                        NEW.estado := 'completo';
                    END IF;
                    RETURN NEW;
                END;
                $$ LANGUAGE plpgsql;
            ");
            DB::statement('
                CREATE TRIGGER trg_espacio_stock_estado
                    BEFORE INSERT OR UPDATE OF cantidad_actual, cantidad_ideal
                    ON espacio_stocks
                    FOR EACH ROW
                    EXECUTE FUNCTION actualizar_estado_espacio_stock();
            ');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS trg_espacio_stock_estado ON espacio_stocks');
            DB::statement('DROP FUNCTION IF EXISTS actualizar_estado_espacio_stock()');
        }
        Schema::dropIfExists('espacio_stocks');
    }
};
