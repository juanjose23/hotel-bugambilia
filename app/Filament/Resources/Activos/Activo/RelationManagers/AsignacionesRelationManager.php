<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo\RelationManagers;

use App\Enums\Activos\EstadoAsignacion;
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
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AsignacionesRelationManager extends RelationManager
{
    protected static string $relationship = 'asignaciones';

    protected static ?string $title = 'Ubicación y Asignaciones';

    protected static ?string $label = 'Asignación';

    protected static ?string $pluralLabel = 'Asignaciones';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('asignable_type')
                ->label('Tipo de Destino')
                ->placeholder('Seleccione tipo de destino')
                ->options([
                    Habitacion::class => 'Habitación',
                    Ubicacion::class => 'Ubicación / Bodega',
                    Espacio::class => 'Espacio / Área Común',
                ])
                ->reactive()
                ->native(false)
                ->required()
                ->afterStateUpdated(fn (callable $set) => $set('asignable_id', null)),

            Select::make('asignable_id')
                ->label('Destino Específico')
                ->placeholder('Primero seleccione un tipo de destino')
                ->options(function (callable $get) {
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

            TextInput::make('motivo')
                ->label('Motivo de Asignación')
                ->placeholder('Ej. Traslado inicial a habitación 101')
                ->required()
                ->columnSpanFull(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('fecha_inicio')
                    ->label('Desde')
                    ->date('d/m/Y')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('asignable_type')
                    ->label('Tipo')
                    ->badge()
                    ->state(fn (ActivoAsignacion $record) => $record->tipoDestinoLabel())
                    ->color(fn (ActivoAsignacion $record) => $record->tipoDestinoColor()),

                TextColumn::make('asignable.nombre')
                    ->label('Destino')
                    ->state(fn (ActivoAsignacion $record) => $record->destinoLabel()),

                TextColumn::make('fecha_fin')
                    ->label('Hasta')
                    ->date('d/m/Y')
                    ->placeholder('Vigente')
                    ->sortable(),

                TextColumn::make('motivo')
                    ->label('Motivo')
                    ->limit(60)
                    ->placeholder('—'),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge(),
            ])
            ->defaultSort('fecha_inicio', 'desc')
            ->headerActions([
                CreateAction::make()
                    ->label('Asignar / Trasladar')
                    ->icon(Heroicon::MapPin)
                    ->using(function (array $data): Model {
                        $owner = $this->getOwnerRecord();

                        app(AsignarActivo::class)->execute(
                            activoId: (int) $owner->getKey(),
                            asignableType: $data['asignable_type'],
                            asignableId: (int) $data['asignable_id'],
                            userId: auth()->id() ?? 1,
                            motivo: $data['motivo']
                        );

                        return ActivoAsignacion::where('activo_id', $owner->getKey())
                            ->whereNull('fecha_fin')
                            ->firstOrFail();
                    }),
            ])
            ->actions([
                Action::make('desvincular')
                    ->label('Desvincular / Retirar')
                    ->icon(Heroicon::MinusCircle)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Desvincular activo y asignar destino')
                    ->form([
                        Select::make('asignable_type')
                            ->label('Tipo de Destino')
                            ->placeholder('Seleccione tipo de destino')
                            ->options([
                                Habitacion::class => 'Habitación',
                                Ubicacion::class => 'Ubicación / Bodega',
                                Espacio::class => 'Espacio / Área Común',
                            ])
                            ->reactive()
                            ->native(false)
                            ->required()
                            ->afterStateUpdated(fn (callable $set) => $set('asignable_id', null)),

                        Select::make('asignable_id')
                            ->label('Destino Específico')
                            ->placeholder('Primero seleccione un tipo de destino')
                            ->options(function (callable $get) {
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
                            ->placeholder('Ej. Retiro temporal del activo de la habitación'),
                    ])
                    ->action(function (ActivoAsignacion $record, array $data): void {
                        app(AsignarActivo::class)->execute(
                            activoId: $record->activo_id,
                            asignableType: $data['asignable_type'],
                            asignableId: (int) $data['asignable_id'],
                            userId: auth()->id() ?? 1,
                            motivo: $data['motivo']
                        );
                    })
                    ->visible(fn (ActivoAsignacion $record) => $record->estado === EstadoAsignacion::Vigente),
            ]);
    }
}
