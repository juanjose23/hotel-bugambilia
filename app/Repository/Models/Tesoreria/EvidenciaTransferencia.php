<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

final class EvidenciaTransferencia extends Model implements AuditableContract
{
    use Auditable;

    protected $table = 'tes_evidencias';

    protected $guarded = ['id'];

    protected $casts = [];

    protected static function booted(): void
    {
        self::updating(fn () => throw new \DomainException('Este registro de auditoría es inmutable.'));
        self::deleting(fn () => throw new \DomainException('Este registro de auditoría se conserva.'));
    }
}
