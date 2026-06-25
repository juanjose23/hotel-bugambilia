<?php

namespace App\Models\Colaboradores;

use App\Enums\Catalogos\EstadoCatalogo;
use App\Models\Limpieza\Turno;
use App\Models\Personas\Persona;
use App\Models\Shared\Imagen;
use Database\Factories\ColaboradorFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Colaborador extends Model implements AuditableContract
{
    protected $table = 'colaboradores';

    protected $with = [
        'persona',
    ];

    protected $guarded = ['id'];

    protected $casts = [
        'fecha_ingreso' => 'date',
    ];

    /** @use HasFactory<ColaboradorFactory> */
    use Auditable, HasFactory, SoftDeletes;

    protected static function newFactory(): ColaboradorFactory
    {
        return ColaboradorFactory::new();
    }

    /** @return BelongsTo<Persona, $this> */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class);
    }

    /** @return MorphOne<Imagen, $this> */
    public function imagen(): MorphOne
    {
        return $this->morphOne(Imagen::class, 'imagenable');
    }

    /** @return HasOne<ColaboradorDatosMedicos, $this> */
    public function datosMedicos(): HasOne
    {
        return $this->hasOne(ColaboradorDatosMedicos::class);
    }

    /** @return HasMany<ColaboradorContactoEmergencia, $this> */
    public function contactosEmergencia(): HasMany
    {
        return $this->hasMany(ColaboradorContactoEmergencia::class);
    }

    /** @return HasMany<ColaboradorSalario, $this> */
    public function salarios(): HasMany
    {
        return $this->hasMany(ColaboradorSalario::class);
    }

    /** @return HasMany<ColaboradorCargoHistorial, $this> */
    public function cargosHistorial(): HasMany
    {
        return $this->hasMany(ColaboradorCargoHistorial::class);
    }

    public function cargoActual(): ?ColaboradorCargoHistorial
    {
        /** @var ColaboradorCargoHistorial|null $cargo */
        $cargo = $this->cargosHistorial()
            ->with(['cargo', 'departamento'])
            ->where('estado', EstadoCatalogo::Activo->value)
            ->latest('fecha_inicio')
            ->first();

        return $cargo;
    }

    /** @return HasMany<ColaboradorDocumento, $this> */
    public function documentos(): HasMany
    {
        return $this->hasMany(ColaboradorDocumento::class);
    }

    /** @return HasMany<Turno, $this> */
    public function turnosLider(): HasMany
    {
        return $this->hasMany(Turno::class, 'lider_id');
    }

    /** @return HasMany<Turno, $this> */
    public function turnosApoyo(): HasMany
    {
        return $this->hasMany(Turno::class, 'apoyo_id');
    }

    /** @return Collection<int, Turno> */
    public function getTurnosAttribute(): Collection
    {
        return Turno::where('lider_id', $this->id)
            ->orWhere('apoyo_id', $this->id)
            ->get();
    }
}
