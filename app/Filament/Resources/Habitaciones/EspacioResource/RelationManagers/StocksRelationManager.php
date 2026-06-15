<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers;

use App\Enums\HabitacionesEspacios\EstadoStock;
use App\Models\Catalogos\ProductoVariante;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StocksRelationManager extends RelationManager
{
    protected static string $relationship = 'espacioStocks';

    protected static ?string $title = 'Inventario Operativo — Blancos, Toallas y Minibar';

    protected static ?string $label = 'Ítem en Espacio';

    protected static ?string $pluralLabel = 'Stock en Espacio';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('producto_variante_id')
                    ->label('Producto / Variante')
                    ->options(
                        ProductoVariante::with('producto')
                            ->get()
                            ->mapWithKeys(fn (ProductoVariante $v) => [
                                $v->id => "[{$v->producto->nombre}] {$v->nombre_variante} ({$v->codigo})",
                            ])
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('cantidad_ideal')
                    ->label('Cantidad ideal')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),

                TextInput::make('cantidad_actual')
                    ->label('Cantidad actual')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->default(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variante.producto.nombre')
                    ->label('Producto')
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query
                            ->whereHas('variante.producto', fn (Builder $q) => $q->where('nombre', 'ilike', "%{$search}%"));
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('producto_variantes', 'espacio_stocks.producto_variante_id', '=', 'producto_variantes.id')
                            ->leftJoin('productos', 'producto_variantes.producto_id', '=', 'productos.id')
                            ->orderBy('productos.nombre', $direction)
                            ->select('espacio_stocks.*');
                    }),

                TextColumn::make('variante.nombre_variante')
                    ->label('Variante')
                    ->searchable(),

                TextColumn::make('cantidad_ideal')
                    ->label('Ideal')
                    ->sortable(),

                TextColumn::make('cantidad_actual')
                    ->label('Actual')
                    ->sortable(),

                TextColumn::make('diferencia')
                    ->label('Diferencia')
                    ->state(fn ($record) => (float) $record->cantidad_actual - (float) $record->cantidad_ideal)
                    ->color(fn ($state) => $state < 0 ? 'danger' : ($state > 0 ? 'warning' : 'success')),

                TextColumn::make('estado_enum')
                    ->label('Estado')
                    ->badge()
                    ->state(fn ($record) => $record->estado_enum)
                    ->color(fn (EstadoStock $state) => $state->color())
                    ->icon(fn (EstadoStock $state) => $state->getIcon()),

                TextColumn::make('ultima_verificacion')
                    ->label('Últ. Verificación')
                    ->dateTime()
                    ->placeholder('Nunca')
                    ->sortable(),
            ])
            ->defaultSort('espacio_stocks.id')
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar Item')
                    ->icon(Heroicon::Plus),
            ])
            ->actions([
                Action::make('consumir')
                    ->label('Registrar Consumo')
                    ->icon(Heroicon::MinusCircle)
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Registrar Consumo en Espacio')
                    ->schema([
                        TextInput::make('cantidad')
                            ->label('Cantidad consumida')
                            ->numeric()
                            ->required()
                            ->minValue(0.01)
                            ->rule(function (Get $get, $record) {
                                return function (string $attribute, $value, \Closure $fail) use ($record): void {
                                    if ((float) $value > (float) $record->cantidad_actual) {
                                        $fail("La cantidad no puede exceder el stock actual ({$record->cantidad_actual}).");
                                    }
                                };
                            }),
                        TextInput::make('motivo')
                            ->label('Motivo')
                            ->required()
                            ->default('Consumo registrado'),
                    ])
                    ->action(function (array $data, $record): void {
                        $record->decrement('cantidad_actual', (float) $data['cantidad']);
                    }),
            ]);
    }
}
