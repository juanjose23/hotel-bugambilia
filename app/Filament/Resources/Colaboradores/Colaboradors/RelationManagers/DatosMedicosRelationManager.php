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
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DatosMedicosRelationManager extends RelationManager
{
    protected static string $relationship = 'datosMedicos';

    protected static ?string $title = 'Datos Médicos';

    protected static ?string $label = 'dato médico';

    protected static ?string $pluralLabel = 'datos médicos';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información de Salud')
                ->columnSpanFull()
                ->schema([
                    TextInput::make('tipo_sangre')
                        ->label('Tipo de Sangre')
                        ->placeholder('Ej. O+, A-')
                        ->maxLength(5)
                        ->prefixIcon(Heroicon::Beaker),

                    Select::make('estado')
                        ->label('Estado')
                        ->options(EstadoGeneral::options())
                        ->default(EstadoGeneral::Activo->value)
                        ->required()
                        ->selectablePlaceholder(false)
                        ->prefixIcon(Heroicon::CheckCircle),

                    Textarea::make('alergias')
                        ->label('Alergias')
                        ->rows(3)
                        ->columnSpanFull(),

                    Textarea::make('enfermedades_cronicas')
                        ->label('Condiciones Crónicas')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tipo_sangre')
                    ->label('Sangre')
                    ->badge()
                    ->color('danger'),

                TextColumn::make('alergias')
                    ->label('Alergias')
                    ->limit(30)
                    ->tooltip(fn ($state) => $state),

                EstadoBadgeColumn::make(EstadoGeneral::class),
            ])
            ->filters([
                FiltroEliminados::make(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->icon('heroicon-o-plus')
                    ->modalWidth('3xl'),
            ])
            ->recordActions([
                EditAction::make()
                    ->modalHeading('Editar información médica')
                    ->modalWidth('3xl'),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->emptyStateActions([
                CreateAction::make(),
            ]);
    }
}
