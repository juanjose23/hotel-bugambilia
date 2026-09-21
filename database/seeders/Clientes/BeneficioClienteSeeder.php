<?php

declare(strict_types=1);

namespace Database\Seeders\Clientes;

use App\Enums\Promociones\EstadoUsoBeneficioCliente;
use App\Enums\Promociones\TipoBeneficioCliente;
use App\Enums\Promociones\TipoReglaBeneficioCliente;
use App\Repository\Models\Catalogos\Catalogo;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Cuentas\Cuenta;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\Promociones\Promocion;
use App\Repository\Models\Promociones\PromocionBeneficio;
use App\Repository\Models\Promociones\PromocionBeneficioRegla;
use App\Repository\Models\Promociones\PromocionBeneficioUso;
use App\Repository\Models\Reservas\Reserva;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

final class BeneficioClienteSeeder extends Seeder
{
    public function run(): void
    {
        $tipoRegular = Catalogo::query()->where('codigo', 'CLI_REGULAR')->first();
        $tipoCorporativo = Catalogo::query()->where('codigo', 'CLI_CORPORATIVO')->first();
        $tipoVIP = Catalogo::query()->where('codigo', 'CLI_VIP')->first();

        $promoRomantica = Promocion::query()->where('nombre', 'like', '%Romántica%')->first();
        $promoEstancia = Promocion::query()->where('nombre', 'like', '%Prolongada%')->first();

        $beneficiosDefinicion = [
            // ─── 01. PROGRAMA VIP (CLUB ORO BUGAMBILIAS) ───
            [
                'codigo' => 'BEN-VIP-ALOJ15',
                'nombre' => 'Descuento Exclusivo VIP en Alojamiento (15% OFF)',
                'segmento_cliente_id' => $tipoVIP?->id,
                'promocion_id' => null,
                'tipo' => TipoBeneficioCliente::DescuentoReserva,
                'valor' => 15.00,
                'es_porcentaje' => true,
                'combinable' => false,
                'limite_usos_por_cliente' => null,
                'activo' => true,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addYears(2)->endOfYear()->toDateString(),
                'descripcion' => 'Descuento del 15% en el total de estadía para clientes distinguidos VIP del Hotel Bugambilias en reservaciones con importe mínimo de $100.',
                'metadata' => ['nivel' => 'VIP Gold', 'icono' => 'sparkles', 'badge_color' => 'amber'],
                'reglas' => [
                    [
                        'tipo_regla' => TipoReglaBeneficioCliente::MontoMinimo,
                        'operador' => '>=',
                        'valor_numerico' => 100.00,
                        'valor_texto' => null,
                        'obligatoria' => true,
                    ],
                ],
            ],
            [
                'codigo' => 'BEN-VIP-REST12',
                'nombre' => 'Descuento Gourmet VIP en Restaurante (12% OFF)',
                'segmento_cliente_id' => $tipoVIP?->id,
                'promocion_id' => null,
                'tipo' => TipoBeneficioCliente::DescuentoRestaurante,
                'valor' => 12.00,
                'es_porcentaje' => true,
                'combinable' => true,
                'limite_usos_por_cliente' => null,
                'activo' => true,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addYears(2)->endOfYear()->toDateString(),
                'descripcion' => '12% de descuento automático en consumos de restaurante, cafetería y room service para clientes con membresía VIP.',
                'metadata' => ['nivel' => 'VIP Gold', 'icono' => 'utensils', 'badge_color' => 'amber'],
                'reglas' => [],
            ],
            [
                'codigo' => 'BEN-VIP-UPGRADE',
                'nombre' => 'Upgrade Preferencial de Habitación VIP',
                'segmento_cliente_id' => $tipoVIP?->id,
                'promocion_id' => null,
                'tipo' => TipoBeneficioCliente::UpgradeHabitacion,
                'valor' => 100.00,
                'es_porcentaje' => true,
                'combinable' => true,
                'limite_usos_por_cliente' => 3,
                'activo' => true,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addYears(2)->endOfYear()->toDateString(),
                'descripcion' => 'Upgrade de cortesía a Suite o habitación de categoría superior para estadías mínimas de 2 noches, sujeto a disponibilidad al check-in.',
                'metadata' => ['nivel' => 'VIP Gold', 'icono' => 'arrow-up-circle', 'badge_color' => 'purple'],
                'reglas' => [
                    [
                        'tipo_regla' => TipoReglaBeneficioCliente::NochesMinimas,
                        'operador' => '>=',
                        'valor_numerico' => 2.00,
                        'valor_texto' => null,
                        'obligatoria' => true,
                    ],
                ],
            ],
            [
                'codigo' => 'BEN-VIP-LATEOUT',
                'nombre' => 'Late Check-out Gratuito VIP (Hasta 2:00 PM)',
                'segmento_cliente_id' => $tipoVIP?->id,
                'promocion_id' => null,
                'tipo' => TipoBeneficioCliente::LateCheckout,
                'valor' => 0.00,
                'es_porcentaje' => false,
                'combinable' => true,
                'limite_usos_por_cliente' => null,
                'activo' => true,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addYears(2)->endOfYear()->toDateString(),
                'descripcion' => 'Salida tardía garantizada sin cargo adicional hasta las 2:00 PM para huéspedes distinguidos.',
                'metadata' => ['nivel' => 'VIP Gold', 'icono' => 'clock', 'badge_color' => 'blue'],
                'reglas' => [],
            ],

            // ─── 02. PROGRAMA CORPORATIVO (CONVENIOS EMPRESARIALES) ───
            [
                'codigo' => 'BEN-CORP-DESC10',
                'nombre' => 'Tarifa Corporativa Preferencial (10% OFF)',
                'segmento_cliente_id' => $tipoCorporativo?->id,
                'promocion_id' => null,
                'tipo' => TipoBeneficioCliente::DescuentoReserva,
                'valor' => 10.00,
                'es_porcentaje' => true,
                'combinable' => false,
                'limite_usos_por_cliente' => null,
                'activo' => true,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addYears(2)->endOfYear()->toDateString(),
                'descripcion' => 'Tarifa de convenio empresarial con 10% de descuento en habitaciones estándar, dobles y ejecutivas.',
                'metadata' => ['convenio' => 'Corporativo', 'icono' => 'building-2', 'badge_color' => 'blue'],
                'reglas' => [],
            ],
            [
                'codigo' => 'BEN-CORP-ANTICIPO',
                'nombre' => 'Anticipo Flexible Corporativo (20% en vez de 50%)',
                'segmento_cliente_id' => $tipoCorporativo?->id,
                'promocion_id' => null,
                'tipo' => TipoBeneficioCliente::AnticipoReducido,
                'valor' => 20.00,
                'es_porcentaje' => true,
                'combinable' => true,
                'limite_usos_por_cliente' => null,
                'activo' => true,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addYears(2)->endOfYear()->toDateString(),
                'descripcion' => 'Facilidad de pago institucional con anticipo mínimo del 20% para confirmación inmediata de reservas corporativas.',
                'metadata' => ['convenio' => 'Corporativo', 'icono' => 'credit-card', 'badge_color' => 'emerald'],
                'reglas' => [],
            ],
            [
                'codigo' => 'BEN-CORP-LATEOUT',
                'nombre' => 'Late Check-out Ejecutivo (Hasta 1:00 PM)',
                'segmento_cliente_id' => $tipoCorporativo?->id,
                'promocion_id' => null,
                'tipo' => TipoBeneficioCliente::LateCheckout,
                'valor' => 0.00,
                'es_porcentaje' => false,
                'combinable' => true,
                'limite_usos_por_cliente' => null,
                'activo' => true,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addYears(2)->endOfYear()->toDateString(),
                'descripcion' => 'Extensión de salida sin recargo hasta la 1:00 PM para personal y ejecutivos de empresas asociadas.',
                'metadata' => ['convenio' => 'Corporativo', 'icono' => 'clock-3', 'badge_color' => 'cyan'],
                'reglas' => [
                    [
                        'tipo_regla' => TipoReglaBeneficioCliente::NochesMinimas,
                        'operador' => '>=',
                        'valor_numerico' => 1.00,
                        'valor_texto' => null,
                        'obligatoria' => true,
                    ],
                ],
            ],

            // ─── 03. BENEFICIOS GLOBALES, BIENVENIDA & CUMPLEAÑOS ───
            [
                'codigo' => 'BEN-BIENV-10',
                'nombre' => 'Bono de Bienvenida Primera Reserva (10% OFF)',
                'segmento_cliente_id' => null, // Aplica a todos los nuevos clientes
                'promocion_id' => null,
                'tipo' => TipoBeneficioCliente::DescuentoReserva,
                'valor' => 10.00,
                'es_porcentaje' => true,
                'combinable' => false,
                'limite_usos_por_cliente' => 1,
                'activo' => true,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addYears(2)->endOfYear()->toDateString(),
                'descripcion' => '10% de descuento especial otorgado en la primera reservación de alojamiento efectuada por el cliente.',
                'metadata' => ['programa' => 'Bienvenida', 'icono' => 'gift', 'badge_color' => 'pink'],
                'reglas' => [
                    [
                        'tipo_regla' => TipoReglaBeneficioCliente::PrimerReserva,
                        'operador' => '=',
                        'valor_numerico' => null,
                        'valor_texto' => null,
                        'obligatoria' => true,
                    ],
                    [
                        'tipo_regla' => TipoReglaBeneficioCliente::UnaVezPorCliente,
                        'operador' => '=',
                        'valor_numerico' => 1.00,
                        'valor_texto' => null,
                        'obligatoria' => true,
                    ],
                ],
            ],
            [
                'codigo' => 'BEN-CUMPLE-POSTRE',
                'nombre' => 'Cortesía de Cumpleaños (Postre Gourmet o Coctel)',
                'segmento_cliente_id' => null,
                'promocion_id' => null,
                'tipo' => TipoBeneficioCliente::Cortesia,
                'valor' => 100.00,
                'es_porcentaje' => true,
                'combinable' => true,
                'limite_usos_por_cliente' => 1,
                'activo' => true,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addYears(2)->endOfYear()->toDateString(),
                'descripcion' => 'Postre de la casa o coctel de bienvenida de cortesía para el huésped registrado que festeje su cumpleaños durante la estancia.',
                'metadata' => ['programa' => 'Celebraciones', 'icono' => 'cake', 'badge_color' => 'indigo'],
                'reglas' => [
                    [
                        'tipo_regla' => TipoReglaBeneficioCliente::FechaNacimiento,
                        'operador' => '=',
                        'valor_numerico' => null,
                        'valor_texto' => null,
                        'obligatoria' => true,
                    ],
                ],
            ],
            [
                'codigo' => 'BEN-ESTANCIA-LARGA',
                'nombre' => 'Beneficio Larga Estancia (10% OFF Restaurante)',
                'segmento_cliente_id' => null,
                'promocion_id' => $promoEstancia?->id,
                'tipo' => TipoBeneficioCliente::DescuentoRestaurante,
                'valor' => 10.00,
                'es_porcentaje' => true,
                'combinable' => true,
                'limite_usos_por_cliente' => null,
                'activo' => true,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addYears(2)->endOfYear()->toDateString(),
                'descripcion' => '10% de descuento en restaurante para estadías consecutivas de 4 o más noches.',
                'metadata' => ['programa' => 'Estancia Prolongada', 'icono' => 'calendar-days', 'badge_color' => 'teal'],
                'reglas' => [
                    [
                        'tipo_regla' => TipoReglaBeneficioCliente::NochesMinimas,
                        'operador' => '>=',
                        'valor_numerico' => 4.00,
                        'valor_texto' => null,
                        'obligatoria' => true,
                    ],
                ],
            ],
            [
                'codigo' => 'BEN-ROMANTICO-CHAMP',
                'nombre' => 'Cortesía Botella de Vino en Paquete Romántico',
                'segmento_cliente_id' => null,
                'promocion_id' => $promoRomantica?->id,
                'tipo' => TipoBeneficioCliente::Cortesia,
                'valor' => 100.00,
                'es_porcentaje' => true,
                'combinable' => false,
                'limite_usos_por_cliente' => 1,
                'activo' => true,
                'fecha_inicio' => now()->startOfYear()->toDateString(),
                'fecha_fin' => now()->addYears(2)->endOfYear()->toDateString(),
                'descripcion' => 'Botella de vino tinto o blanco con copas en la habitación para reservas con paquete de Escapada Romántica.',
                'metadata' => ['programa' => 'Romance', 'icono' => 'wine', 'badge_color' => 'rose'],
                'reglas' => [],
            ],
        ];

        DB::transaction(function () use ($beneficiosDefinicion): void {
            foreach ($beneficiosDefinicion as $item) {
                $reglas = $item['reglas'];
                unset($item['reglas']);

                /** @var PromocionBeneficio $beneficio */
                $beneficio = PromocionBeneficio::query()->updateOrCreate(
                    ['codigo' => $item['codigo']],
                    $item
                );

                // Sincronizar reglas asociadas
                $beneficio->reglas()->delete();
                foreach ($reglas as $regla) {
                    PromocionBeneficioRegla::query()->create(array_merge($regla, [
                        'beneficio_id' => $beneficio->id,
                    ]));
                }
            }
        });

        $this->sembrarUsosHistoricosDemo();

        $this->command->info('Beneficios de cliente y reglas sembrados exitosamente.');
    }

