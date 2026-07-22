<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MÓDULO: INVENTARIOS FÍSICOS (Physical Stock Counts)
     *
     * Registro de las tomas periódicas de inventario físico para auditorías
     * y corrección de discrepancias entre el stock lógico (base de datos)
     * y el stock real (conteo físico).
     *
     * ESTADOS:
     *   borrador   - Editable. El auditor puede modificar datos_hoja.
     *   procesado  - Bloqueado. Los ajustes ya fueron aplicados a inv_lotes e inv_stock.
     *
     * PROCESO ProcesarInventarioFisico:
     *   Lee datos_hoja (JSON: [{lote_id, cantidad_contada}])
     *   → Calcula diferencias vs lote.cantidad_disponible
     *   → Registra AJUSTE_ENTRADA o AJUSTE_SALIDA en inv_movimientos
     *   → Marca estado = 'procesado' (irreversible)
     */
    public function up(): void
    {
        Schema::create('inv_inventarios_fisicos', function (Blueprint $table) {
            $table->comment('Tomas de inventario físico y auditorías periódicas. Estado borrador=editable, procesado=bloqueado con ajustes aplicados.');
            $table->id()->comment('Identificador único autoincremental de la toma de inventario físico');
            $table->string('codigo', 100)->unique()->comment('Folio único de control (ej: INV-FIS-20260520). Generado sugerido por Filament, editable por el auditor.');
            $table->date('fecha_toma')->comment('Fecha en la cual se realizó el conteo físico de los productos');
            $table->string('estado', 40)->default('borrador')->comment('Estado de la auditoría: borrador (editable) o procesado (bloqueado, ajustes aplicados)');
            $table->foreignId('creado_por_id')
                ->nullable()
                ->comment('Auditor o responsable físico de la toma. FK → users, nullOnDelete.')
                ->constrained('users')
                ->nullOnDelete();
            $table->text('observaciones')->nullable()->comment('Notas u observaciones surgidas durante el conteo físico');
            $table->longText('datos_hoja')
                ->nullable()
                ->comment('JSON con los conteos físicos capturados: [{lote_id: int, cantidad_contada: float}]. Procesado por ProcesarInventarioFisico.');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['estado']);
            $table->index('creado_por_id');
        });
    }

    public function down(): void
    {
        Schema::table('inv_inventarios_fisicos', fn (Blueprint $t) => $t->dropIndex(['creado_por_id']));
        Schema::dropIfExists('inv_inventarios_fisicos');
    }
};
