<?php

namespace App\Models\Audits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $usuario_id
 * @property string $tipo_reporte
 * @property array<string, mixed>|null $parametros
 * @property string|null $ruta_archivo
 * @property int $conteo_descargas
 * @property Carbon|null $ultima_descarga_en
 */
class AuditoriaReporte extends Model
{
    protected $table = 'auditoria_reportes';

    protected $fillable = [
        'usuario_id',
        'tipo_reporte',
        'parametros',
        'ruta_archivo',
        'conteo_descargas',
        'ultima_descarga_en',
    ];

    protected $casts = [
        'parametros' => 'array',
        'ultima_descarga_en' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
