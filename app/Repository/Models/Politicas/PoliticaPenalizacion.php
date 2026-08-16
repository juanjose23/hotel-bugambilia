<?php

declare(strict_types=1);

namespace App\Repository\Models\Politicas;

use App\Enums\Politicas\UnidadAnticipacion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property int $id
 * @property int $politica_id
 * @property int|null $min_unidades
 * @property int|null $max_unidades
 * @property UnidadAnticipacion $unidad
 * @property float $porcentaje
 * @property bool $aplica_no_show
 * @property int $orden
 * @property Politica|null $politica
 */
class PoliticaPenalizacion extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'politica_penalizaciones';

    protected $guarded = ['id'];

    protected $casts = [
        'unidad' => UnidadAnticipacion::class,
        'porcentaje' => 'float',
        'aplica_no_show' => 'boolean',
        'min_unidades' => 'integer',
        'max_unidades' => 'integer',
        'orden' => 'integer',
    ];

    /**
     * @return BelongsTo<Politica, $this>
     */
    public function politica(): BelongsTo
    {
        return $this->belongsTo(Politica::class, 'politica_id');
    }
}
