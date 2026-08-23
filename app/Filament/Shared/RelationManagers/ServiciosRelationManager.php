<?php

declare(strict_types=1);

namespace App\Filament\Shared\RelationManagers;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Concerns\TieneAccionesCrudEstandar;
use App\Repository\Models\Shared\ServicioAsignacion;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ServiciosRelationManager extends RelationManager
{
    use TieneAccionesCrudEstandar;

    protected static string $relationship = 'servicioAsignaciones';

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
                    ->prefixIcon(Heroicon::CheckBadge),

                Toggle::make('incluido')
                    ->label('Incluido')
                    ->default(false)
                    ->inline(false),

                Select::make('estado')
                    ->label('Estado')
                    ->options(EstadoGeneral::options())
                    ->default(EstadoGeneral::Activo->value)
                    ->required()
                    ->prefixIcon(Heroicon::CheckCircle),
            ]);
    }

    public function getUniqueServicioRule(): Closure
    {
        return fn (callable $get, $component): Closure => function (string $attribute, $value, Closure $fail) use ($component) {
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
        $table
            ->columns([
                TextColumn::make('servicio.nombre')
                    ->label('Servicio')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('incluido')
                    ->label('Incluido')
                    ->boolean()
                    ->alignCenter(),

                EstadoBadgeColumn::make(EstadoGeneral::class),
            ]);

        return $table
            ->headerActions($this->getStandardHeaderActions())
            ->actions($this->getStandardRowActions());
    }

    /**
     * @param  Builder<ServicioAsignacion>  $query
     * @return Builder<ServicioAsignacion>
     */
    protected function modifyQueryUsing(Builder $query): Builder
    {
        return $query->with(['servicio']);
    }
}
