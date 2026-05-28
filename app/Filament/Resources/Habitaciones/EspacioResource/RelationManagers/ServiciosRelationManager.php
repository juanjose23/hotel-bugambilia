<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\EspacioResource\RelationManagers;

use App\Enums\HabitacionesEspacios\EstadoServicioHabitacion;
use App\Models\Espacios\ServicioEspacio;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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
    protected static string $relationship = 'serviciosEspacio';

    protected static ?string $title = 'Servicios de Espacio';

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
                    ->rules([$this->getUniqueServicioEspacioRule()])
                    ->live()
                    ->prefixIcon(Heroicon::Sparkles),

                Toggle::make('incluido')
                    ->label('Incluido')
                    ->default(false)
                    ->inline(false),

                Select::make('estado')
                    ->label('Estado')
                    ->options(EstadoServicioHabitacion::options())
                    ->default(EstadoServicioHabitacion::Activo->value)
                    ->required()
                    ->prefixIcon(Heroicon::CheckCircle),
            ]);
    }

    public function getUniqueServicioEspacioRule(): \Closure
    {
        return fn (callable $get, $component): \Closure => function (string $attribute, $value, \Closure $fail) use ($component) {
            $servicioId = intval($value);
            $parentRecord = $this->getOwnerRecord();
            $record = $component->getRecord();

            if ($servicioId === 0) {
                return;
            }

            $query = ServicioEspacio::withTrashed()
                ->where('servicio_id', $servicioId)
                ->where('espacio_id', $parentRecord->getKey());

            if ($record && $record->exists) {
                $query->where('id', '!=', $record->getKey());
            }

            if ($query->exists()) {
                $fail('Este servicio ya está asignado a este espacio.');
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
            ->headerActions([
                CreateAction::make()->icon(Heroicon::Plus),
            ])
            ->actions([
                EditAction::make()->iconButton(),
                DeleteAction::make()->iconButton(),
            ]);
    }
}
