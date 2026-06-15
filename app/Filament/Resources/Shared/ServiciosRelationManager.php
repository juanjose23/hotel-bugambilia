<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shared;

use App\Enums\HabitacionesEspacios\EstadoServicioAsignacion;
use App\Filament\Resources\Shared\Concerns\HasStandardCrudActions;
use App\Models\Shared\ServicioAsignacion;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServiciosRelationManager extends RelationManager
{
    use HasStandardCrudActions;

    protected static string $relationship = 'servicioEntries';

    protected static ?string $title = 'Servicios';

    protected static ?string $label = 'Servicio';

    protected static ?string $pluralLabel = 'Servicios';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('servicio_id')
                    ->label('Servicio')
                    ->relationship('servicio', 'nombre')
                    ->required()
                    ->preload()
                    ->searchable()
                    ->rules([$this->getUniqueServicioRule()])
                    ->live()
                    ->prefixIcon(Heroicon::Sparkles),

                Toggle::make('incluido')
                    ->label('Incluido')
                    ->default(false)
                    ->inline(false),

                Select::make('estado')
                    ->label('Estado')
                    ->options(EstadoServicioAsignacion::options())
                    ->default(EstadoServicioAsignacion::Activo->value)
                    ->required()
                    ->prefixIcon(Heroicon::CheckCircle),
            ]);
    }

    public function getUniqueServicioRule(): \Closure
    {
        return fn (callable $get, $component): \Closure => function (string $attribute, $value, \Closure $fail) use ($component) {
            $servicioId = intval($value);
            $parentRecord = $this->getOwnerRecord();
            $record = $component->getRecord();

            if ($servicioId === 0) {
                return;
            }

            $query = ServicioAsignacion::withTrashed()
                ->where('servicio_id', $servicioId)
                ->where('serviceable_id', $parentRecord->getKey())
                ->where('serviceable_type', $parentRecord::class);

            if ($record && $record->exists) {
                $query->where('id', '!=', $record->getKey());
            }

            if ($query->exists()) {
                $fail('Este servicio ya está asignado.');
            }
        };
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('servicio.nombre')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('incluido')
                    ->label('Incluido')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state): string => $state->color())
                    ->formatStateUsing(fn ($state): string => $state->label()),
            ])
            ->headerActions($this->getStandardHeaderActions())
            ->actions($this->getStandardRowActions());
    }
}
