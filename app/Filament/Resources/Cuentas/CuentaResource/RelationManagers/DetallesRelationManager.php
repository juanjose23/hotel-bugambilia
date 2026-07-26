<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\CuentaResource\RelationManagers;

use App\Interactors\Cuentas\RegistrarDetalleCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use BackedEnum;
use DomainException;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class DetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    protected static ?string $title = 'Detalles de consumo';

    protected static BackedEnum|string|null $icon = 'heroicon-m-document-text';

    public function form(Schema $schema): Schema
    {
        return $schema->schema([
            TextInput::make('concepto')
                ->label('Concepto')
                ->required()
                ->maxLength(255),
            TextInput::make('precio_unitario')
                ->label('Precio Unitario')
                ->numeric()
                ->prefix('C$')
                ->minValue(0.01)
                ->required(),
            TextInput::make('cantidad')
                ->label('Cantidad')
                ->numeric()
                ->default(1)
                ->minValue(0.01)
                ->required(),
            TextInput::make('impuesto')
                ->label('Impuesto')
                ->numeric()
                ->prefix('C$')
                ->default(0),
            TextInput::make('descuento')
                ->label('Descuento')
                ->numeric()
                ->prefix('C$')
                ->default(0),
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
                TextColumn::make('concepto')
                    ->label('Concepto')
                    ->searchable(),
                TextColumn::make('cantidad')
                    ->label('Cant.')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('precio_unitario')
                    ->label('P. Unitario')
                    ->money('NIO'),
                TextColumn::make('descuento')
                    ->label('Descuento')
                    ->money('NIO'),
                TextColumn::make('impuesto')
                    ->label('Impuesto')
                    ->money('NIO'),
                TextColumn::make('total')
                    ->label('Total')
                    ->money('NIO')
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('creador.name')
                    ->label('Registrado por')
                    ->placeholder('Sistema'),
            ])
            ->defaultSort('created_at', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Registrar cargo manual')
                    ->icon('heroicon-o-plus')
                    ->action(function (array $data): void {
                        try {
                            /** @var Cuenta $cuenta */
                            $cuenta = $this->getOwnerRecord();

                            /** @var int|null $userId */
                            $userId = auth()->id();

                            app(RegistrarDetalleCuenta::class)->ejecutar(
                                cuenta: $cuenta,
                                concepto: $data['concepto'],
                                precioUnitario: (float) $data['precio_unitario'],
                                cantidad: (float) ($data['cantidad'] ?? 1),
                                impuesto: (float) ($data['impuesto'] ?? 0),
                                descuento: (float) ($data['descuento'] ?? 0),
                                creadorId: $userId,
                            );

                            Notification::make()
                                ->title('Cargo registrado')
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
