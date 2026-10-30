<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('estancias', fn (Blueprint $t) => $t->unique(['id', 'reserva_id'], 'estancias_id_reserva_unique'));
        Schema::table('reserva_detalles', fn (Blueprint $t) => $t->unique(['id', 'reserva_id'], 'reserva_detalles_id_reserva_unique'));
        Schema::table('ventas', fn (Blueprint $t) => $t->unique(['id', 'cuenta_id'], 'ventas_id_cuenta_unique'));
        Schema::table('cuentas', fn (Blueprint $t) => $t->foreign(['estancia_id', 'reserva_id'], 'cuentas_estancia_reserva_fk')->references(['id', 'reserva_id'])->on('estancias')->restrictOnDelete());
        Schema::table('estancias', fn (Blueprint $t) => $t->foreign(['reserva_detalle_id', 'reserva_id'], 'estancias_detalle_reserva_fk')->references(['id', 'reserva_id'])->on('reserva_detalles')->restrictOnDelete());
        Schema::table('pagos_cuenta', fn (Blueprint $t) => $t->foreign(['venta_id', 'cuenta_id'], 'pagos_cuenta_venta_cuenta_fk')->references(['id', 'cuenta_id'])->on('ventas')->restrictOnDelete());
        Schema::table('facturas', fn (Blueprint $t) => $t->foreign(['venta_id', 'cuenta_id'], 'facturas_venta_cuenta_fk')->references(['id', 'cuenta_id'])->on('ventas')->restrictOnDelete());
    }

    public function down(): void
    {
        Schema::table('facturas', fn (Blueprint $t) => $t->dropForeign('facturas_venta_cuenta_fk'));
        Schema::table('pagos_cuenta', fn (Blueprint $t) => $t->dropForeign('pagos_cuenta_venta_cuenta_fk'));
        Schema::table('estancias', fn (Blueprint $t) => $t->dropForeign('estancias_detalle_reserva_fk'));
        Schema::table('cuentas', fn (Blueprint $t) => $t->dropForeign('cuentas_estancia_reserva_fk'));
        Schema::table('ventas', fn (Blueprint $t) => $t->dropUnique('ventas_id_cuenta_unique'));
        Schema::table('reserva_detalles', fn (Blueprint $t) => $t->dropUnique('reserva_detalles_id_reserva_unique'));
        Schema::table('estancias', fn (Blueprint $t) => $t->dropUnique('estancias_id_reserva_unique'));
    }
};
