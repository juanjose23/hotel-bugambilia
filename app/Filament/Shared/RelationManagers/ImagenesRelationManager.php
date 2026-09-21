<?php

declare(strict_types=1);

namespace App\Filament\Shared\RelationManagers;

use App\Repository\Models\Shared\Imagen;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ImagenesRelationManager extends RelationManager
{
    protected static string $relationship = 'imagenes';

    protected static ?string $title = 'Galería de Imágenes';

    protected static ?string $label = 'Imagen';

    protected static ?string $pluralLabel = 'Imágenes';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                FileUpload::make('url')
                    ->label('Archivo de Imagen')
                    ->image()
                    ->disk('public')
                    ->visibility('public')
                    ->directory('galeria')
                    ->required()
                    ->maxSize(4096)
                    ->columnSpanFull(),

                TextInput::make('orden')
                    ->label('Orden de visualización')
                    ->numeric()
                    ->default(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('url')
                    ->label('Vista Previa')
                    ->state(fn (Imagen $record): string => $record->url_completa)
                    ->height(70)
                    ->width(120)
                    ->extraImgAttributes(['class' => 'rounded-lg object-cover shadow-sm']),

                TextColumn::make('url')
                    ->label('Ruta / URL')
                    ->limit(50)
                    ->searchable(),

                TextColumn::make('orden')
                    ->label('Orden')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Fecha de Subida')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('orden', 'asc')
            ->headerActions([
                CreateAction::make()
                    ->label('Agregar Imagen'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
