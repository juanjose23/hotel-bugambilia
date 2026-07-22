<?php

namespace App\Filament\Resources\Colaboradores\Colaboradors\RelationManagers;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroEliminados;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ContactosEmergenciaRelationManager extends RelationManager
{
    protected static string $relationship = 'contactosEmergencia';

    protected static ?string $title = 'Contactos de Emergencia';

    protected static ?string $label = 'contacto';

    protected static ?string $pluralLabel = 'contactos';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Contacto de Emergencia')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('nombre')
                        ->label('Nombre Completo')
                        ->required()
                        ->maxLength(150)
                        ->prefixIcon(Heroicon::Identification),

                    TextInput::make('telefono')
                        ->label('Teléfono')
                        ->tel()
                        ->required()
                        ->maxLength(20)
                        ->prefixIcon(Heroicon::Phone),

                    TextInput::make('parentesco')
                        ->label('Parentesco')
                        ->maxLength(50)
                        ->prefixIcon(Heroicon::UserGroup),

                    Select::make('estado')
                        ->label('Estado')
                        ->options(EstadoGeneral::options())
                        ->default(EstadoGeneral::Activo->value)
                        ->required()
                        ->selectablePlaceholder(false)
                        ->prefixIcon(Heroicon::CheckCircle),
                ])->columns(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('telefono')
                    ->label('Teléfono')
                    ->searchable(),

                TextColumn::make('parentesco')
                    ->label('Parentesco')
                    ->placeholder('N/A'),

                EstadoBadgeColumn::make(EstadoGeneral::class),
            ])
            ->filters([
                FiltroEliminados::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->modalWidth('2xl'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalWidth('2xl'),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }
}
