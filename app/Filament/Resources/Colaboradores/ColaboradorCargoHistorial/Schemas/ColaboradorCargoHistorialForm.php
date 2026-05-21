<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorCargoHistorial\Schemas;

use App\Enums\CatalogoTipo;
use App\Enums\EstadoCatalogo;
use App\UseCases\Colaboradores\Queries\ObtenerNombreCompleto;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ColaboradorCargoHistorialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Información del Cargo')
                ->description('Detalles del puesto y departamento asignado.')
                ->columnSpanFull()
                ->schema([
                    Select::make('colaborador_id')
                        ->relationship('colaborador', 'id')
                        ->getOptionLabelFromRecordUsing(
                            fn ($record) => app(ObtenerNombreCompleto::class)
                                ->nombreCompletoConCodigo($record)
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->prefixIcon(Heroicon::User)
                        ->helperText('Seleccione el colaborador al que se le asignará el cargo.')
                        ->columnSpanFull(),

                    Select::make('cargo_id')
                        ->label('Cargo')
                        ->relationship(
                            name: 'cargo',
                            titleAttribute: 'nombre',
                            modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                'catalogoTipo',
                                fn (Builder $q) => $q->where('codigo', CatalogoTipo::CARGO->value)
                            )
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->prefixIcon(Heroicon::Briefcase)
                        ->helperText('Seleccione el puesto de trabajo.'),

                    Select::make('departamento_id')
                        ->label('Departamento')
                        ->relationship(
                            name: 'departamento',
                            titleAttribute: 'nombre',
                            modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                'catalogoTipo',
                                fn (Builder $q) => $q->where('codigo', CatalogoTipo::DEPARTAMENTO->value)
                            )
                        )
                        ->searchable()
                        ->preload()
                        ->placeholder('Seleccione un departamento')
                        ->prefixIcon(Heroicon::BuildingOffice)
                        ->helperText('Departamento u área administrativa.'),

                    DatePicker::make('fecha_inicio')
                        ->label('Fecha de Inicio')
                        ->default(now())
                        ->required()
                        ->prefixIcon(Heroicon::Calendar)
                        ->helperText('Fecha en que inicia en esta posición.'),

                    DatePicker::make('fecha_fin')
                        ->label('Fecha de Fin')
                        ->prefixIcon(Heroicon::CalendarDays)
                        ->helperText('Dejar vacío si es el cargo actual.'),

                    Select::make('estado')
                        ->label('Estado')
                        ->options(EstadoCatalogo::options())
                        ->default(EstadoCatalogo::Activo->value)
                        ->required()
                        ->selectablePlaceholder(false)
                        ->prefixIcon(Heroicon::CheckCircle)
                        ->helperText('Indica si esta asignación está vigente.'),
                ])->columns(2),
        ]);
    }
}
