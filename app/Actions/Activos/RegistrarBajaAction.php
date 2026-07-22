<?php

declare(strict_types=1);

namespace App\Actions\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\TipoBaja;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoBaja;
use App\Repository\Persistencia\Activos\ActivoBajaRepositorioInterface;
use App\Repository\Persistencia\Activos\ActivoRepositorioInterface;
use App\Services\Shared\GeneradorCodigoService;

class RegistrarBajaAction
{
    public function __construct(
        private readonly GeneradorCodigoService $generadorCodigo,
        private readonly ActivoBajaRepositorioInterface $bajaRepositorio,
        private readonly ActivoRepositorioInterface $activoRepositorio,
    ) {}

    public function ejecutar(
        Activo $activo,
        TipoBaja $motivoTipo,
        string $motivoDetalle,
        int $userId,
        ?float $valorResidual = null,
        ?int $aprobadoPorId = null,
        ?string $documentoSoporte = null
    ): ActivoBaja {
        $activo->estado = EstadoActivo::DadoDeBaja;
        $this->activoRepositorio->guardar($activo);

        $codigoBaja = $this->generadorCodigo->generarCorrelativo(
            'BAJA-'.now()->format('Y'),
            ActivoBaja::class,
            'codigo'
        );

        return $this->bajaRepositorio->crear([
            'codigo' => $codigoBaja,
            'activo_id' => $activo->id,
            'fecha_baja' => now()->toDateString(),
            'motivo_tipo' => $motivoTipo,
            'motivo_detalle' => $motivoDetalle,
            'valor_residual' => $valorResidual,
            'aprobado_por_id' => $aprobadoPorId,
            'creado_por_id' => $userId,
            'documento_soporte' => $documentoSoporte,
        ]);
    }
}
