<?php

namespace App\Models\Compras;

use App\Models\Catalogos\Catalogo;
use App\Models\Personas\Persona;
use Database\Factories\Compras\ProveedorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Proveedor extends Model implements AuditableContract
{
    /** @use HasFactory<ProveedorFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected $table = 'proveedores';

    protected $with = [
        'persona',
    ];

    protected $fillable = [
        'codigo',
        'persona_id',
        'tipo_proveedor_id',
        'direccion_fiscal',
        'notas',
        'estado',
    ];

    protected $casts = [
        'estado' => 'integer',
    ];

    /** @return BelongsTo<Persona, $this> */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    /** @return BelongsTo<Catalogo, $this> */
    public function tipoProveedor(): BelongsTo
    {
        return $this->belongsTo(Catalogo::class, 'tipo_proveedor_id');
    }

    /** @return HasOne<ProveedorContacto, $this> */
    public function contactoPrincipal(): HasOne
    {
        return $this->hasOne(ProveedorContacto::class, 'proveedor_id')
            ->where('principal', true);
    }

    /** @return HasMany<ProveedorContacto, $this> */
    public function contactos(): HasMany
    {
        return $this->hasMany(ProveedorContacto::class, 'proveedor_id');
    }
}
