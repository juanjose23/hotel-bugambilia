<?php

namespace App\Models\Catalogos;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Auditable;
class Catalogo extends Model implements AuditableContract
{
    /** @use HasFactory<\Database\Factories\CatalogoFactory> */
    use HasFactory, Auditable;
    protected $table = 'catalogos';
    protected $guarded = [];

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<CatalogoTipo, $this> */
    public function catalogoTipo(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CatalogoTipo::class, 'catalogo_tipo_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<self, $this> */
    public function padre(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(self::class, 'padre_id');
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<self, $this> */
    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(self::class, 'padre_id');
    }
}