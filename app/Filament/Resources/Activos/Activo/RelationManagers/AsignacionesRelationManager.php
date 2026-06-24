<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo\RelationManagers;

use App\Filament\Resources\Shared\Concerns\HasActivoAsignaciones;
use App\Models\Activos\ActivoAsignacion;
use App\UseCases\Activos\Mutations\Asignacion\AsignarActivo;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class AsignacionesRelationManager extends RelationManager
{
    use HasActivoAsignaciones;

    protected static string $relationship = 'asignaciones';

    protected static ?string $title = 'Ubicación y Asignaciones';

    protected static ?string $label = 'Asignación';

    protected static ?string $pluralLabel = 'Asignaciones';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            ...$this->getAsignacionDestinoFields(),

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

                        $ownerKeyVal = $owner->getKey() ?? 0;
                        $ownerKey = is_numeric($ownerKeyVal) ? (int) $ownerKeyVal : 0;

                        $asignableTypeVal = $data['asignable_type'] ?? '';
                        $asignableType = is_string($asignableTypeVal) ? $asignableTypeVal : '';

                        $asignableIdVal = $data['asignable_id'] ?? 0;
                        $asignableId = is_numeric($asignableIdVal) ? (int) $asignableIdVal : 0;

                        $motivoVal = $data['motivo'] ?? null;
                        $motivo = is_string($motivoVal) ? $motivoVal : null;

                        app(AsignarActivo::class)->execute(
                            activoId: $ownerKey,
                            asignableType: $asignableType,
                            asignableId: $asignableId,
                            userId: (int) auth()->id(),
                            motivo: $motivo
                        );

                        return ActivoAsignacion::where('activo_id', $ownerKey)
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
