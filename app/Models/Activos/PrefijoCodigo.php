<?php

declare(strict_types=1);

namespace App\Models\Activos;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable as AuditableTrait;
use OwenIt\Auditing\Contracts\Auditable;

class PrefijoCodigo extends Model implements Auditable
{
    /** @use HasFactory<Factory<static>> */
    use AuditableTrait, HasFactory;

    protected $table = 'inv_prefijos_codigo';

    protected $guarded = ['id'];
}