    private function sembrarUsosHistoricosDemo(): void
    {
        // Clientes clave
        $personaVip = Persona::query()->where('primer_nombre', 'Roberto Carlos')->first();
        $clienteVip = $personaVip ? Cliente::query()->where('persona_id', $personaVip->id)->first() : null;

        $personaCorp = Persona::query()->where('primer_nombre', 'Hotel Hacienda Real S.A.')->orWhere('primer_nombre', 'Turismo del Norte S.A.')->first();
        $clienteCorp = $personaCorp ? Cliente::query()->where('persona_id', $personaCorp->id)->first() : null;

        $personaReg = Persona::query()->where('primer_nombre', 'Ana María')->first();
        $clienteReg = $personaReg ? Cliente::query()->where('persona_id', $personaReg->id)->first() : null;

        $beneficioVipAloj = PromocionBeneficio::query()->where('codigo', 'BEN-VIP-ALOJ15')->first();
        $beneficioVipRest = PromocionBeneficio::query()->where('codigo', 'BEN-VIP-REST12')->first();
        $beneficioCorpDesc = PromocionBeneficio::query()->where('codigo', 'BEN-CORP-DESC10')->first();
        $beneficioBienvenida = PromocionBeneficio::query()->where('codigo', 'BEN-BIENV-10')->first();
        $beneficioCumple = PromocionBeneficio::query()->where('codigo', 'BEN-CUMPLE-POSTRE')->first();

        // Reservas y cuentas demo
        $reservaVip = $clienteVip ? Reserva::query()->where('cliente_id', $clienteVip->id)->latest()->first() : null;
        $cuentaVip = Cuenta::query()->whereNotNull('cliente_id')->latest()->first();

        $reservaCorp = $clienteCorp ? Reserva::query()->where('cliente_id', $clienteCorp->id)->latest()->first() : null;
        $cuentaCorp = Cuenta::query()->latest()->first();

        $reservaReg = $clienteReg ? Reserva::query()->where('cliente_id', $clienteReg->id)->latest()->first() : null;

        $usos = [
            // 1. Uso VIP Alojamiento
            [
                'beneficio' => $beneficioVipAloj,
                'cliente' => $clienteVip,
                'reserva_id' => $reservaVip?->id,
                'cuenta_id' => $cuentaVip?->id,
                'venta_id' => null,
                'monto_descuento' => 37.50,
                'estado' => EstadoUsoBeneficioCliente::Aplicado,
                'usado_en' => now()->subDays(5),
                'metadata' => [
                    'tipo' => 'descuento_reserva',
                    'canal' => 'recepcion',
                    'nota' => '15% de descuento por membresía VIP Oro',
                ],
            ],
            // 2. Uso VIP Restaurante
            [
                'beneficio' => $beneficioVipRest,
                'cliente' => $clienteVip,
                'reserva_id' => $reservaVip?->id,
                'cuenta_id' => $cuentaVip?->id,
                'venta_id' => null,
                'monto_descuento' => 14.40,
                'estado' => EstadoUsoBeneficioCliente::Aplicado,
                'usado_en' => now()->subDays(3),
                'metadata' => [
                    'tipo' => 'descuento_restaurante',
                    'platos' => ['Cena gourmet', 'Bebidas'],
                ],
            ],
            // 3. Uso Corporativo Alojamiento
            [
                'beneficio' => $beneficioCorpDesc,
                'cliente' => $clienteCorp,
                'reserva_id' => $reservaCorp?->id,
                'cuenta_id' => $cuentaCorp?->id,
                'venta_id' => null,
                'monto_descuento' => 45.00,
                'estado' => EstadoUsoBeneficioCliente::Aplicado,
                'usado_en' => now()->subDays(12),
                'metadata' => [
                    'tipo' => 'descuento_reserva',
                    'convenio' => 'Tarifa empresarial corporativa 10%',
                ],
            ],
            // 4. Uso Bienvenida Nuevo Cliente
            [
                'beneficio' => $beneficioBienvenida,
                'cliente' => $clienteReg,
                'reserva_id' => $reservaReg?->id,
                'cuenta_id' => null,
                'venta_id' => null,
                'monto_descuento' => 22.00,
                'estado' => EstadoUsoBeneficioCliente::Aplicado,
                'usado_en' => now()->subDays(20),
                'metadata' => [
                    'tipo' => 'bienvenida',
                    'cupon' => 'BIENVENIDA_10',
                ],
            ],
            // 5. Uso Cumpleaños Cortesía
            [
                'beneficio' => $beneficioCumple,
                'cliente' => $clienteReg,
                'reserva_id' => $reservaReg?->id,
                'cuenta_id' => null,
                'venta_id' => null,
                'monto_descuento' => 8.50,
                'estado' => EstadoUsoBeneficioCliente::Aplicado,
                'usado_en' => now()->subDays(2),
                'metadata' => [
                    'tipo' => 'cumpleanos',
                    'item_cortesia' => 'Tres Leches Gourmet con Vela de Festejo',
                ],
            ],
        ];

        DB::transaction(function () use ($usos): void {
            foreach ($usos as $registro) {
                if ($registro['beneficio'] === null || $registro['cliente'] === null) {
                    continue;
                }

                $existe = PromocionBeneficioUso::query()
                    ->where('beneficio_id', $registro['beneficio']->id)
                    ->where('cliente_id', $registro['cliente']->id)
                    ->exists();

                if (! $existe) {
                    PromocionBeneficioUso::query()->create([
                        'beneficio_id' => $registro['beneficio']->id,
                        'cliente_id' => $registro['cliente']->id,
                        'reserva_id' => $registro['reserva_id'],
                        'cuenta_id' => $registro['cuenta_id'],
                        'venta_id' => $registro['venta_id'],
                        'monto_descuento' => $registro['monto_descuento'],
                        'estado' => $registro['estado'],
                        'usado_en' => $registro['usado_en'],
                        'metadata' => $registro['metadata'],
                    ]);
                }
            }
        });
    }
}
