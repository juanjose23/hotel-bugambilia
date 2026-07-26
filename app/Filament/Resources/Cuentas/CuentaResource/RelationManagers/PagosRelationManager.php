<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\CuentaResource\RelationManagers;

use App\Enums\Cuentas\EstadoPago;
use App\Enums\Cuentas\MetodoPago;
use App\Interactors\Cuentas\RegistrarPagoCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use BackedEnum;
use DomainException;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class PagosRelationManager extends RelationManager
{
    protected static string $relationship = 'pagos';

    protected static ?string $title = 'Pagos y Abonos';

    protected static BackedEnum|string|null $icon = 'heroicon-m-banknotes';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('forma_pago')
                ->label('Forma de Pago')
                ->options(MetodoPago::class)
                ->required()
                ->native(false),
            TextInput::make('monto')
                ->label('Monto')
                ->numeric()
                ->prefix('C$')
                ->minValue(0.01)
                ->required(),
            TextInput::make('propina')
                ->label('Propina')
                ->numeric()
                ->prefix('C$')
                ->default(0),
            TextInput::make('referencia_transaccion')
                ->label('Referencia / Voucher')
                ->maxLength(100),
            TextInput::make('observaciones')
                ->label('Observaciones')
                ->maxLength(255),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('forma_pago')
                    ->label('Forma de Pago')
                    ->badge(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
                TextColumn::make('monto')
                    ->label('Monto')
                    ->money('NIO')
                    ->sortable(),
                TextColumn::make('propina')
                    ->label('Propina')
                    ->money('NIO'),
                TextColumn::make('referencia_transaccion')
                    ->label('Referencia')
                    ->placeholder('—'),
                TextColumn::make('usuario.name')
                    ->label('Recibido por')
                    ->placeholder('Sistema'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar pago')
                    ->icon('heroicon-o-banknotes')
                    ->action(function (array $data): void {
                        try {
                            /** @var Cuenta $cuenta */
                            $cuenta = $this->getOwnerRecord();

                            /** @var int|null $userId */
                            $userId = auth()->id();

                            app(RegistrarPagoCuenta::class)->ejecutar(
                                cuenta: $cuenta,
                                metodoPago: MetodoPago::from((int) $data['forma_pago']),
                                monto: (float) $data['monto'],
                                propina: (float) ($data['propina'] ?? 0),
                                estado: EstadoPago::APLICADO,
                                referenciaTransaccion: $data['referencia_transaccion'] ?? null,
                                observaciones: $data['observaciones'] ?? null,
                                usuarioId: $userId,
                            );

                            Notification::make()
                                ->title('Pago registrado')
                                ->success()
                                ->send();
                        } catch (DomainException $e) {
                            Notification::make()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
            ])
            ->actions([]);
    }
}
