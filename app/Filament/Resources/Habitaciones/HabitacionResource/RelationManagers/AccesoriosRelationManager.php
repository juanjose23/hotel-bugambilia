<?php

// app/Filament/Resources/Habitaciones/HabitacionResource/RelationManagers/AccesoriosRelationManager.php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\RelationManagers;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Models\Activos\Activo;
use App\Models\Activos\ActivoAsignacion;
use App\Models\Catalogos\Ubicacion;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\UseCases\Activos\Mutations\AsignarActivo;
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

class AccesoriosRelationManager extends RelationManager
{
    protected static string $relationship = 'asignacionesActivos';

    protected static ?string $title = 'Accesorios y Mobiliario';

    protected static ?string $label = 'Accesorio';

    protected static ?string $pluralLabel = 'Accesorios y Mobiliario';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('activo_id')
                    ->label('Activo Fijo / Accesorio')
                    ->placeholder('Seleccione el activo a asignar')
                    ->options(function () {
                        return Activo::where('estado', EstadoActivo::Activo->value)
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
                    ->default('Asignado a habitación desde panel de control')
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
                    ->label('Asignar Accesorio')
                    ->icon(Heroicon::Plus)
                    ->using(function (array $data): Model {
                        $owner = $this->getOwnerRecord();
                        app(AsignarActivo::class)->execute(
                            activoId: (int) $data['activo_id'],
                            asignableType: $owner::class,
                            asignableId: $owner->getKey(),
                            userId: auth()->id() ?? 1,
                            motivo: $data['motivo']
                        );

                        return ActivoAsignacion::where('activo_id', $data['activo_id'])
                            ->whereNull('fecha_fin')
                            ->firstOrFail();
                    }),
            ])
            ->actions([
                Action::make('retirar')
                    ->label('Retirar / Desasignar')
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
                        $record->update([
                            'fecha_fin' => now()->toDateString(),
                            'estado' => EstadoAsignacion::Cerrada,
                        ]);

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
