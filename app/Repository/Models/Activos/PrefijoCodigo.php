<?php

declare(strict_types=1);

namespace App\Repository\Models\Activos;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class PrefijoCodigo extends Model implements Auditable
{
    use AuditableTrait;

    protected $table = 'inv_prefijos_codigo';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'ultimo_numero' => 'integer',
        ];
    }
}
