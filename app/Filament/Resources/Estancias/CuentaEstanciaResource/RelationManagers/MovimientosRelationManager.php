<?php

declare(strict_types=1);

namespace App\Filament\Resources\Estancias\CuentaEstanciaResource\RelationManagers;

use App\Enums\Estancias\TipoMovimientoCuenta;
use App\Interactors\CuentasEstancia\RegistrarMovimientoCuenta;
use App\Repository\Models\Estancias\CuentaEstancia;
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

final class MovimientosRelationManager extends RelationManager
{
    protected static string $relationship = 'movimientos';

    protected static ?string $title = 'Movimientos de cuenta';

    protected static BackedEnum|string|null $icon = 'heroicon-m-currency-dollar';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            Select::make('tipo')
                ->label('Tipo de movimiento')
                ->options(TipoMovimientoCuenta::opciones())
                ->required()
                ->native(false),
            TextInput::make('concepto')
                ->label('Concepto')
                ->required()
                ->maxLength(200),
            TextInput::make('monto')
                ->label('Monto')
                ->numeric()
                ->prefix('C$')
                ->minValue(0.01)
                ->required(),
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
                TextColumn::make('tipo')
                    ->label('Tipo')
                    ->badge(),
                TextColumn::make('concepto')
                    ->label('Concepto')
                    ->searchable(),
                TextColumn::make('monto')
                    ->label('Monto')
                    ->money('NIO')
                    ->sortable(),
                TextColumn::make('usuario.name')
                    ->label('Registrado por')
                    ->placeholder('Sistema'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar movimiento')
                    ->icon('heroicon-o-plus')
                    ->action(function (array $data): void {
                        try {
                            /** @var CuentaEstancia $cuenta */
                            $cuenta = $this->getOwnerRecord();

                            $interactor = app(RegistrarMovimientoCuenta::class);
                            /** @var int|null $userId */
                            $userId = auth()->id();

                            $interactor->ejecutar(
                                cuenta: $cuenta,
                                tipo: TipoMovimientoCuenta::from($data['tipo']),
                                concepto: $data['concepto'],
                                monto: (float) $data['monto'],
                                usuarioId: $userId,
                            );

                            Notification::make()
                                ->title('Movimiento registrado')
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
