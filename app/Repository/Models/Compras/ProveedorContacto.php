<?php

declare(strict_types=1);

namespace App\Repository\Models\Compras;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class ProveedorContacto extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'proveedor_contactos';

    protected $guarded = ['id'];

    protected $casts = [
        'principal' => 'boolean',
    ];

    /** @return BelongsTo<Proveedor, $this> */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    protected static function booted(): void
    {
        self::saved(function (self $contacto) {
            if ($contacto->principal) {
                static::where('proveedor_id', $contacto->proveedor_id)
                    ->where('id', '!=', $contacto->id)
                    ->update(['principal' => false]);
            }
        });
    }
}
