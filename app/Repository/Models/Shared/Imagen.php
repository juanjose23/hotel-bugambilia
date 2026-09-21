<?php

namespace App\Repository\Models\Shared;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Imagen extends Model implements AuditableContract
{
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

    /**
     * Get the full, absolute or web-accessible URL for the image.
     */
    public function getUrlCompletaAttribute(): string
    {
        $url = (string) $this->url;
        if (blank($url)) {
            return '';
        }

        if (filter_var($url, FILTER_VALIDATE_URL) !== false || str_starts_with($url, 'data:')) {
            return $url;
        }

        if (str_starts_with($url, '/images/') || str_starts_with($url, 'images/')) {
            return asset(ltrim($url, '/'));
        }

        if (str_starts_with($url, '/storage/') || str_starts_with($url, 'storage/')) {
            return asset(ltrim($url, '/'));
        }

        return Storage::disk('public')->url($url);
    }
}
