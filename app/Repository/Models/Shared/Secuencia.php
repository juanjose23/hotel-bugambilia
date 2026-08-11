<?php

declare(strict_types=1);

namespace App\Repository\Models\Shared;

use Illuminate\Database\Eloquent\Model;

final class Secuencia extends Model
{
    protected $table = 'secuencias';

    protected $fillable = [
        'tipo',
        'anio',
        'ultimo_numero',
    ];

    protected $casts = [
        'anio' => 'integer',
        'ultimo_numero' => 'integer',
    ];
}
