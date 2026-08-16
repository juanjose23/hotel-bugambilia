<?php

declare(strict_types=1);

namespace App\Repository\Models\Cuentas;

use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Catálogo de cargos fiscales y comerciales aplicables a cuentas.
 *
 * @property int $id
 * @property string $codigo
 * @property string $nombre
 * @property TipoCargo $tipo
 * @property ModoCargo $modo_calculo
 * @property float $valor
 * @property BaseCalculo $base_calculo
 * @property int $orden
 * @property bool $obligatorio
 * @property bool $permite_modificacion
 * @property array<string>|null $areas
 * @property string|null $fecha_inicio
 * @property string|null $fecha_fin
 * @property int $estado
 */
final class CargoFacturacion extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'cargos_facturacion';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tipo' => TipoCargo::class,
            'modo_calculo' => ModoCargo::class,
            'base_calculo' => BaseCalculo::class,
            'valor' => 'decimal:4',
            'orden' => 'integer',
            'obligatorio' => 'boolean',
            'permite_modificacion' => 'boolean',
            'areas' => 'array',
            'estado' => 'integer',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeActivos(Builder $query): Builder
    {
        return $query->where('estado', 1)
            ->where(function (Builder $q): void {
                $q->whereNull('fecha_inicio')
                    ->orWhere('fecha_inicio', '<=', now()->toDateString());
            })
            ->where(function (Builder $q): void {
                $q->whereNull('fecha_fin')
                    ->orWhere('fecha_fin', '>=', now()->toDateString());
            });
    }
}
