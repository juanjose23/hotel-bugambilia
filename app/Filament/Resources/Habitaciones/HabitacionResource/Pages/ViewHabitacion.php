<?php

declare(strict_types=1);

namespace App\Filament\Resources\Habitaciones\HabitacionResource\Pages;

use App\Filament\Resources\Habitaciones\HabitacionResource\HabitacionResource;
use App\Interactors\Habitaciones\AsignarPackAHabitacion;
use App\Interactors\Habitaciones\ClonarHabitacion;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Inventario\ProductoKit;
use App\Repository\Queries\Shared\ObtenerOpcionesColaborador;
use App\Support\CachedOptions;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class ViewHabitacion extends ViewRecord
{
    protected ClonarHabitacion $clonarHabitacion;

    protected ObtenerOpcionesColaborador $obtenerOpcionesColaborador;

    protected AsignarPackAHabitacion $asignarPackAHabitacion;

    public function boot(ClonarHabitacion $clonarHabitacion, ObtenerOpcionesColaborador $obtenerOpcionesColaborador, AsignarPackAHabitacion $asignarPackAHabitacion): void
    {
        $this->clonarHabitacion = $clonarHabitacion;
        $this->obtenerOpcionesColaborador = $obtenerOpcionesColaborador;
        $this->asignarPackAHabitacion = $asignarPackAHabitacion;
    }

    protected static string $resource = HabitacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('clonar')
                ->label('Clonar Habitación')
                ->icon(Heroicon::DocumentDuplicate)
                ->color('info')
                ->modalHeading(fn (Habitacion $record) => "Clonar: {$record->nombre}")
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
                            ->body("Se creó la habitación {$nueva->codigo} — {$nueva->nombre}. Estado: Mantenimiento.")
                            ->success()
                            ->send();

                        $this->redirect(
                            HabitacionResource::getUrl('view', ['record' => $nueva->id])
                        );

                    } catch (\InvalidArgumentException $e) {
                        Notification::make()
                            ->title('No se pudo clonar')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('surtir_pack_rapido')
                ->label('Surtir Pack de Blancos')
                ->icon(Heroicon::ArchiveBoxArrowDown)
                ->color('success')
                ->modalHeading('Surtir Pack a la Habitación')
                ->modalDescription('Seleccione el pack de productos y la bodega de origen para surtir esta habitación.')
                ->schema([
                    Select::make('producto_pack_id')
                        ->label('Pack / Kit')
                        ->placeholder('Seleccione un pack')
                        ->options(
                            CachedOptions::productosKit()
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, $state): void {
                            $items = ProductoKit::with('variante')
                                ->where('producto_padre_id', $state)
                                ->get();
                            $preview = [];
                            foreach ($items as $item) {
                                $variante = $item->variante;
                                $preview[] = [
                                    'variante' => $variante !== null ? $variante->nombre_variante : 'N/A',
                                    'cantidad' => $item->cantidad,
                                ];
                            }
                            $set('items_preview', $preview);
                        }),

                    Select::make('bodega_origen_id')
                        ->label('Bodega de origen')
                        ->placeholder('Seleccione la bodega')
                        ->options(
                            CachedOptions::ubicacionesAlmacen()
                        )
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('cantidad_packs')
                        ->label('Cantidad de packs')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->default(1),

                    Select::make('colaborador_id')
                        ->label('Colaborador que llevó el pack')
                        ->options($this->obtenerOpcionesColaborador->ejecutar())
                        ->searchable()
                        ->preload(),

                    Repeater::make('items_preview')
                        ->label('Items incluidos en el pack')
                        ->schema([
                            TextInput::make('variante')->disabled(),
                            TextInput::make('cantidad')->disabled(),
                        ])
                        ->disabled()
                        ->columns(2)
                        ->defaultItems(0)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ])
                ->action(function (array $data, Habitacion $record, Action $action): void {
                    try {
                        $this->asignarPackAHabitacion->execute(
                            destinoId: $record->id,
                            productoPackId: (int) $data['producto_pack_id'],
                            bodegaOrigenId: (int) $data['bodega_origen_id'],
                            cantidadPacks: (float) $data['cantidad_packs'],
                            creadoPorId: (int) auth()->id(),
                            referencia: null,
                            destinoTipo: 'habitacion',
                            colaboradorId: $data['colaborador_id'] ? (int) $data['colaborador_id'] : null,
                        );

                        Notification::make()
                            ->title('Pack surtido exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al surtir pack')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                }),

            Action::make('imprimir_hoja')
                ->label('Imprimir Hoja de Habitación')
                ->icon(Heroicon::Printer)
                ->color('info')
                ->url(fn (Habitacion $record) => route('reporte.activos.hoja-habitacion.pdf', ['tipo' => 'habitacion', 'id' => $record->id]))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->can('Activos:ReporteHojaHabitacion') ?? false),
            EditAction::make(),
        ];
    }
}
