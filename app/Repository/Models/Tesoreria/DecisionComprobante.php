<?php

declare(strict_types=1);

namespace App\Repository\Models\Tesoreria;

use Illuminate\Database\Eloquent\Model;

final class DecisionComprobante extends Model
{
    protected $table = 'tes_decisiones_comprobante';

    protected $guarded = ['id'];

    protected static function booted(): void
    {
        self::updating(fn () => throw new \DomainException('La decisión se conserva.'));
        self::deleting(fn () => throw new \DomainException('La decisión se conserva.'));
    }
}
