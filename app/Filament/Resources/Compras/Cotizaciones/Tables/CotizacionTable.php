<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Tables;

use App\BusinessLogic\Compras\VerificarEdicionCotizacion;
use App\BusinessLogic\Compras\VerificarSolicitudBloqueada;
use App\Enums\Compras\EstadoCotizacion;
use App\Events\Compras\GanadorSeleccionado;
use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\OrdenesCompra\OrdenCompraResource;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Filament\Shared\Concerns\TieneAccionesImprimirExportar;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Interactors\Compras\Cotizaciones\RechazarCotizacion;
use App\Interactors\Compras\OrdenesCompra\GenerarOrdenDesdeCotizacion;
use App\Repository\Models\Compras\Cotizacion;
use App\Repository\Models\Compras\Proveedor;
use DomainException;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CotizacionTable
{
    use InyectaDesdeContenedor, TieneAccionesImprimirExportar;

    public function __construct(
        private readonly GenerarOrdenDesdeCotizacion $generarOrdenDesdeCotizacion,
        private readonly VerificarSolicitudBloqueada $verificarSolicitudBloqueada,
        private readonly VerificarEdicionCotizacion $verificarEdicionCotizacion,
        private readonly RechazarCotizacion $rechazarCotizacion,
    ) {}

    public static function configure(Table $table): Table
    {
        return static::make()->doConfigure($table);
    }

    private function doConfigure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('solicitud.codigo')
                    ->label('Solicitud')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Cotizacion $record) => "ID: $record->solicitud_id"),

                TextColumn::make('proveedor.codigo')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Cotizacion $record) => ($record->proveedor && $record->proveedor->persona && $record->proveedor->persona->personaJuridica)
                            ? $record->proveedor->persona->personaJuridica->razon_social
                            : (($record->proveedor && $record->proveedor->persona) ? $record->proveedor->persona->primer_nombre : '')
                    ),

                TextColumn::make('fecha_cotizacion')
                    ->label('Fecha')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('dias_entrega')
                    ->label('Entrega')
                    ->suffix(' días')
                    ->alignCenter(),

                TextColumn::make('total')
                    ->label('Total')
                    ->money(fn (Cotizacion $record) => $record->moneda ? $record->moneda->codigo : 'USD')
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                IconColumn::make('es_elegida')
                    ->label('Ganadora')
                    ->boolean()
                    ->trueIcon(Heroicon::CheckBadge)
                    ->trueColor('success')
                    ->alignCenter(),

                EstadoBadgeColumn::make(EstadoCotizacion::class)
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('creadaPor.name')
                    ->label('Registrada por')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('elegidaPor.name')
                    ->label('Elegida por')
                    ->toggleable(isToggledHiddenByDefault: true),

                FechaStandardColumn::make('elegida_en', 'Fecha Selección')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('proveedor_id')
                    ->label('Proveedor')
                    ->relationship('proveedor', 'codigo')
                    ->getOptionLabelFromRecordUsing(fn (Proveedor $record) => "[$record->codigo] - ".(
                        ($record->persona && $record->persona->personaJuridica)
                            ? $record->persona->personaJuridica->razon_social
                            : ($record->persona ? $record->persona->primer_nombre : '')
                    )
                    )
                    ->searchable()
                    ->preload(),

                SelectFilter::make('solicitud_id')
                    ->relationship('solicitud', 'codigo')
                    ->label('Solicitud'),

                SelectFilter::make('es_elegida')
                    ->label('Solo Ganadoras')
                    ->options([
                        '1' => 'Sí',
                        '0' => 'No',
                    ]),

                FiltroEstado::make(EstadoCotizacion::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    Action::make('generarOrden')
                        ->label('Generar Orden de Compra')
                        ->icon(Heroicon::ShoppingCart)
                        ->color('primary')
                        ->visible(fn (?Cotizacion $record) => $record !== null
                            && ($record->es_elegida || $record->items_elegidos_count > 0)
                            && $record->solicitud !== null
                            && ! $this->verificarSolicitudBloqueada->estaBloqueada($record->solicitud)
                        )
                        ->action(action: function (Cotizacion $record) {
                            try {
                                $orden = $this->generarOrdenDesdeCotizacion->ejecutar($record->id);

                                GanadorSeleccionado::dispatch($record);

                                Notification::make()
                                    ->title('Orden de Compra Generada')
                                    ->body("Se ha creado la orden $orden->codigo.")
                                    ->success()
                                    ->send();

                                return redirect(OrdenCompraResource::getUrl('edit', ['record' => $orden]));
                            } catch (Exception $e) {
                                Notification::make()
                                    ->title('Error al generar la orden')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();

                                return \Illuminate\Log\log('Error', (array) $e->getMessage());
                            }
                        }),

                    EditAction::make()
                        ->visible(fn (?Cotizacion $record) => $record !== null && $this->verificarEdicionCotizacion->puedeEditar($record)),

                    Action::make('view')
                        ->label('Ver Cotización')
                        ->icon(Heroicon::Eye)
                        ->color('gray')
                        ->url(fn (Cotizacion $record) => CotizacionResource::getUrl('view', ['record' => $record]))
                        ->visible(fn (?Cotizacion $record) => $record !== null && auth()->user()?->can('View:Cotizacion')),

                    Action::make('rechazar')
                        ->label('Rechazar Cotización')
                        ->icon(Heroicon::XCircle)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Rechazar cotización')
                        ->modalDescription('¿Está seguro de rechazar esta cotización?')
                        ->schema([
                            Textarea::make('motivo')
                                ->label('Motivo de rechazo')
                                ->required()
                                ->placeholder('Indique la razón del rechazo...'),
                        ])
                        ->visible(fn (?Cotizacion $record) => $record !== null && $record->estado === EstadoCotizacion::Activa)
                        ->action(function (Cotizacion $record, array $data) {
                            try {
                                $this->rechazarCotizacion->ejecutar($record, $data['motivo']);

                                Notification::make()
                                    ->title('Cotización Rechazada')
                                    ->success()
                                    ->send();
                            } catch (DomainException $e) {

                                Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Action::make('verComparativa')
                        ->label('Ver Comparativa')
                        ->icon(Heroicon::ArrowsRightLeft)
                        ->color('success')
                        ->url(fn (?Cotizacion $record) => $record ? CotizacionResource::getUrl('comparativa', ['solicitud_id' => $record->solicitud_id]) : '')
                        ->visible(fn () => auth()->user()?->can('Compras:ViewComparativaCotizaciones') ?? false),

                    self::makeImprimirAction('admin.compras.reportes.comparativa', 'Compras:ImprimirComparativa', 'Imprimir Comparativo', fn (?Cotizacion $record) => $record ? route('admin.compras.reportes.comparativa', ['solicitud' => $record->solicitud_id]) : ''),

                    self::makeImprimirAction('admin.compras.reportes.cotizacion', 'Compras:ImprimirCotizacion', 'Imprimir Cotización'),

                ])
                    ->icon(Heroicon::EllipsisVertical)
                    ->tooltip('Más opciones'),
            ]);
    }
}
