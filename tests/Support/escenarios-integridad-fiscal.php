<?php

declare(strict_types=1);

use App\Enums\Cuentas\EstadoCuenta;
use App\Enums\Cuentas\MetodoPago;
use App\Enums\Cuentas\TipoCuenta;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Interactors\Cuentas\Gestion\CerrarCuentaYGenerarVenta;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Cuentas\Venta;
use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaSerie;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Tesoreria\Caja;
use App\Repository\Models\User;

function escenarioIntegridadFiscal(int $limite = 100): array
{
    $tag = bin2hex(random_bytes(4));
    $moneda = Moneda::firstOrCreate(['codigo' => 'NIO'], ['nombre' => 'Córdoba', 'simbolo' => 'C$', 'es_predeterminada' => true]);
    Moneda::where('id', '!=', $moneda->id)->update(['es_predeterminada' => false]);
    $moneda->update(['es_predeterminada' => true]);
    $usuario = User::factory()->create(['name' => 'Auditor de demostración', 'is_admin' => true]);
    $serie = FacturaSerie::create(['codigo' => 'QA-'.$tag, 'nombre' => 'Serie DEMO no fiscal', 'prefijo_fiscal' => 'DEMO', 'caja_id' => Caja::firstOrCreate(['codigo' => 'DEMO-CAJA'], ['nombre' => 'Caja sintética fiscal', 'ubicacion' => 'Recepción de pruebas', 'creada_por' => $usuario->id])->id, 'siguiente_numero' => 1, 'activa' => true]);
    $autorizacion = FacturaAutorizacionDgi::create(['factura_serie_id' => $serie->id, 'numero_autorizacion' => 'DEMO-NO-VALIDO-'.$tag, 'ruc_emisor' => 'DEMO-NO-FISCAL', 'razon_social_emisor' => 'Emisor sintético de auditoría', 'fecha_autorizacion' => today()->subDay(), 'vence_at' => today()->addDays(30), 'rango_desde' => 1, 'rango_hasta' => $limite, 'activa' => true]);
    $cuenta = Cuenta::create(['numero_cuenta' => 'QA-CUENTA-'.$tag, 'tipo_cuenta' => TipoCuenta::RESTAURANTE_DIRECTO, 'moneda_id' => $moneda->id, 'estado' => EstadoCuenta::ABIERTA, 'abierta_at' => now()]);
    app(RegistrarDetalleCuenta::class)->ejecutar(cuenta: $cuenta, concepto: 'Servicio sintético de auditoría', precioUnitario: 100.0);
    $cuenta->refresh();

    return compact('moneda', 'usuario', 'serie', 'autorizacion', 'cuenta');
}
function emitirIntegridadFiscal(array $f): Venta
{
    app(RegistrarPagoCuenta::class)->ejecutar($f['cuenta'], MetodoPago::EFECTIVO, (float) $f['cuenta']->total, monedaId: $f['moneda']->id, usuarioId: $f['usuario']->id);

    return app(CerrarCuentaYGenerarVenta::class)->ejecutar($f['cuenta']->refresh(), $f['usuario']->id, ['emitir_factura' => true, 'factura_serie_id' => $f['serie']->id, 'receptor' => ['razon_social' => 'Cliente DEMO / sin validez fiscal']]);
}
