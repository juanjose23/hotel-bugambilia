<?php

declare(strict_types=1);

namespace App\Interactors\Cuentas\Beneficios;

use App\Enums\Cuentas\BaseCalculo;
use App\Enums\Cuentas\ModoCargo;
use App\Enums\Cuentas\TipoCargo;
use App\Enums\Promociones\EstadoUsoBeneficioCliente;
use App\Enums\Promociones\TipoBeneficioCliente;
use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Cuentas\Gestion\RecalcularCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\CuentaCargo;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Promociones\PromocionBeneficio;
use App\Repository\Models\Promociones\PromocionBeneficioUso;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Promociones\RegistrarUsoBeneficioCliente;
use App\Repository\Queries\Cuentas\ResolverClienteDeCuentaQuery;
use App\Repository\Queries\Promociones\ObtenerBeneficiosClienteElegiblesQuery;
use Illuminate\Support\Facades\Schema;

final readonly class AplicarBeneficioClienteCuenta
{
    private const CODIGO_CARGO = 'BENEFICIO_CLIENTE';

    public function __construct(
        private ResolverClienteDeCuentaQuery $resolverCliente,
        private ObtenerBeneficiosClienteElegiblesQuery $beneficiosElegibles,
        private RegistrarUsoBeneficioCliente $registrarUso,
        private RecalcularCuenta $recalcularCuenta,
        private CuentaRepositorioInterface $cuentas,
    ) {}

    public function aplicar(Cuenta $cuenta, ?int $usuarioId = null): ?CuentaCargo
    {
        if (! $this->tablasDisponibles()) {
            return null;
        }

        $cliente = $this->resolverCliente->ejecutar($cuenta);
        if ($cliente === null) {
            return null;
        }

        $subtotal = (float) $cuenta->subtotal;
        if ($subtotal <= 0) {
            $subtotal = $this->cuentas->subtotalDetallesActivos($cuenta);
        }

        $beneficio = $this->beneficiosElegibles
            ->paraCliente($cliente, [
                'monto' => $subtotal,
                'cuenta_id' => $cuenta->id,
                'tipo_consumo' => 'restaurante',
            ])
            ->filter(fn (PromocionBeneficio $beneficio): bool => in_array($beneficio->tipo, [
                TipoBeneficioCliente::DescuentoRestaurante,
                TipoBeneficioCliente::Cortesia,
            ], true))
            ->sortByDesc(fn (PromocionBeneficio $beneficio): float => $this->calcularDescuento($beneficio, $subtotal))
            ->first();

        if (! $beneficio instanceof PromocionBeneficio) {
            return null;
        }

        $montoDescuento = $this->calcularDescuento($beneficio, $subtotal);
        if ($montoDescuento <= 0) {
            return null;
        }

        $cargoExistente = $this->cuentas->cuentaCargoPorCodigo($cuenta, self::CODIGO_CARGO);
        $datosCargo = [
            'cargo_id' => null,
            'tipo' => TipoCargo::Descuento,
            'codigo' => self::CODIGO_CARGO,
            'nombre' => 'Beneficio cliente: '.$beneficio->nombre,
            'modo_calculo' => $beneficio->es_porcentaje ? ModoCargo::Porcentaje : ModoCargo::MontoFijo,
            'valor' => (float) ($beneficio->valor ?? 0),
            'base_calculo' => BaseCalculo::SubtotalBruto,
            'base_monto' => $subtotal,
            'monto' => $montoDescuento,
            'origen_type' => $beneficio->getMorphClass(),
            'origen_id' => $beneficio->id,
            'moneda_id' => $cuenta->moneda_id,
            'aplicado_por' => $usuarioId,
            'estado' => EstadoGeneral::Activo->value,
            'observaciones' => 'Aplicado automaticamente desde club de clientes.',
        ];

        if ($cargoExistente instanceof CuentaCargo) {
            $this->cuentas->actualizarCuentaCargo($cargoExistente, $datosCargo);
            $cargo = $cargoExistente->refresh();
        } else {
            $cargo = $this->cuentas->crearCuentaCargo($cuenta, $datosCargo);
        }

        $this->recalcularCuenta->ejecutar($cuenta, $usuarioId);

        return $cargo;
    }

    public function registrarUso(Cuenta $cuenta, ?Venta $venta = null): ?PromocionBeneficioUso
    {
        if (! $this->tablasDisponibles()) {
            return null;
        }

        $cliente = $this->resolverCliente->ejecutar($cuenta);
        $cargo = $this->cuentas->cuentaCargoPorCodigo($cuenta, self::CODIGO_CARGO);

        if ($cliente === null || ! $cargo instanceof CuentaCargo || ! is_numeric($cargo->origen_id)) {
            return null;
        }

        /** @var PromocionBeneficio|null $beneficio */
        $beneficio = PromocionBeneficio::query()->find((int) $cargo->origen_id);
        if (! $beneficio instanceof PromocionBeneficio) {
            return null;
        }

        return $this->registrarUso->registrar($beneficio, $cliente, [
            'cuenta_id' => $cuenta->id,
            'venta_id' => $venta?->id,
            'monto_descuento' => (float) $cargo->monto,
            'estado' => EstadoUsoBeneficioCliente::Aplicado,
            'metadata' => [
                'origen' => 'cobro_cuenta',
                'cargo_id' => $cargo->id,
            ],
        ]);
    }

    private function calcularDescuento(PromocionBeneficio $beneficio, float $subtotal): float
    {
        $valor = (float) ($beneficio->valor ?? 0);

        if ($beneficio->tipo === TipoBeneficioCliente::Cortesia && $valor <= 0) {
            return round($subtotal, 2);
        }

        $descuento = $beneficio->es_porcentaje
            ? $subtotal * min($valor, 100) / 100
            : $valor;

        return round(min($subtotal, max(0, $descuento)), 2);
    }

    private function tablasDisponibles(): bool
    {
        return Schema::hasTable('promocion_beneficios')
            && Schema::hasTable('promocion_beneficio_reglas')
            && Schema::hasTable('promocion_beneficio_usos');
    }
}
