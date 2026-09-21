<?php

declare(strict_types=1);

namespace App\Filament\Pages\Reservas\Tables;

use App\Enums\Cuentas\MetodoPago;
use App\Enums\Reservas\EstadoReserva;
use App\Filament\Pages\Reservas\CheckOutPage;
use App\Interactors\Cuentas\Cobros\RegistrarPagoCuenta;
use App\Repository\Models\Reservas\Reserva;
use App\Support\MonedaHelper;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class CheckOutEstanciasTable
{
    public static function configure(Table $table, CheckOutPage $page): Table
    {
        return $table
            ->query(
                Reserva::query()
                    ->with([
                        'habitacion',
                        'espacio',
                        'moneda',
                        'estancia.habitacion',
                        'estancia.cuenta.moneda',
                        'estancias.habitacion',
                        'estancias.cuenta.moneda',
                    ])
                    ->whereIn('estado', [
                        EstadoReserva::CHECKED_IN,
                        EstadoReserva::PARCIALMENTE_CHECKED_IN,
                        EstadoReserva::PARCIALMENTE_CHECKED_OUT,
                    ])
            )
            ->columns([
                TextColumn::make('codigo_reserva')
                    ->label('Reserva')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('habitacion.nombre')
                    ->label('Habitación')
                    ->icon('heroicon-o-home')
                    ->formatStateUsing(function (Reserva $record): string {
                        $hab = $record->habitacion ?? $record->estancia->habitacion ?? $record->estancias->first()?->habitacion;
                        if ($hab !== null) {
                            return "Hab. {$hab->numero} — {$hab->nombre}";
                        }

                        return $record->espacio->nombre ?? '—';
                    })
                    ->sortable(),

                TextColumn::make('nombre_cliente')
                    ->label('Huésped Titular')
                    ->icon('heroicon-o-user')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('fecha_check_in')
                    ->label('Entrada')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('fecha_check_out')
                    ->label('Salida Prevista')
                    ->date('d/m/Y')
                    ->description(fn (Reserva $record): string => $record->fecha_check_out?->isToday() ? '¡Sale Hoy!' : '')
                    ->sortable(),

                TextColumn::make('estancia.cuenta.saldo')
                    ->label('Saldo Cuenta')
                    ->formatStateUsing(function (Reserva $record): string {
                        $cuenta = $record->estancia->cuenta ?? null;
                        $saldo = $cuenta !== null ? (float) $cuenta->saldo : 0.0;
                        $moneda = $cuenta->moneda ?? $record->moneda;

                        if ($saldo <= 0.0) {
                            return MonedaHelper::formatear(0.0, $moneda).' (Al día)';
                        }

                        return MonedaHelper::formatear($saldo, $moneda).' (Pendiente)';
                    })
                    ->badge()
                    ->color(function (Reserva $record): string {
                        $cuenta = $record->estancia->cuenta ?? null;
                        $saldo = $cuenta !== null ? (float) $cuenta->saldo : 0.0;

                        return $saldo <= 0.0 ? 'success' : 'danger';
                    }),
            ])
            ->defaultSort('fecha_check_out')
            ->recordActions([
                Action::make('iniciar_check_out')
                    ->label('Realizar Check-Out')
                    ->icon('heroicon-o-arrow-right-on-rectangle')
                    ->color('warning')
                    ->url(fn (Reserva $record): string => CheckOutPage::getUrl(['record' => $record->id])),

                Action::make('cobrar_rapido')
                    ->label('Cobrar')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (Reserva $record): bool => ($record->estancia->cuenta->saldo ?? 0.0) > 0)
                    ->schema([
                        Select::make('forma_pago')
                            ->label('Método de Pago')
                            ->options(MetodoPago::class)
                            ->default(MetodoPago::EFECTIVO)
                            ->required(),
                        TextInput::make('monto')
                            ->label('Monto a Cobrar')
                            ->numeric()
                            ->prefix(fn (Reserva $record): string => MonedaHelper::codigo($record->estancia->cuenta->moneda ?? $record->moneda))
                            ->default(fn (Reserva $record): float => (float) ($record->estancia->cuenta->saldo ?? 0.0))
                            ->required()
                            ->minValue(0.01),
                        TextInput::make('propina')
                            ->label('Propina voluntaria')
                            ->numeric()
                            ->default(0.0)
                            ->minValue(0),
                        TextInput::make('referencia_transaccion')
                            ->label('N° Referencia / Voucher')
                            ->placeholder('Ej. POS-987654')
                            ->maxLength(100),
                    ])
                    ->action(function (array $data, Reserva $record, RegistrarPagoCuenta $registrarPago): void {
                        $cuenta = $record->estancia?->cuenta;
                        if ($cuenta === null) {
                            return;
                        }

                        $metodo = $data['forma_pago'] instanceof MetodoPago
                            ? $data['forma_pago']
                            : (is_string($data['forma_pago']) ? MetodoPago::tryFrom($data['forma_pago']) : null) ?? MetodoPago::EFECTIVO;

                        $usuarioId = auth()->id();
                        $pago = $registrarPago->ejecutar(
                            cuenta: $cuenta,
                            metodoPago: $metodo,
                            monto: (float) $data['monto'],
                            propina: is_numeric($data['propina'] ?? null) ? (float) $data['propina'] : 0.0,
                            referenciaTransaccion: isset($data['referencia_transaccion']) && is_string($data['referencia_transaccion']) ? $data['referencia_transaccion'] : null,
                            observaciones: 'Cobro rápido desde tabla de Check-Out',
                            usuarioId: is_int($usuarioId) ? $usuarioId : null,
                        );

                        $montoAbono = MonedaHelper::formatear((float) $pago->monto, $cuenta->moneda);
                        Notification::make()
                            ->title('Pago registrado')
                            ->body("Se aplicó abono de {$montoAbono} a la cuenta #{$cuenta->numero_cuenta}.")
                            ->success()
                            ->send();
                    }),
            ]);
    }
}
