<?php

namespace App\Models\General;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Imagen extends Model
{
    protected $table = 'imagenes';

    protected $guarded = [];

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
