<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Tables;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Filament\Resources\Habitaciones\HabitacionResource\HabitacionResource;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Habitaciones\ClonarHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class HabitacionTable
{
    public function __construct(
        private readonly ClonarHabitacion $clonarHabitacion,
    ) {}

    public function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['detalle', 'categoria', 'ubicacion']))
            ->columns([
                TextColumn::make('codigo')
                    ->label('Código')
                    ->badge()
                    ->color('primary')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('numero')
                    ->label('Número')
                    ->sortable()
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('nombre')
                    ->label('Nombre')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('categoria.nombre')
                    ->label('Categoría')
                    ->sortable(),

                TextColumn::make('ubicacion.nombre')
                    ->label('Ubicación')
                    ->sortable(),

                TextColumn::make('detalle.capacidad_adultos')
                    ->label('Cap. Adultos')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('detalle.capacidad_ninos')
                    ->label('Cap. Niños')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('detalle.medidas')
                    ->label('Medidas (m²)')
                    ->alignCenter()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => $state !== null && $state !== '' ? number_format((float) $state, 2) : null),

                EstadoBadgeColumn::make(EstadoEspacio::class)
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Creado')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('codigo')
            ->filters([
                FiltroEstado::make(EstadoEspacio::class),
                SelectFilter::make('categoria_id')
                    ->label('Categoría')
                    ->relationship('categoria', 'nombre'),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('ver')
                        ->label('Ver')
                        ->icon('heroicon-o-eye')
                        ->color('gray')
                        ->url(fn ($record) => HabitacionResource::getUrl('view', ['record' => $record])),

                    Action::make('editar')
                        ->label('Editar')
                        ->icon('heroicon-o-pencil-square')
                        ->url(fn ($record) => HabitacionResource::getUrl('edit', ['record' => $record])),

                    Action::make('clonar')
                        ->label('Clonar')
                        ->icon('heroicon-o-document-duplicate')
                        ->color('info')
                        ->modalHeading(fn (Habitacion $record) => "Clonar habitación: {$record->nombre}")
                        ->modalDescription('Se copiarán la categoría, detalle, servicios, precios, políticas y la plantilla de stock. Los activos fijos (TV, AC, minibar) deberán asignarse manualmente a la nueva habitación.')
                        ->modalWidth('lg')
                        ->schema([
                            TextInput::make('nuevo_numero')
                                ->label('Nuevo número de habitación')
                                ->placeholder('Ej. 102')
                                ->integer()
                                ->minValue(1)
                                ->required()
                                ->unique(
                                    table: 'habitaciones',
                                    column: 'numero',
                                    ignorable: fn () => null,
                                )
                                ->helperText('Debe ser un número único no usado por ninguna habitación.'),

                            TextInput::make('nuevo_nombre')
                                ->label('Nombre de la nueva habitación (opcional)')
                                ->placeholder('Ej. Suite Presidencial 102')
                                ->maxLength(150)
                                ->helperText('Si se deja vacío se usará "Habitación {número}".'),
                        ])
                        ->action(function (array $data, Habitacion $record) {
                            try {
                                $nueva = $this->clonarHabitacion->execute(
                                    origen: $record,
                                    nuevoNumero: (int) $data['nuevo_numero'],
                                    nuevoNombre: filled($data['nuevo_nombre']) ? $data['nuevo_nombre'] : null,
                                );

                                Notification::make()
                                    ->title('Habitación clonada exitosamente')
                                    ->body("Se creó la habitación {$nueva->codigo} — {$nueva->nombre}. Estado: Mantenimiento. Recuerde asignar los activos fijos y surtir el stock.")
                                    ->success()
                                    ->send();

                            } catch (\InvalidArgumentException $e) {
                                Notification::make()
                                    ->title('No se pudo clonar la habitación')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    DeleteAction::make(),
                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Acciones'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
