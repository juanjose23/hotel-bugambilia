<?php

declare(strict_types=1);

namespace App\Repository\Models\Audits;

use App\Enums\Shared\EstadoEjecucionJob;
use App\Enums\Shared\TipoJob;
use App\Repository\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $usuario_id
 * @property TipoJob $tipo_job
 * @property string $nombre_job
 * @property string $tipo_ejecucion
 * @property EstadoEjecucionJob $estado
 * @property string|null $mensaje
 * @property Carbon|null $ejecutado_en
 * @property Carbon|null $completado_en
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class AuditoriaJob extends Model
{
    protected $table = 'auditoria_jobs';

    protected $guarded = ['id'];

    protected $casts = [
        'tipo_job' => TipoJob::class,
        'estado' => EstadoEjecucionJob::class,
        'ejecutado_en' => 'datetime',
        'completado_en' => 'datetime',
    ];

    /** @return BelongsTo<User, $this> */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
