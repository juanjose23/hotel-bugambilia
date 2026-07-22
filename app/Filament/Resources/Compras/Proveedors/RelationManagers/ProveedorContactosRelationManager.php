<?php

namespace App\Filament\Resources\Compras\Proveedors\RelationManagers;

use App\Filament\Shared\Infolists\TimestampsInfolistEntry;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProveedorContactosRelationManager extends RelationManager
{
    protected static string $relationship = 'contactos';

    protected static ?string $title = 'Contactos del proveedor';

    protected static ?string $label = 'contacto';

    protected static ?string $pluralLabel = 'contactos';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema(components: [
                Section::make('Información del Contacto')
                    ->columns()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre completo')
                            ->required()
                            ->maxLength(150)
                            ->prefixIcon(Heroicon::User),

                        TextInput::make('cargo')
                            ->label('Cargo')
                            ->maxLength(100)
                            ->nullable()
                            ->prefixIcon(Heroicon::Briefcase),

                        TextInput::make('telefono')
                            ->label('Teléfono')
                            ->maxLength(20)
                            ->nullable()
                            ->prefixIcon(Heroicon::Phone),

                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->maxLength(100)
                            ->email()
                            ->nullable()
                            ->prefixIcon(Heroicon::Envelope),

                        Toggle::make('principal')
                            ->label('Contacto principal')
                            ->inline(false)
                            ->helperText('Marcar como contacto por defecto del proveedor'),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->weight('bold'),

                TextColumn::make('cargo')
                    ->label('Cargo')
                    ->placeholder('—'),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->placeholder('—')
                    ->copyable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->placeholder('—')
                    ->copyable(),

                IconColumn::make('principal')
                    ->label('Principal')
                    ->boolean()
                    ->trueIcon(Heroicon::Star)
                    ->falseIcon(Heroicon::Star)
                    ->trueColor('warning')
                    ->falseColor('gray'),
            ])
            ->headerActions([
                CreateAction::make()->icon(Heroicon::Plus),
            ])
            ->recordActions([
                ViewAction::make()
                    ->iconButton()
                    ->schema([
                        Section::make('Detalles del contacto')
                            ->columns()
                            ->schema([
                                TextEntry::make('nombre')
                                    ->label('Nombre')
                                    ->icon(Heroicon::User)
                                    ->columnSpan(2),

                                TextEntry::make('cargo')
                                    ->label('Cargo')
                                    ->placeholder('—')
                                    ->icon(Heroicon::Briefcase),

                                TextEntry::make('principal')
                                    ->label('Principal')
                                    ->badge()
                                    ->color(fn ($state): string => $state ? 'warning' : 'gray')
                                    ->formatStateUsing(fn ($state): string => $state ? 'Sí' : 'No'),

                                TextEntry::make('telefono')
                                    ->label('Teléfono')
                                    ->placeholder('—')
                                    ->icon(Heroicon::Phone)
                                    ->copyable(),

                                TextEntry::make('email')
                                    ->label('Email')
                                    ->placeholder('—')
                                    ->icon(Heroicon::Envelope)
                                    ->copyable(),

                                ...TimestampsInfolistEntry::make(withIcons: true),
                            ]),
                    ]),
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }
}
