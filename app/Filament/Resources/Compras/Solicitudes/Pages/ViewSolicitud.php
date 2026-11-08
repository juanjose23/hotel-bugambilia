<?php

namespace App\Filament\Resources\Compras\Solicitudes\Pages;

use App\Enums\Compras\EstadoSolicitud;
use App\Filament\Pages\Restaurante\MateriaPrimaCocina;
use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\Solicitudes\SolicitudResource;
use App\Interactors\Restaurante\Cocina\DespacharSolicitudAbastecimientoCocina;
use App\Interactors\Restaurante\Cocina\ResolverSolicitudAbastecimientoCocina;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Compras\Solicitud;
use DomainException;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class ViewSolicitud extends ViewRecord
{
    protected static string $resource = SolicitudResource::class;

    public function getRecord(): Solicitud
    {
        /** @var Solicitud $record */
        $record = parent::getRecord();
        $record->loadMissing(['items.productoVariante', 'items.producto']);

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('imprimir')
                ->label('Imprimir')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->url(fn (): string => route('admin.compras.reportes.solicitud', ['solicitud' => $this->getRecord()->codigo]))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->can('Compras:ImprimirSolicitud') ?? false),

            Action::make('resolverInventarioCocina')
                ->label('Resolver con Inventario')
                ->icon(Heroicon::ArrowsRightLeft)
                ->color('success')
                ->modalHeading('Confirmar retiro para cocina')
                ->modalDescription('Verifica el lote y la cantidad en cada bodega. Para cambiar de lote, usa Despacho Manual a Cocina.')
                ->fillForm(function (): array {
                    $plan = app(ResolverSolicitudAbastecimientoCocina::class)->plan($this->getRecord());

                    return [
                        'confirmacion' => $plan['confirmacion'],
                        'retiro' => implode("\n", [...array_column($plan['filas'], 'texto'), ...$plan['faltantes']]),
                    ];
                })
                ->schema([
                    Hidden::make('confirmacion')->required(),
                    Textarea::make('retiro')->label('Lista de retiro y faltantes')->rows(8)->disabled()->dehydrated(false),
                ])
                ->modalSubmitActionLabel('Confirmar retiro y despacho')
                ->action(function (array $data): void {
                    $record = $this->getRecord();

                    try {
                        app(ResolverSolicitudAbastecimientoCocina::class)->ejecutar(
                            solicitud: $record,
                            usuarioId: auth()->id() !== null ? (int) auth()->id() : null,
                            confirmacion: (string) $data['confirmacion'],
                        );
                    } catch (DomainException $exception) {
                        Notification::make()
                            ->title('No se pudo completar el despacho')
                            ->body($exception->getMessage())
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()->title('Solicitud resuelta')->body('Revise la notificación y las notas para ver origen, destino y cantidad trasladada.')->success()->send();
                    $this->refreshFormData(['notas']);
                })
                ->visible(fn (): bool => $this->getRecord()->estado === EstadoSolicitud::Aprobada
                    && (auth()->user()?->can('Inventario:ResolverAbastecimientoCocina') ?? false)),

            ActionGroup::make([
                Action::make('crearCotizacion')
                    ->label('Crear Cotización')
                    ->icon(Heroicon::ClipboardDocumentCheck)
                    ->color('warning')
                    ->url(fn (): string => CotizacionResource::getUrl('create', [
                        'solicitud_id' => $this->getRecord()->id,
                    ]))
                    ->visible(fn (): bool => $this->getRecord()->estado === EstadoSolicitud::Aprobada),

                Action::make('despacharCocina')
                    ->label('Despacho Manual a Cocina')
                    ->icon(Heroicon::ArchiveBoxArrowDown)
                    ->color('success')
                    ->modalHeading('Despachar abastecimiento a cocina')
                    ->modalDescription('Coteja el código de cada lote con su etiqueta y confirma las cantidades que retiraste de la bodega.')
                    ->schema(fn (): array => [
                        Select::make('ubicacion_origen_id')
                            ->label('Bodega origen')
                            ->live()
                            ->afterStateUpdated(function (Set $set, mixed $state): void {
                                $despacho = app(DespacharSolicitudAbastecimientoCocina::class);
                                foreach ($this->getRecord()->items as $item) {
                                    $set('lotes_'.$item->id, is_numeric($state) ? $despacho->sugerencia($item, (int) $state) : []);
                                }
                            })
                            ->options(fn (): array => self::opcionesUbicaciones())
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->required(),
                        Select::make('ubicacion_destino_id')
                            ->label('Cocina destino')
                            ->options(fn (): array => self::opcionesUbicacionesCocina())
                            ->searchable()
                            ->preload()
                            ->native(false),
                        ...$this->camposLotesDespacho(),
                        TextInput::make('motivo_lotes')->label('Motivo si cambiaste los lotes sugeridos')->maxLength(255),
                    ])
                    ->modalSubmitActionLabel('Confirmar retiro y despacho')
                    ->action(function (array $data): void {
                        $record = $this->getRecord();

                        $lotes = [];
                        foreach ($record->items as $item) {
                            $lotes[$item->id] = $data['lotes_'.$item->id] ?? [];
                        }

                        try {
                            app(DespacharSolicitudAbastecimientoCocina::class)->ejecutar(
                                solicitud: $record,
                                ubicacionOrigenId: (int) $data['ubicacion_origen_id'],
                                ubicacionDestinoId: isset($data['ubicacion_destino_id']) && is_numeric($data['ubicacion_destino_id'])
                                    ? (int) $data['ubicacion_destino_id']
                                    : null,
                                usuarioId: auth()->id() !== null ? (int) auth()->id() : null,
                                lotes: $lotes,
                                motivo: $data['motivo_lotes'] ?? null,
                            );
                        } catch (DomainException $exception) {
                            Notification::make()->title('No se pudo despachar')->body($exception->getMessage())->danger()->send();

                            return;
                        }

                        Notification::make()->title('Abastecimiento despachado')->body('El stock fue trasladado hacia cocina.')->success()->send();
                        $this->refreshFormData(['notas']);
                    })
                    ->visible(fn (): bool => $this->getRecord()->estado === EstadoSolicitud::Aprobada
                        && (auth()->user()?->can('Inventario:ResolverAbastecimientoCocina') ?? false)),

                Action::make('transformarMateriaPrima')
                    ->label('Transformar Materia Prima')
                    ->icon(Heroicon::Beaker)
                    ->url(fn (): string => MateriaPrimaCocina::getUrl())
                    ->visible(fn (): bool => $this->getRecord()->estado === EstadoSolicitud::Aprobada),
            ])
                ->label('Más opciones')
                ->icon(Heroicon::EllipsisVertical)
                ->color('gray')
                ->visible(fn (): bool => $this->getRecord()->estado === EstadoSolicitud::Aprobada),

            EditAction::make()
                ->visible(function (): bool {
                    /** @var Solicitud $record */
                    $record = $this->getRecord();

                    return $record->estado === EstadoSolicitud::Borrador;
                }),
        ];
    }

    /** @return list<Repeater> */
    private function camposLotesDespacho(): array
    {
        $despacho = app(DespacharSolicitudAbastecimientoCocina::class);
        $campos = [];
        foreach ($this->getRecord()->items as $item) {
            $cantidad = $despacho->cantidad($item);
            if ($item->producto_variante_id === null || $cantidad <= 0) {
                continue;
            }

            $nombre = $item->productoVariante->nombre_variante ?? $item->producto->nombre ?? 'Producto';
            $campos[] = Repeater::make('lotes_'.$item->id)
                ->label("{$nombre} · Retirar {$cantidad}")
                ->visible(fn (Get $get): bool => is_numeric($get('ubicacion_origen_id')))
                ->minItems(1)
                ->defaultItems(0)
                ->reorderable(false)
                ->schema([
                    Select::make('stock_id')
                        ->label('Lote y vencimiento')
                        ->options(function (Get $get) use ($despacho, $item): array {
                            $origen = $get('../../ubicacion_origen_id');

                            return is_numeric($origen) ? $despacho->opciones($item, (int) $origen) : [];
                        })
                        ->searchable()
                        ->required(),
                    TextInput::make('cantidad')->label('Cantidad retirada')->numeric()->minValue(0.0001)->required(),
                ])
                ->columns(2);
        }

        return $campos;
    }

    /** @return array<int, string> */
    private static function opcionesUbicaciones(): array
    {
        /** @var array<int, string> $opciones */
        $opciones = Ubicacion::query()
            ->where('estado', 1)
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();

        return $opciones;
    }

    /** @return array<int, string> */
    private static function opcionesUbicacionesCocina(): array
    {
        /** @var array<int, string> $opciones */
        $opciones = Ubicacion::query()
            ->where('estado', 1)
            ->where(function ($query): void {
                $query
                    ->where('nombre', 'Cocina Restaurante')
                    ->orWhere('nombre', 'Cocina')
                    ->orWhere('nombre', 'like', '%Cocina%')
                    ->orWhere('tipo', 'cocina');
            })
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();

        return $opciones;
    }
}
