<?php

declare(strict_types=1);

namespace App\Models\Inventario;

use App\Enums\Inventario\EstadoInventarioFisico;
use App\Enums\Inventario\EstadoLote;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class InventarioFisico extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'inv_inventarios_fisicos';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_toma' => 'date:Y-m-d',
        'datos_hoja' => 'array',
        'estado' => EstadoInventarioFisico::class,
    ];

    /**
     * @return BelongsTo<User, $this>
     */
    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creado_por_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (! $model->codigo) {
                $year = now()->year;
                $month = str_pad((string) now()->month, 2, '0', STR_PAD_LEFT);
                $day = str_pad((string) now()->day, 2, '0', STR_PAD_LEFT);
                $datePrefix = "INF-{$year}{$month}{$day}";

                $model->codigo = DB::transaction(function () use ($datePrefix) {
                    $latest = self::withTrashed()
                        ->where('codigo', 'like', "{$datePrefix}-%")
                        ->orderBy('codigo', 'desc')
                        ->lockForUpdate()
                        ->first();

                    $last = 0;
                    if ($latest && preg_match('/-(\d+)$/', $latest->codigo, $matches)) {
                        $last = (int) $matches[1];
                    }

                    return "{$datePrefix}-".str_pad((string) ($last + 1), 3, '0', STR_PAD_LEFT);
                });
            }

            if (! $model->creado_por_id && auth()->check()) {
                $userId = auth()->id();
                if (is_numeric($userId)) {
                    $userIdInt = (int) $userId;
                    /** @var int<0, max> $userIdVal */
                    $userIdVal = $userIdInt >= 0 ? $userIdInt : 0;
                    $model->creado_por_id = $userIdVal;
                }
            }

            // Pre-populate sheet data if not provided
            if (! $model->datos_hoja) {
                $model->datos_hoja = self::generarHojaInicial();
            }
        });
    }

    /**
     * Generates a default pre-populated Univer Sheet JSON configuration
     * with all currently active lotes (not Agotado).
     *
     * @return array<string, mixed>
     */
    public static function generarHojaInicial(): array
    {
        $lotes = Lote::with(['producto', 'ubicacion'])
            ->where('estado', '!=', EstadoLote::Agotado)->get();

        $cellData = [];
        // Row 0: Header style & value
        $cellData['0'] = [
            '0' => ['v' => 'ID Lote', 's' => ['bl' => 1]],
            '1' => ['v' => 'Código Lote', 's' => ['bl' => 1]],
            '2' => ['v' => 'Producto', 's' => ['bl' => 1]],
            '3' => ['v' => 'Ubicación', 's' => ['bl' => 1]],
            '4' => ['v' => 'Stock Sistema', 's' => ['bl' => 1]],
            '5' => ['v' => 'Cantidad Física', 's' => ['bl' => 1]],
            '6' => ['v' => 'Diferencia (Fórm.)', 's' => ['bl' => 1]],
            '7' => ['v' => 'Notas / Observaciones', 's' => ['bl' => 1]],
        ];

        $rowIndex = 1;
        foreach ($lotes as $lote) {
            $rowStr = (string) $rowIndex;
            // Formula is =F{RowIndex+1}-E{RowIndex+1} (Quantity Physical - Stock System)
            $rowNum = $rowIndex + 1;
            $formula = "=F{$rowNum}-E{$rowNum}";

            $cellData[$rowStr] = [
                '0' => ['v' => $lote->id],
                '1' => ['v' => $lote->codigo_lote],
                '2' => ['v' => $lote->producto ? $lote->producto->nombre : 'Sin producto'],
                '3' => ['v' => $lote->ubicacion->nombre ?? 'Sin Ubicación'],
                '4' => ['v' => (float) $lote->cantidad_disponible],
                '5' => ['v' => (float) $lote->cantidad_disponible], // Default physical equal to system initially
                '6' => ['f' => $formula],
                '7' => ['v' => ''],
            ];
            $rowIndex++;
        }

        return [
            'id' => 'workbook-inventario-'.time(),
            'sheetOrder' => ['sheet-1'],
            'sheets' => [
                'sheet-1' => [
                    'id' => 'sheet-1',
                    'name' => 'Físico vs Sistema',
                    'rowCount' => max(100, $rowIndex + 20),
                    'columnCount' => 10,
                    'cellData' => $cellData,
                ],
            ],
        ];
    }
}
