<?php

declare(strict_types=1);

namespace App\Repository\Models\Usuarios;

use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

final class SocialAccount extends Model
{
    use SoftDeletes;

    protected $table = 'social_accounts';

    protected $guarded = ['id'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'provider_data' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
