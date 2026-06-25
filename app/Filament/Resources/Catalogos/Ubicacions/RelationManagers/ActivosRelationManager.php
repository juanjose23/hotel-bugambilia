<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogos\Ubicacions\RelationManagers;

use App\Enums\Activos\EstadoActivo;
use App\Filament\Resources\Shared\Concerns\HasActivoAsignaciones;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoAsignacion;
use App\UseCases\Activos\Mutations\Asignacion\AsignarActivo;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ActivosRelationManager extends RelationManager
{
    use HasActivoAsignaciones;

    protected static string $relationship = 'asignacionesActivos';

    protected static ?string $title = 'Activos Asignados';

    protected static ?string $label = 'Activo';

    protected static ?string $pluralLabel = 'Activos Asignados';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('activo_id')
                ->label('Activo Fijo / Accesorio')
                ->placeholder('Seleccione el activo a asignar')
                ->options(function () {
                    return Activo::where('estado', EstadoActivo::Activo->value)
                        ->get()
                        ->mapWithKeys(function (Activo $activo) {
                            $ubicacionActual = data_get($activo, 'asignacionActiva.asignable.nombre') ?? 'Sin ubicación';
                            $ubicacionActualStr = is_scalar($ubicacionActual) ? (string) $ubicacionActual : 'Sin ubicación';

                            $codigoInventario = (string) $activo->codigo_inventario;
                            $nombreDescriptivo = (string) $activo->nombre_descriptivo;

                            return [$activo->id => "{$codigoInventario} - {$nombreDescriptivo} (Ubicación: {$ubicacionActualStr})"];
                        })
                        ->all();
                })
                ->searchable()
                ->preload()
                ->required(),

            TextInput::make('motivo')
                ->label('Motivo de la Asignación')
                ->default('Asignado a ubicación desde panel de control')
                ->placeholder('Ej. Equipamiento de bodega')
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
                    ->label('Fecha Asignación')
                    ->date()
                    ->sortable(),

                TextColumn::make('fecha_fin')
                    ->label('Fecha Retiro')
                    ->date()
                    ->placeholder('Activo actualmente')
                    ->sortable(),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
            ])
            ->defaultSort('fecha_inicio', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Asignar Activo')
                    ->icon(Heroicon::Plus)
                    ->using(function (array $data): Model {
                        $owner = $this->getOwnerRecord();
                        $ownerKey = $owner->getKey();
                        $ownerId = is_numeric($ownerKey) ? (int) $ownerKey : 0;

                        app(AsignarActivo::class)->execute(
                            activoId: (int) $data['activo_id'],
                            asignableType: $owner::class,
                            asignableId: $ownerId,
                            userId: (int) (auth()->id() ?? 1),
                            motivo: $data['motivo']
                        );

                        return ActivoAsignacion::where('activo_id', $data['activo_id'])
                            ->whereNull('fecha_fin')
                            ->firstOrFail();
                    }),
            ])
            ->actions([
                $this->getDesvincularAction(
                    label: 'Desvincular / Retirar',
                    modalHeading: 'Desvincular activo y asignar destino',
                ),
            ]);
    }
}
