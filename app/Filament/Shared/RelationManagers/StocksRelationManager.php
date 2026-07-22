<?php

declare(strict_types=1);

namespace App\Filament\Shared\RelationManagers;

use App\Enums\Shared\EstadoStock;
use App\Filament\Shared\Concerns\TieneAccionesCrudEstandar;
use App\Filament\Shared\Concerns\TieneSelectProductoVariante;
use App\Repository\Models\Shared\Stock;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StocksRelationManager extends RelationManager
{
    use TieneAccionesCrudEstandar, TieneSelectProductoVariante;

    protected static string $relationship = 'stocks';

    protected static ?string $title = 'Stock de Consumibles';

    protected static ?string $label = 'Item en Stock';

    protected static ?string $pluralLabel = 'Stock';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('producto_variante_id')
                    ->label('Producto / Variante')
                    ->options($this->getProductoVarianteOptions())
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
                    ->searchable()
                    ->sortable(),

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
                    ->label('Ãšlt. Verificación')
                    ->dateTime()
                    ->placeholder('Nunca')
                    ->sortable(),
            ])
            ->defaultSort('id')
            ->headerActions($this->getStandardHeaderActions('Agregar Item'))
            ->recordActions($this->getStandardRowActions());
    }

    /**
     * @param  Builder<Stock>  $query
     * @return Builder<Stock>
     */
    protected function modifyQueryUsing(Builder $query): Builder
    {
        return $query->with(['variante.producto']);
    }

    /** @return array<int, Action> */
    protected function getStandardHeaderActions(?string $label = null): array
    {
        return [
            CreateAction::make()
                ->label($label ?? $this->getCreateActionLabel())
                ->icon('heroicon-m-plus')
                ->before(function (CreateAction $action, array $data) {
                    $existing = $this->getRelationship()
                        ->where('producto_variante_id', $data['producto_variante_id'])
                        ->exists();

                    if ($existing) {
                        Notification::make()
                            ->title('El producto/variante ya existe en este stock')
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }
}
