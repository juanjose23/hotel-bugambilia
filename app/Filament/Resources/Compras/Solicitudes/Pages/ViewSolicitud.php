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
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

class ViewSolicitud extends ViewRecord
{
    protected static string $resource = SolicitudResource::class;

    public function getRecord(): Solicitud
    {
        /** @var Solicitud $record */
        $record = parent::getRecord();
        $record->loadMissing('items.productoVariante');

        return $record;
    }

    protected function getHeaderActions(): array
    {
        return [

            Action::make('imprimir')
                ->label('Imprimir')
                ->icon(Heroicon::Printer)
                ->color('gray')
                ->url(fn (): string => route('admin.compras.reportes.solicitud', $this->getRecord()))
                ->openUrlInNewTab()
                ->visible(fn () => auth()->user()?->can('Compras:ImprimirSolicitud') ?? false),

            Action::make('resolverInventarioCocina')
                ->label('Resolver con Inventario')
                ->icon(Heroicon::ArrowsRightLeft)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Resolver solicitud con inventario interno')
                ->modalDescription('El sistema buscará stock disponible en bodegas y trasladará las cantidades aprobadas hacia cocina. Si no alcanza, deberá crear cotización u orden de compra.')
                ->action(function (): void {
                    $record = $this->getRecord();

                    try {
                        app(ResolverSolicitudAbastecimientoCocina::class)->ejecutar(
                            solicitud: $record,
                            usuarioId: auth()->id() !== null ? (int) auth()->id() : null,
                        );
                    } catch (DomainException $exception) {
                        Notification::make()
                            ->title('Debe ir a compra')
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
                    ->modalDescription('Use esta opción solo si necesita seleccionar una bodega específica. La opción recomendada es Resolver con Inventario.')
                    ->schema([
                        Select::make('ubicacion_origen_id')
                            ->label('Bodega origen')
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
                    ])
                    ->action(function (array $data): void {
                        $record = $this->getRecord();

                        try {
                            app(DespacharSolicitudAbastecimientoCocina::class)->ejecutar(
                                solicitud: $record,
                                ubicacionOrigenId: (int) $data['ubicacion_origen_id'],
                                ubicacionDestinoId: isset($data['ubicacion_destino_id']) && is_numeric($data['ubicacion_destino_id'])
                                    ? (int) $data['ubicacion_destino_id']
                                    : null,
                                usuarioId: auth()->id() !== null ? (int) auth()->id() : null,
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
