<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('habitacion_vistas', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('habitacion_id')->constrained('habitaciones')->cascadeOnDelete();
            $table->foreignId('catalogo_id')->constrained('catalogos')->restrictOnDelete();
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
            $table->unique(['habitacion_id', 'catalogo_id']);
            $table->index('catalogo_id');
        });

        DB::table('detalles_habitacion')->orderBy('id')->each(function (object $detalle): void {
            if ($detalle->vistas === null) {
                return;
            }
            $vistas = json_decode((string) $detalle->vistas, true, 512, JSON_THROW_ON_ERROR);
            if (! is_array($vistas) || ! array_is_list($vistas)) {
                throw new RuntimeException('Las vistas de habitacion requieren una lista revisada.');
            }
            foreach ($vistas as $orden => $valor) {
                $catalogoId = $this->resolverVista($valor);
                DB::table('habitacion_vistas')->updateOrInsert(
                    ['habitacion_id' => $detalle->habitacion_id, 'catalogo_id' => $catalogoId],
                    ['orden' => $orden, 'created_at' => now(), 'updated_at' => now()],
                );
            }
        });

        // Archivo de entrada para trazabilidad; la aplicacion utiliza la relacion nueva.
        Schema::table('detalles_habitacion', fn (Blueprint $table) => $table->renameColumn('vistas', 'vistas_legacy'));
    }

    private function resolverVista(mixed $valor): int
    {
        $tipos = DB::table('catalogo_tipos')->whereRaw('upper(codigo) = ?', ['TIPO_VISTA'])->pluck('id');
        if ($tipos->count() !== 1) {
            throw new RuntimeException('Debe existir un unico catalogo de tipos de vista.');
        }
        $tipo = $tipos->first();
        if (! is_numeric($tipo)) {
            throw new RuntimeException('Tipo de vista sin identificador valido.');
        }
        $tipoId = (int) $tipo;
        if (is_int($valor) || (is_string($valor) && ctype_digit($valor))) {
            $id = (int) $valor;
            if (! DB::table('catalogos')->where('id', $id)->where('catalogo_tipo_id', $tipoId)->exists()) {
                throw new RuntimeException('Un identificador de vista no pertenece al catalogo esperado.');
            }

            return $id;
        }
        if (! is_string($valor) || trim($valor) === '') {
            throw new RuntimeException('Vista sin nombre: revisar antes de migrar.');
        }
        $ids = DB::table('catalogos')->where('catalogo_tipo_id', $tipoId)->where('nombre', $valor)->pluck('id');
        if ($ids->count() > 1) {
            throw new RuntimeException('Nombre de vista ambiguo en el catalogo.');
        }
        if ($ids->isNotEmpty()) {
            $id = $ids->first();
            if (! is_numeric($id)) {
                throw new RuntimeException('Vista sin identificador valido.');
            }

            return (int) $id;
        }

        // Conservar la etiqueta exacta; no adivinar equivalencias entre vistas diferentes.
        return (int) DB::table('catalogos')->insertGetId([
            'catalogo_tipo_id' => $tipoId,
            'codigo' => match ($valor) {
                'Vista Panorámica' => 'VISTA_PANORAMICA', 'Vista a la Terraza' => 'VISTA_TERRAZA',
                'Vista al Mar / Montaña' => 'VISTA_MAR_MONTANA', 'Vista 360°' => 'VISTA_360',
                'Vista al Jardín Tropical' => 'VISTA_JARDIN_TROPICAL', 'Vista a la Naturaleza' => 'VISTA_NATURALEZA',
                default => 'VISTA_'.strtoupper(substr(hash('sha256', $valor), 0, 16)),
            },
            'nombre' => $valor, 'estado' => 1, 'orden' => 0,
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('detalles_habitacion', fn (Blueprint $table) => $table->renameColumn('vistas_legacy', 'vistas'));
        DB::table('detalles_habitacion')->orderBy('id')->each(function (object $detalle): void {
            $nombres = DB::table('habitacion_vistas as hv')->join('catalogos as c', 'c.id', '=', 'hv.catalogo_id')
                ->where('hv.habitacion_id', $detalle->habitacion_id)->orderBy('hv.orden')->pluck('c.nombre')->all();
            DB::table('detalles_habitacion')->where('id', $detalle->id)->update(['vistas' => json_encode($nombres, JSON_THROW_ON_ERROR)]);
        });
        Schema::dropIfExists('habitacion_vistas');
    }
};
