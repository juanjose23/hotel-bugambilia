<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogos\Productos\RelationManagers;

use App\Models\Catalogos\ProductoVariante;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class KitsRelationManager extends RelationManager
{
    protected static string $relationship = 'kitItems';

    protected static ?string $title = 'Items del Pack / Kit';

    protected static ?string $label = 'Item del Kit';

    protected static ?string $pluralLabel = 'Items del Kit';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('producto_variante_id')
                    ->label('Variante incluida')
                    ->options(fn () => ProductoVariante::with('producto')
                        ->get()
                        ->mapWithKeys(fn (ProductoVariante $v) => [
                            $v->id => '['.($v->producto ? $v->producto->nombre : '').'] '.$v->nombre_variante.' ('.$v->codigo.')',
                        ])
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('cantidad')
                    ->label('Cantidad')
                    ->numeric()
                    ->required()
                    ->minValue(0.0001)
                    ->default(1)
                    ->prefixIcon(Heroicon::Hashtag),

                TextInput::make('talla')
                    ->label('Talla (opcional)')
                    ->placeholder('Ej. S, M, L, XL')
                    ->maxLength(20)
                    ->prefixIcon(Heroicon::AdjustmentsHorizontal),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('variante.producto.nombre')
                    ->label('Producto')
                    ->searchable(),

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

                TextColumn::make('talla')
                    ->label('Talla')
                    ->placeholder('—')
                    ->badge()
                    ->color('info'),
            ])
            ->headerActions([
                CreateAction::make()->icon(Heroicon::Plus),
            ])
            ->actions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }
}
