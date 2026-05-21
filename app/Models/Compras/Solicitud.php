<?php

namespace App\Models\Compras;

use App\Enums\Compras\EstadoSolicitud;
use App\Models\Catalogos\Catalogo;
use App\Models\Colaboradores\Colaborador;
use App\Traits\HasStatusHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * @property bool|null $ordenes_compra_exists
 */
class Solicitud extends Model implements AuditableContract
{
    use Auditable, HasStatusHistory, SoftDeletes;

    protected $table = 'solicitudes_compra';

    protected $with = [
        'colaborador',
        'departamentoSolicitante',
        'items',
    ];

    protected $fillable = [
        'codigo',
        'colaborador_id',
        'departamento_solicitante_id',
        'fecha_solicitud',
        'fecha_necesita',
        'motivo',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_solicitud' => 'date',
        'fecha_necesita' => 'date',
        'estado' => EstadoSolicitud::class,
    ];

    /** @return BelongsTo<Colaborador, $this> */
    public function colaborador(): BelongsTo
    {
        return $this->belongsTo(Colaborador::class, 'colaborador_id');
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function departamentoSolicitante(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'departamento_solicitante_id');
    }

    /** @return HasMany<SolicitudItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(SolicitudItem::class, 'solicitud_id');
    }

    /** @return HasMany<Cotizacion, $this> */
    public function cotizaciones(): HasMany
    {
        return $this->hasMany(Cotizacion::class, 'solicitud_id');
    }

    /** @return HasMany<OrdenCompra, $this> */
    public function ordenesCompra(): HasMany
    {
        return $this->hasMany(OrdenCompra::class, 'solicitud_id');
    }
}
