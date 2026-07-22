<?php

declare(strict_types=1);

namespace App\Repository\Models\Inventario;

use App\Enums\Inventario\EstadoInventarioFisico;
use App\Enums\Inventario\EstadoLote;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
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

    /**
     * @return Collection<int, Lote>
     */
    public function lotes(): Collection
    {
        $loteIds = $this->extractLoteIdsFromDatosHoja();

        if ($loteIds === []) {
            return collect();
        }

        return Lote::whereIn('id', $loteIds)->get();
    }

    /**
     * @return array<int, int>
     */
    private function extractLoteIdsFromDatosHoja(): array
    {
        $data = $this->datos_hoja;

        if (! is_array($data)) {
            return [];
        }

        if (isset($data[0]) && is_array($data[0]) && array_key_exists('lote_id', $data[0])) {
            $ids = [];
            foreach ($data as $row) {
                if (is_array($row) && isset($row['lote_id']) && is_numeric($row['lote_id'])) {
                    $ids[] = (int) $row['lote_id'];
                }
            }

            return $ids;
        }

        $sheetKey = array_key_first($data['sheets'] ?? []);
        $cellData = is_string($sheetKey) ? ($data['sheets'][$sheetKey]['cellData'] ?? null) : null;

        if (! is_array($cellData)) {
            return [];
        }

        $ids = [];
        foreach ($cellData as $rowIndex => $row) {
            if ($rowIndex === '0') {
                continue;
            }
            if (is_array($row) && isset($row['0']) && is_array($row['0']) && isset($row['0']['v']) && is_numeric($row['0']['v'])) {
                $ids[] = (int) $row['0']['v'];
            }
        }

        return $ids;
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
