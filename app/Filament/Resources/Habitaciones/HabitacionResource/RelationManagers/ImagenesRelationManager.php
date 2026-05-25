<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagenesRelationManager extends RelationManager
{
    protected static string $relationship = 'imagenes';

    protected static ?string $title = 'Galería Multimedia';

    protected static ?string $label = 'Imagen';

    protected static ?string $pluralLabel = 'Imágenes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('url')
                    ->label('Imagen')
                    ->image()
                    ->directory('habitaciones/galeria')
                    ->maxSize(2048)
                    ->required(),

                TextInput::make('orden')
                    ->label('Orden')
                    ->numeric()
                    ->default(0)
                    ->minValue(0),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('url')
            ->columns([
                ImageColumn::make('url')
                    ->label('Vista Previa')
                    ->circular()
                    ->limit(1)
                    ->height(60),

                TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->searchable(),

                TextColumn::make('orden')
                    ->label('Orden')
                    ->sortable()
                    ->alignCenter(),
            ])
            ->defaultSort('orden')
            ->headerActions([
                CreateAction::make()
                    ->icon(Heroicon::Plus),
            ])
            ->recordActions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }
}
