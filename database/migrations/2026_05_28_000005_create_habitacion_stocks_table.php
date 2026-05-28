<?php

declare(strict_types=1);

use App\Models\Catalogos\ProductoVariante;
use App\Models\Habitaciones\Habitacion;
use App\Models\Inventario\Lote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habitacion_stocks', function (Blueprint $table) {
            $table->id()->comment('Identificador único autoincremental');
            $table->foreignIdFor(Habitacion::class, 'habitacion_id')
                ->comment('Habitación asociada')
                ->constrained('habitaciones')
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
            $table->decimal('cantidad_ideal', 12, 4)->comment('Cantidad ideal que debería tener la habitación');
            $table->decimal('cantidad_actual', 12, 4)->comment('Cantidad real actual en la habitación');
            $table->string('estado', 20)->nullable()->comment('Estado calculado: completo, faltante, sobrante');
            $table->timestamp('ultima_verificacion')->nullable()->comment('Fecha de última verificación física');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['habitacion_id', 'producto_variante_id', 'deleted_at'], 'uq_habitacion_stock_variante');
        });

        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("
                CREATE OR REPLACE FUNCTION actualizar_estado_habitacion_stock()
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
                CREATE TRIGGER trg_habitacion_stock_estado
                    BEFORE INSERT OR UPDATE OF cantidad_actual, cantidad_ideal
                    ON habitacion_stocks
                    FOR EACH ROW
                    EXECUTE FUNCTION actualizar_estado_habitacion_stock();
            ');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement('DROP TRIGGER IF EXISTS trg_habitacion_stock_estado ON habitacion_stocks');
            DB::statement('DROP FUNCTION IF EXISTS actualizar_estado_habitacion_stock()');
        }
        Schema::dropIfExists('habitacion_stocks');
    }
};
