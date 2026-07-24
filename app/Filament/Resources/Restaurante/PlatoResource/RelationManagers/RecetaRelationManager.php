<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\PlatoResource\RelationManagers;

use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Models\Restaurante\Plato;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RecetaRelationManager extends RelationManager
{
    protected static string $relationship = 'ingredientes';

    protected static ?string $title = 'Ingredientes de la Receta';

    protected static ?string $label = 'Ingrediente';

    protected static ?string $pluralLabel = 'Ingredientes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('producto_variante_id')
                    ->label('Ingrediente (Variante)')
                    ->options(fn (): array => ProductoVariante::query()
                        ->with('producto.unidadMedida')
                        ->whereHas('producto', fn ($q) => $q->whereNull('deleted_at'))
                        ->get()
                        ->mapWithKeys(fn (ProductoVariante $v) => [
                            $v->id => '['.($v->producto->nombre ?? '?').'] '.$v->nombre_variante.' ('.$v->codigo.')',
                        ])
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->live(),

                TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->required()
                    ->minValue(0.0001)
                    ->default(1)
                    ->suffix(fn (callable $get): string => $this->obtenerUnidadMedida($get('producto_variante_id'))),
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

                TextColumn::make('variante.codigo')
                    ->label('SKU')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('cantidad')
                    ->label('Cantidad')
                    ->sortable(),

                TextColumn::make('variante.unidadMedida.nombre')
                    ->label('Unidad')
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon(Heroicon::Plus)
                    ->before(function (RelationManager $livewire): void {
                        /** @var Plato $plato */
                        $plato = $livewire->getOwnerRecord();

                        if ($plato->producto_receta_id === null) {
                            Notification::make()
                                ->title('Seleccione una receta primero')
                                ->body('Debe asignar un Producto (receta) al plato antes de agregar ingredientes.')
                                ->warning()
                                ->send();

                            abort(403);
                        }
                    })
                    ->mutateDataUsing(function (array $data, RelationManager $livewire): array {
                        /** @var Plato $plato */
                        $plato = $livewire->getOwnerRecord();
                        $data['producto_padre_id'] = $plato->producto_receta_id;

                        return $data;
                    }),
            ])
            ->actions([
                EditAction::make()
                    ->iconButton(),
                DeleteAction::make()
                    ->iconButton(),
            ]);
    }

    private function obtenerUnidadMedida(?int $varianteId): string
    {
        if ($varianteId === null) {
            return 'uds';
        }

        /** @var ProductoVariante|null $variante */
        $variante = ProductoVariante::with('unidadMedida')->find($varianteId);

        return $variante->unidadMedida->nombre ?? 'uds';
    }
}
