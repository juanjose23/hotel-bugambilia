<?php

namespace App\Filament\Resources\Compras\Recepciones\Pages;

use App\Enums\Compras\EstadoRecepcion;
use App\Filament\Resources\Compras\Recepciones\Actions\RecepcionEstadoActions;
use App\Filament\Resources\Compras\Recepciones\RecepcionResource;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\RecepcionItem;
use App\UseCases\Inventario\Recepciones\Mutations\ConvertirItemAUbicaciones;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewRecepcion extends ViewRecord
{
    protected static string $resource = RecepcionResource::class;

    /** @return array<int, Action | ActionGroup> */
    protected function getHeaderActions(): array
    {
        return [
            ...RecepcionEstadoActions::make(),
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

                            return $record->items->mapWithKeys(fn ($item) => [
                                $item->id => "{$item->producto->nombre} (Recibido: {$item->cantidad_recibida})",
                            ])->toArray();
                        })
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            $item = RecepcionItem::find($state);
                            if ($item) {
                                $set('nombre_prefijo', $item->producto->nombre);
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
                        $creadas = app(ConvertirItemAUbicaciones::class)->execute($data);

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

                    return auth()->user() && auth()->user()->can('Compras:ImprimirRecepcion');
                }),
        ];
    }
}
