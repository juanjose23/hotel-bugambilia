<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Imagen extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;

    protected $table = 'imagenes';

    protected $guarded = ['id'];

    /**
     * Get the parent imageable model.
     *
     * @return MorphTo<Model, $this>
     */
    public function imagenable(): MorphTo
    {
        return $this->morphTo();
    }
}
