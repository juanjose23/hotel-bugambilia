<?php

namespace App\Repository\Models\Monedas;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property Carbon $fecha
 * @property int $moneda_origen_id
 * @property int $moneda_destino_id
 * @property float $tasa
 * @property-read Moneda $monedaOrigen
 * @property-read Moneda $monedaDestino
 */
class TasaCambio extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'tasas_cambio';

    protected $guarded = ['id'];

    protected $casts = [
        'fecha' => 'date:Y-m-d',
        'tasa' => 'decimal:4',
        'es_fija' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function ($tasaCambio): void {
            $tasaCambio->clearCache();
        });

        static::deleted(function ($tasaCambio): void {
            $tasaCambio->clearCache();
        });
    }

    public function clearCache(): void
    {
        $origenCodigo = $this->monedaOrigen->codigo;
        $destinoCodigo = $this->monedaDestino->codigo;
        $fechaString = $this->fecha->toDateString();

        if ($origenCodigo && $destinoCodigo && $fechaString) {
            Cache::forget("tasa_cambio_{$fechaString}_{$origenCodigo}_{$destinoCodigo}");
        }
    }

    /** @return BelongsTo<Moneda, $this> */
    public function monedaOrigen(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_origen_id');
    }

    /** @return BelongsTo<Moneda, $this> */
    public function monedaDestino(): BelongsTo
    {
        return $this->belongsTo(Moneda::class, 'moneda_destino_id');
    }

    public static function obtenerTasa(\DateTimeInterface|string $fecha, string $origenCodigo = 'USD', string $destinoCodigo = 'NIO'): float
    {
        $fechaString = $fecha instanceof \DateTimeInterface ? $fecha->format('Y-m-d') : $fecha;

        return Cache::remember(
            "tasa_cambio_{$fechaString}_{$origenCodigo}_{$destinoCodigo}",
            now()->addHours(12),
            function () use ($fechaString, $origenCodigo, $destinoCodigo) {
                $origen = Moneda::where('codigo', $origenCodigo)->first();
                $destino = Moneda::where('codigo', $destinoCodigo)->first();

                if (! $origen || ! $destino) {
                    return 1.0;
                }

                // 1. Buscar tasa exacta para la fecha
                $exacta = self::where('fecha', $fechaString)
                    ->where('moneda_origen_id', $origen->id)
                    ->where('moneda_destino_id', $destino->id)
                    ->first();

                if ($exacta) {
                    return (float) $exacta->tasa;
                }

                // 2. Buscar tasa fija de respaldo (es_fija = true)
                $fija = self::where('moneda_origen_id', $origen->id)
                    ->where('moneda_destino_id', $destino->id)
                    ->where('es_fija', true)
                    ->orderBy('fecha', 'desc')
                    ->first();

                if ($fija) {
                    return (float) $fija->tasa;
                }

                // 3. Fallback a la última registrada
                $ultima = self::where('moneda_origen_id', $origen->id)
                    ->where('moneda_destino_id', $destino->id)
                    ->orderBy('fecha', 'desc')
                    ->first();

                if ($ultima) {
                    return (float) $ultima->tasa;
                }

                return 1.0;
            }
        );
    }
}
