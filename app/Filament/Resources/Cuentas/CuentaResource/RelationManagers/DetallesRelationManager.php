<?php

declare(strict_types=1);

namespace App\Filament\Resources\Cuentas\CuentaResource\RelationManagers;

use App\Enums\Shared\EstadoGeneral;
use App\Interactors\Cuentas\Gestion\RegistrarDetalleCuenta;
use App\Repository\Models\Cuentas\Cuenta;
use App\Support\MonedaHelper;
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
use Illuminate\Database\Eloquent\Builder;

final class DetallesRelationManager extends RelationManager
{
    protected static string $relationship = 'detalles';

    protected static ?string $title = 'Detalles de Consumo';

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
                ->minValue(0.01)
                ->required(),
            TextInput::make('cantidad')
                ->label('Cantidad')
                ->numeric()
                ->default(1)
                ->minValue(0.01)
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('moneda'))
            ->columns(components: [
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('concepto')
                    ->label('Concepto')
                    ->searchable(),
                TextColumn::make('productos_comanda')
                    ->label('Productos solicitados')
                    ->state(state: function ($record): array {
                        $items = $record->metadatos['items'] ?? [];

                        if (! is_array($items)) {
                            return [];
                        }

                        return collect($items)
                            ->filter(static fn (mixed $item): bool => is_array($item))
                            ->map(static function (array $item) use ($record): string {
                                $nombre = isset($item['nombre']) && is_string($item['nombre'])
                                    ? $item['nombre']
                                    : 'Producto';

                                $cantidad = isset($item['cantidad']) && is_numeric($item['cantidad'])
                                    ? $item['cantidad']
                                    : 1;

                                $subtotal = isset($item['subtotal']) && is_numeric($item['subtotal'])
                                    ? (float) $item['subtotal']
                                    : 0.0;

                                return sprintf(
                                    '%s × %s — %s %s',
                                    $nombre,
                                    $cantidad,
                                    MonedaHelper::simbolo($record?->moneda),
                                    number_format($subtotal, 2),
                                );
                            })
                            ->values()
                            ->all();
                    })
                    ->bulleted()
                    ->listWithLineBreaks()
                    ->wrap()
                    ->placeholder('Detalle general'),
                TextColumn::make('cantidad')
                    ->label('Cant.')
                    ->numeric(decimalPlaces: 2),
                TextColumn::make('precio_unitario')
                    ->label('P. Unitario')
                    ->money(fn ($record): string => MonedaHelper::codigo($record?->moneda)),
                TextColumn::make('subtotal')
                    ->label('Subtotal')
                    ->money(fn ($record): string => MonedaHelper::codigo($record?->moneda))
                    ->sortable()
                    ->weight(FontWeight::Bold),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn (int $state): string => $state === EstadoGeneral::Activo->value ? 'Activo' : 'Anulado')
                    ->color(fn (int $state): string => $state === EstadoGeneral::Activo->value ? 'success' : 'danger'),
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
            ->recordActions([]);
    }
}
