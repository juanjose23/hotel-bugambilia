<?php

declare(strict_types=1);

namespace App\Filament\Shared\RelationManagers;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Concerns\TieneActivoAsignaciones;
use App\Interactors\Activos\Gestion\AsignarActivo;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Activos\ActivoAsignacion;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class InventarioFijoRelationManager extends RelationManager
{
    use TieneActivoAsignaciones;

    protected AsignarActivo $asignarActivo;

    public function boot(AsignarActivo $asignarActivo): void
    {
        $this->asignarActivo = $asignarActivo;
    }

    protected static string $relationship = 'asignacionesActivos';

    protected static ?string $title = 'Inventario Fijo – Accesorios y Mobiliario';

    protected static ?string $label = 'Activo Fijo';

    protected static ?string $pluralLabel = 'Inventario Fijo';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('activo_id')
                    ->label('Activo Fijo / Accesorio')
                    ->placeholder('Seleccione el activo a asignar')
                    ->options(fn (): array => Activo::query()
                        ->select(['id', 'codigo_inventario', 'nombre_descriptivo'])
                        ->where('estado', EstadoActivo::Activo->value)
                        ->with(['asignacionActiva.asignable'])
                        ->get()
                        ->mapWithKeys(fn (Activo $activo): array => [
                            $activo->id => sprintf(
                                '%s - %s (Ubicación: %s)',
                                $activo->codigo_inventario,
                                $activo->nombre_descriptivo,
                                $activo->asignacionActiva?->destinoLabel() ?? 'Sin ubicación'
                            ),
                        ])
                        ->all()
                    )
                    ->searchable()
                    ->preload()
                    ->required(),

                TextInput::make('motivo')
                    ->label('Motivo de la Asignación')
                    ->default('Asignado desde panel de inventario fijo')
                    ->placeholder('Ej. Equipamiento de habitación')
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('activo.codigo_inventario')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('activo.nombre_descriptivo')
                    ->label('Nombre / Descripción')
                    ->searchable(),

                TextColumn::make('activo.numero_serie')
                    ->label('Nº Serie')
                    ->searchable()
                    ->placeholder('N/A'),

                TextColumn::make('fecha_inicio')
                    ->label('Asignado desde')
                    ->date()
                    ->sortable(),

                TextColumn::make('fecha_fin')
                    ->label('Retirado el')
                    ->date()
                    ->placeholder('Actualmente asignado')
                    ->sortable(),

                EstadoBadgeColumn::make(EstadoAsignacion::class),
            ])
            ->defaultSort('fecha_inicio', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Asignar Activo Fijo')
                    ->icon(Heroicon::Plus)
                    ->using(function (array $data): Model {
                        $owner = $this->getOwnerRecord();
                        $this->asignarActivo->ejecutar(
                            activoId: (int) ($data['activo_id'] ?? 0),
                            asignableType: $owner::class,
                            asignableId: intval(is_scalar($owner->getKey()) ? $owner->getKey() : 0),
                            userId: (int) auth()->id(),
                            motivo: (string) $data['motivo']
                        );

                        return ActivoAsignacion::query()
                            ->where('activo_id', $data['activo_id'])
                            ->whereNull('fecha_fin')
                            ->firstOrFail();
                    }),
            ])
            ->actions([
                $this->getDesvincularAction(),
            ]);
    }

    /**
     * @param  Builder<ActivoAsignacion>  $query
     * @return Builder<ActivoAsignacion>
     */
    protected function modifyQueryUsing(Builder $query): Builder
    {
        return $query->with(['activo']);
    }
}
