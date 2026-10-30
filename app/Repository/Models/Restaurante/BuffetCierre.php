<?php

declare(strict_types=1);

namespace App\Repository\Models\Restaurante;

use Illuminate\Database\Eloquent\Model;

final class BuffetCierre extends Model
{
    protected $table = 'restaurante_buffet_cierres';

    protected $guarded = ['id'];
}
