<?php

namespace App\Repository\Models\Shared;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Imagen extends Model implements AuditableContract
{
    use Auditable, SoftDeletes;
    use Auditable, SoftDeletes;

    protected $table = 'imagenes';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
        ];
    }

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
