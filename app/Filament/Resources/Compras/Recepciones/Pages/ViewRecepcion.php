<?php

namespace App\Filament\Resources\Compras\Recepciones\Pages;

use App\Enums\Compras\EstadoRecepcion;
use App\Filament\Resources\Compras\Recepciones\Actions\RecepcionEstadoActions;
use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\Interactors\Inventario\Recepciones\ConvertirItemAUbicaciones\ConvertirItemAUbicaciones;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Compras\RecepcionItem;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewRecepcion extends ViewRecord
{
    protected ConvertirItemAUbicaciones $convertirItemAUbicaciones;

    public function boot(ConvertirItemAUbicaciones $convertirItemAUbicaciones): void
    {
        $this->convertirItemAUbicaciones = $convertirItemAUbicaciones;
    }

    protected static string $resource = RecepcionResource::class;

    /** @return array<int, Action | ActionGroup> */
    protected function getHeaderActions(): array
    {
        return [
            ...RecepcionEstadoActions::acciones(),
            Action::make('convertirUbicacion')
                ->label('Convertir a Estructura Física')
                ->icon('heroicon-o-squares-plus')
                ->color('success')
                ->schema([
                    Select::make('recepcion_item_id')
                        ->label('Seleccionar Ítem Recibido')
                        ->options(function () {
                            /** @var RecepcionCompra $record */
                            $record = $this->getRecord();

                            $record->load('items.producto', 'items.variante', 'items.unidadMedida');

                            return $record->items->mapWithKeys(fn ($item) => [
                                $item->id => ($item->producto ? $item->producto->nombre : 'N/A').' (Recibido: '.$item->cantidad_recibida.')',
                            ])->toArray();
                        })
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            $item = RecepcionItem::find((int) $state);
                            if ($item) {
                                $item->load('producto');
                                $set('nombre_prefijo', $item->producto->nombre ?? '');
                                $set('cantidad_a_convertir', (int) $item->cantidad_recibida);
                            }
                        }),
                    TextInput::make('nombre_prefijo')
                        ->label('Nombre / Prefijo de la estructura')
                        ->placeholder('Ej. Estante Metálico, Gabinete, Caja')
                        ->required(),
                    TextInput::make('cantidad_a_convertir')
                        ->label('Cantidad de unidades a crear')
                        ->numeric()
                        ->default(1)
                        ->required(),
                    Select::make('parent_id')
                        ->label('Ubicación Padre (Almacén o zona donde se colocará)')
                        ->options(fn () => Ubicacion::pluck('nombre', 'id')->toArray())
                        ->searchable()
                        ->nullable()
                        ->helperText('Opcional. E.g. Almacén General o Bodega Central'),
                    TextInput::make('niveles_por_unidad')
                        ->label('Número de niveles por estructura')
                        ->numeric()
                        ->default(3)
                        ->required()
                        ->helperText('E.g. Si crea un estante, cuántos niveles tendrá'),
                    TextInput::make('posiciones_por_nivel')
                        ->label('Número de posiciones/compartimientos por nivel')
                        ->numeric()
                        ->default(0)
                        ->required()
                        ->helperText('E.g. Si desea subdividir cada nivel, indique cuántas posiciones tendrá. Ponga 0 para omitir.'),
                ])
                ->action(function (array $data) {
                    try {
                        /** @var array{recepcion_item_id: int, parent_id: int|null, nombre_prefijo: string, cantidad_a_convertir: int, niveles_por_unidad: int, posiciones_por_nivel: int} $data */
                        $data['recepcion_item_id'] = intval($data['recepcion_item_id']);
                        $data['cantidad_a_convertir'] = intval($data['cantidad_a_convertir']);
                        $data['niveles_por_unidad'] = intval($data['niveles_por_unidad']);
                        $data['posiciones_por_nivel'] = intval($data['posiciones_por_nivel']);
                        $data['parent_id'] = $data['parent_id'] !== null ? intval($data['parent_id']) : null;
                        $data['nombre_prefijo'] = (string) $data['nombre_prefijo'];
                        /** @var array<int, Ubicacion> $creadas */
                        $creadas = $this->convertirItemAUbicaciones->execute($data);

                        $count = count(array_filter($creadas, fn ($u) => $u->tipo === 'estante'));

                        Notification::make()
                            ->success()
                            ->title('Conversión Exitosa')
                            ->body("Se han creado {$count} estructuras físicas con sus respectivos niveles y posiciones jerárquicas.")
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->danger()
                            ->title('Error al convertir')
                            ->body($e->getMessage())
                            ->send();
                    }
                })
                ->visible(function () {
                    /** @var RecepcionCompra $record */
                    $record = $this->getRecord();

                    return in_array($record->estado->value, [
                        EstadoRecepcion::Completa->value,
                        EstadoRecepcion::Parcial->value,
                    ]);
                }),
            Action::make('imprimir')
                ->label('Imprimir')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(function () {
                    /** @var RecepcionCompra $record */
                    $record = $this->getRecord();

                    return route('reporte.recepcion', $record);
                })
                ->openUrlInNewTab()
                ->visible(function () {
                    /** @var RecepcionCompra $record */
                    $record = $this->getRecord();

                    return auth()->user()?->can('Compras:ImprimirRecepcion') ?? false;
                }),
        ];
    }
}
