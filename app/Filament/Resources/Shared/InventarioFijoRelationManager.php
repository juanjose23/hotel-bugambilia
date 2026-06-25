<?php

declare(strict_types=1);

namespace App\Filament\Resources\Shared;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\UseCases\Activos\Mutations\Asignacion\AsignarActivo;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InventarioFijoRelationManager extends RelationManager
{
    protected static string $relationship = 'asignacionesActivos';

    protected static ?string $title = 'Inventario Fijo — Accesorios y Mobiliario';

    protected static ?string $label = 'Activo Fijo';

    protected static ?string $pluralLabel = 'Inventario Fijo';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('activo_id')
                    ->label('Activo Fijo / Accesorio')
                    ->placeholder('Seleccione el activo a asignar')
                    ->options(function () {
                        return Activo::with('asignacionActiva')
                            ->where('estado', EstadoActivo::Activo->value)
                            ->get()
                            ->mapWithKeys(function (Activo $a) {
                                $ubicacionActual = $a->asignacionActiva?->destinoLabel() ?? 'Sin ubicación';

                                return [$a->id => "{$a->codigo_inventario} - {$a->nombre_descriptivo} (Ubicación: {$ubicacionActual})"];
                            })
                            ->all();
                    })
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

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
            ])
            ->defaultSort('fecha_inicio', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Asignar Activo Fijo')
                    ->icon(Heroicon::Plus)
                    ->using(function (array $data): Model {
                        $owner = $this->getOwnerRecord();
                        app(AsignarActivo::class)->execute(
                            activoId: is_numeric($data['activo_id'] ?? null) ? intval($data['activo_id']) : 0,
                            asignableType: $owner::class,
                            asignableId: is_numeric($owner->getKey()) ? intval($owner->getKey()) : 0,
                            userId: intval(auth()->id()),
                            motivo: $data['motivo']
                        );

                        return ActivoAsignacion::where('activo_id', $data['activo_id'])
                            ->whereNull('fecha_fin')
                            ->firstOrFail();
                    }),
            ])
            ->actions([
                Action::make('retirar')
                    ->label('Retirar / Reasignar')
                    ->icon(Heroicon::MinusCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Desvincular activo y reasignar destino')
                    ->schema([
                        Select::make('asignable_type')
                            ->label('Tipo de Destino')
                            ->placeholder('Seleccione tipo de destino')
                            ->options([
                                Habitacion::class => 'Habitación',
                                Ubicacion::class => 'Ubicación / Bodega',
                                Espacio::class => 'Espacio / Área Común',
                            ])
                            ->live()
                            ->native(false)
                            ->required()
                            ->afterStateUpdated(fn (callable $set) => $set('asignable_id', null)),

                        Select::make('asignable_id')
                            ->label('Destino Específico')
                            ->placeholder('Primero seleccione un tipo de destino')
                            ->options(function (Get $get) {
                                $type = $get('asignable_type');

                                return match ($type) {
                                    Habitacion::class => Habitacion::pluck('nombre', 'id'),
                                    Ubicacion::class => Ubicacion::pluck('nombre', 'id'),
                                    Espacio::class => Espacio::pluck('nombre', 'id'),
                                    default => [],
                                };
                            })
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required()
                            ->hidden(fn (callable $get) => blank($get('asignable_type'))),

                        Textarea::make('motivo')
                            ->label('Motivo de desvinculación')
                            ->required()
                            ->placeholder('Ej. Retiro temporal del activo'),
                    ])
                    ->action(function (ActivoAsignacion $record, array $data): void {
                        app(AsignarActivo::class)->execute(
                            activoId: $record->activo_id,
                            asignableType: $data['asignable_type'],
                            asignableId: (int) $data['asignable_id'],
                            userId: (int) auth()->id(),
                            motivo: $data['motivo']
                        );
                    })
                    ->visible(fn (ActivoAsignacion $record) => $record->estado === EstadoAsignacion::Vigente),
            ]);
    }
}
