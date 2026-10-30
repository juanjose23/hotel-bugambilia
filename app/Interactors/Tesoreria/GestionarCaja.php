<?php

declare(strict_types=1);

namespace App\Interactors\Tesoreria;

use App\BusinessLogic\Tesoreria\CalcularArqueo;
use App\Repository\Models\Cuentas\PagoCuenta;
use App\Repository\Models\Tesoreria\Caja;
use App\Repository\Models\Tesoreria\MovimientoCaja;
use App\Repository\Models\Tesoreria\SesionCaja;
use App\Repository\Models\User;
use App\Repository\Persistencia\Tesoreria\CajaRepositorio;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;

final readonly class GestionarCaja
{
    public function __construct(private CajaRepositorio $cajas, private CalcularArqueo $calculo) {}

    private function autorizar(int $usuarioId, string $permiso): User
    {
        if (! config('tesoreria.habilitado')) {
            throw new DomainException('La gestión de caja no está activada en este entorno.');
        }
        $user = $this->cajas->usuario($usuarioId);
        Gate::forUser($user)->authorize($permiso);

        return $user;
    }

    /** @param list<int> $monedas */
    public function crear(int $actorId, string $codigo, string $nombre, string $ubicacion, array $monedas): Caja
    {
        return DB::transaction(function () use ($actorId, $codigo, $nombre, $ubicacion, $monedas): Caja {
            $this->autorizar($actorId, 'Create:Caja');
            $codigo = strtoupper(trim($codigo));
            if (preg_match('/^[A-Z0-9_-]{2,30}$/', $codigo) !== 1 || trim($nombre) === '' || mb_strlen($nombre) > 100 || trim($ubicacion) === '' || mb_strlen($ubicacion) > 150 || $monedas === [] || count(array_unique($monedas)) !== count($monedas) || ! $this->cajas->monedasValidas($monedas) || $this->cajas->codigoExiste($codigo)) {
                throw new DomainException('Revisa el código, nombre, ubicación y monedas de la caja. El código debe ser único.');
            }

            return $this->cajas->crearCaja(['codigo' => $codigo, 'nombre' => trim($nombre), 'ubicacion' => trim($ubicacion), 'activa' => true, 'creada_por' => $actorId], $monedas);
        });
    }

    /** @param list<array{moneda_id:int,importe:float}> $fondos */
    public function abrir(int $actorId, int $cajaId, array $fondos, string $origen, string $clave): SesionCaja
    {
        return DB::transaction(function () use ($actorId, $cajaId, $fondos, $origen, $clave): SesionCaja {
            $this->autorizar($actorId, 'Abrir:Caja');
            $this->cajas->bloquearUsuario($actorId);
            $caja = $this->cajas->bloquearCaja($cajaId);
            if (! Str::isUuid($clave) || mb_strlen(trim($origen)) < 10 || mb_strlen($origen) > 2000) {
                throw new DomainException('Identifica quién entrega el fondo y su procedencia (al menos diez caracteres).');
            }
            $existente = $this->cajas->porClave($clave);
            if ($existente !== null) {
                if ($existente->usuario_id !== $actorId || $existente->caja_id !== $cajaId || $existente->origen_fondo !== trim($origen)) {
                    throw new DomainException('La referencia de apertura pertenece a otra operación.');
                }
                $saldos = $this->cajas->saldos($existente);
                if (count($fondos) !== $saldos->count()) {
                    throw new DomainException('La apertura ya fue registrada con otros importes.');
                }
                foreach ($fondos as $f) {
                    $s = $saldos->firstWhere('moneda_id', $f['moneda_id']);
                    if ($s === null || $this->calculo->centavos((float) $s->apertura) !== $this->calculo->centavos($f['importe'])) {
                        throw new DomainException('La apertura ya fue registrada con otros importes.');
                    }
                }

                return $existente;
            }
            if (! $caja->activa || $this->cajas->activaCaja($cajaId) !== null || $this->cajas->activaUsuario($actorId) !== null) {
                throw new DomainException('La caja o el cajero ya tiene un turno abierto o pendiente de revisión.');
            }
            $ids = array_column($fondos, 'moneda_id');
            $permitidas = $caja->monedas->modelKeys();
            if ($fondos === [] || count(array_unique($ids)) !== count($ids) || count($ids) !== count($permitidas) || array_diff($ids, $permitidas) !== []) {
                throw new DomainException('Registra una vez el fondo de cada moneda de la caja, incluso si es cero.');
            }
            foreach ($fondos as $f) {
                $this->calculo->centavos($f['importe']);
            }
            $s = $this->cajas->crearSesion(['codigo' => $clave, 'caja_id' => $cajaId, 'usuario_id' => $actorId, 'estado' => 'abierta', 'origen_fondo' => trim($origen), 'abierta_at' => now()]);
            foreach ($fondos as $f) {
                $this->cajas->crearSaldo($s, ['moneda_id' => $f['moneda_id'], 'apertura' => $f['importe']]);
            }

            return $s;
        });
    }

    public function movimiento(int $actorId, int $sesionId, int $monedaId, string $tipo, float $importe, string $concepto, string $clave, ?string $referencia = null): MovimientoCaja
    {
        return DB::transaction(function () use ($actorId, $sesionId, $monedaId, $tipo, $importe, $concepto, $clave, $referencia): MovimientoCaja {
            $user = $this->autorizar($actorId, $tipo === 'egreso' ? 'Egreso:Caja' : 'Operar:Caja');
            $s = $this->cajas->bloquearSesion($sesionId);
            if ($s->usuario_id !== $actorId) {
                throw new DomainException('Los movimientos manuales corresponden al responsable del turno.');
            }
            if (! in_array($tipo, ['ingreso', 'egreso'], true) || $this->calculo->centavos($importe) <= 0 || mb_strlen(trim($concepto)) < 10 || mb_strlen($concepto) > 1000 || ! Str::isUuid($clave) || ($referencia !== null && mb_strlen($referencia) > 150)) {
                throw new DomainException('Registra un importe positivo, referencia válida y motivo de al menos diez caracteres.');
            }
            $previo = $this->cajas->movimientoPorClave($clave);
            if ($previo !== null) {
                if ($previo->sesion_id !== $sesionId || $previo->moneda_id !== $monedaId || $previo->usuario_id !== $user->id || $previo->tipo !== $tipo || (float) $previo->importe !== $importe || $previo->concepto !== trim($concepto) || $previo->referencia !== $referencia) {
                    throw new DomainException('Esta referencia ya se usó con otros datos.');
                }

                return $previo;
            }
            if ($s->estado !== 'abierta') {
                throw new DomainException('El turno está cerrado o en revisión y no admite movimientos.');
            }
            $saldo = $this->cajas->saldo($s, $monedaId);
            if ($saldo === null) {
                throw new DomainException('La moneda no pertenece al turno.');
            }
            if ($tipo === 'egreso' && $this->calculo->centavos($this->cajas->esperado($s, $saldo)) < $this->calculo->centavos($importe)) {
                throw new DomainException('El egreso supera el efectivo registrado en esta moneda.');
            }

            return $this->cajas->crearMovimiento(['clave_operacion' => $clave, 'sesion_id' => $sesionId, 'moneda_id' => $monedaId, 'usuario_id' => $actorId, 'tipo' => $tipo, 'importe' => $importe, 'concepto' => trim($concepto), 'referencia' => $referencia]);
        });
    }

    public function registrarEfectivo(int $actorId, PagoCuenta $pago, int $monedaId, float $recibido, float $vuelto): void
    {
        DB::transaction(function () use ($actorId, $pago, $monedaId, $recibido, $vuelto): void {
            $this->autorizar($actorId, 'Operar:Caja');
            $actual = $this->cajas->activaUsuario($actorId);
            if ($actual === null) {
                throw new DomainException('Abre tu turno de caja antes de recibir efectivo.');
            }
            $s = $this->cajas->bloquearSesion($actual->id);
            if ($s->estado !== 'abierta' || $this->cajas->saldo($s, $monedaId) === null) {
                throw new DomainException('El turno no está abierto para esta moneda.');
            }
            if ($this->calculo->centavos($recibido) <= 0 || $this->calculo->centavos($recibido) - $this->calculo->centavos($vuelto) !== $this->calculo->centavos((float) $pago->monto)) {
                throw new DomainException('El efectivo recibido y el vuelto no corresponden al pago.');
            }
            if ($this->cajas->pagoTieneEfectivoRegistrado($pago->id)) {
                return;
            }
            $this->cajas->crearMovimiento(['clave_operacion' => Str::uuid()->toString(), 'sesion_id' => $s->id, 'moneda_id' => $monedaId, 'usuario_id' => $actorId, 'tipo' => 'cobro', 'importe' => $recibido, 'pago_cuenta_id' => $pago->id, 'concepto' => 'Efectivo recibido del pago '.$pago->id]);
            if ($vuelto > 0) {
                $this->cajas->crearMovimiento(['clave_operacion' => Str::uuid()->toString(), 'sesion_id' => $s->id, 'moneda_id' => $monedaId, 'usuario_id' => $actorId, 'tipo' => 'vuelto', 'importe' => $vuelto, 'pago_cuenta_id' => $pago->id, 'concepto' => 'Vuelto entregado del pago '.$pago->id]);
            }
        });
    }

    /** @param list<array{moneda_id:int,importe:float}> $conteo */
    public function cerrar(int $actorId, int $sesionId, array $conteo, ?string $motivo = null): SesionCaja
    {
        return DB::transaction(function () use ($actorId, $sesionId, $conteo, $motivo): SesionCaja {
            $this->autorizar($actorId, 'Cerrar:Caja');
            $s = $this->cajas->bloquearSesion($sesionId);
            if ($s->usuario_id !== $actorId) {
                throw new DomainException('Solo el responsable registra el arqueo de su turno.');
            }
            if ($s->estado === 'cerrada') {
                return $s;
            }
            if ($s->estado !== 'abierta') {
                throw new DomainException('El arqueo ya está pendiente de revisión por un supervisor.');
            }
            if ($this->cajas->abastecimientoPendiente($s->id)) {
                throw new DomainException('Resuelve la solicitud de fondo pendiente antes de cerrar el turno.');
            }
            $saldos = $this->cajas->saldos($s);
            $ids = array_column($conteo, 'moneda_id');
            if (count(array_unique($ids)) !== count($ids) || count($ids) !== $saldos->count() || array_diff($ids, $saldos->map(fn ($saldo): int => $saldo->moneda_id)->all()) !== []) {
                throw new DomainException('Cuenta todas las monedas del turno una sola vez.');
            }
            $diferencia = false;
            foreach ($conteo as $fila) {
                $saldo = $saldos->firstWhere('moneda_id', $fila['moneda_id']);
                if ($saldo === null) {
                    throw new DomainException('Moneda ajena al turno.');
                }$c = $this->calculo->comparar($this->cajas->esperado($s, $saldo), $fila['importe']);
                $diferencia = $diferencia || $c['diferencia'] !== 0.0;
                $this->cajas->guardarConteo($saldo, ['esperado_cierre' => $c['esperado'], 'contado' => $c['contado'], 'diferencia' => $c['diferencia']]);
            }
            if ($diferencia && (mb_strlen(trim($motivo ?? '')) < 10 || mb_strlen($motivo ?? '') > 2000)) {
                throw new DomainException('Explica la diferencia con al menos diez caracteres para revisión del supervisor.');
            }

            return $this->cajas->actualizarSesion($s, ['estado' => $diferencia ? 'pendiente_revision' : 'cerrada', 'contada_at' => now(), 'motivo_diferencia' => $diferencia ? trim($motivo ?? '') : null, 'cerrada_at' => $diferencia ? null : now(), 'cerrada_por' => $diferencia ? null : $actorId]);
        });
    }

    public function aprobarCierre(int $actorId, int $sesionId): SesionCaja
    {
        return DB::transaction(function () use ($actorId, $sesionId): SesionCaja {
            $this->autorizar($actorId, 'CerrarDiferencia:Caja');
            $s = $this->cajas->bloquearSesion($sesionId);
            if ($s->usuario_id === $actorId) {
                throw new DomainException('El cierre con diferencia requiere otro usuario autorizado.');
            }
            if ($s->estado === 'cerrada') {
                return $s;
            }
            if ($s->estado !== 'pendiente_revision') {
                throw new DomainException('El turno no está pendiente de revisión.');
            }

            return $this->cajas->actualizarSesion($s, ['estado' => 'cerrada', 'cerrada_at' => now(), 'cerrada_por' => $actorId]);
        });
    }
}
