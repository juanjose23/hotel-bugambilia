<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class OperacionTesoreria extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_operaciones';

    protected $guarded = ['id'];

    protected $casts = ['resultado' => 'array'];
}
