<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia\ColaboradorDatosMedicos;

use App\Enums\Catalogos\EstadoCatalogo;
use App\Enums\Personas\TipoSangre;
use App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia\ColaboradorDatosMedicos\Pages\CreateColaboradorDatosMedicos;
use App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia\ColaboradorDatosMedicos\Pages\EditColaboradorDatosMedicos;
use App\Filament\Resources\Colaboradores\ColaboradorContactoEmergencia\ColaboradorDatosMedicos\Pages\ListColaboradorDatosMedicos;
use App\Filament\Resources\Shared\Filters\FiltroEliminados;
use App\Models\Colaboradores\ColaboradorDatosMedicos;
use App\UseCases\Colaboradores\Queries\ObtenerNombreCompleto;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ColaboradorDatosMedicosResource extends Resource
{
    protected static ?string $model = ColaboradorDatosMedicos::class;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return 'Gestión de Colaboradores';
    }

    public static function getNavigationIcon(): string|BackedEnum|null
    {
        return Heroicon::Heart;
    }

    public static function getNavigationLabel(): string
    {
        return 'Salud';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información')
                ->schema([
                    Select::make('colaborador_id')
                        ->relationship('colaborador', 'id')
                        ->getOptionLabelFromRecordUsing(
                            fn ($record) => app(ObtenerNombreCompleto::class)
                                ->nombreCompletoConCodigo($record)
                        )
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('tipo_sangre')
                        ->label('Tipo de Sangre')
                        ->options(TipoSangre::options())
                        ->searchable()
                        ->placeholder('Selecciona un tipo de sangre...'),

                    Select::make('estado')
                        ->label('Estado')
                        ->options(EstadoCatalogo::options())
                        ->default(EstadoCatalogo::Activo->value)
                        ->required()
                        ->selectablePlaceholder(false),

                    Textarea::make('alergias')
                        ->label('Alergias')
                        ->rows(3)
                        ->columnSpanFull(),

                    Textarea::make('enfermedades_cronicas')
                        ->label('Enfermedades Crónicas')
                        ->rows(3)
                        ->columnSpanFull(),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitle(fn (ColaboradorDatosMedicos $record): string => $record->colaborador->codigo ?? 'Datos médicos')
            ->columns([
                TextColumn::make('colaborador')
                    ->label('Colaborador')
                    ->formatStateUsing(
                        fn ($record) => app(ObtenerNombreCompleto::class)
                            ->obtenerNombreCompleto($record->colaborador)
                    )
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tipo_sangre')
                    ->label('Tipo de Sangre')
                    ->searchable(),
                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->formatStateUsing(fn ($state): string => EstadoCatalogo::labelFor($state))
                    ->color(fn ($state): ?string => is_string($color = EstadoCatalogo::colorFor($state)) ? $color : null),
                TextColumn::make('updated_at')
                    ->label('Actualizado')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                FiltroEliminados::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListColaboradorDatosMedicos::route('/'),
            'create' => CreateColaboradorDatosMedicos::route('/create'),
            'edit' => EditColaboradorDatosMedicos::route('/{record}/edit'),
        ];
    }
}
