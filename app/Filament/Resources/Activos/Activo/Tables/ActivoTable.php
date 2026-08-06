<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo\Tables;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\EstadoAsignacion;
use App\Enums\Activos\TipoBaja;
use App\Enums\Activos\TipoMantenimiento;
use App\Filament\Shared\Columns\EstadoBadgeColumn;
use App\Filament\Shared\Filters\FiltroEstado;
use App\Filament\Shared\Forms\MonedaSelect;
use App\Filament\Shared\Forms\ProveedorSelect;
use App\Filament\Shared\Forms\UserSelect;
use App\Interactors\Activos\Gestion\AsignarActivo;
use App\Interactors\Activos\Gestion\DarDeBajaActivo;
use App\Interactors\Activos\Mantenimiento\EnviarAMantenimiento;
use App\Repository\Models\Activos\Activo;
use App\Repository\Models\Catalogos\Ubicacion;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Persistencia\Activos\ActivoAsignacionRepositorioInterface;
use App\Repository\Persistencia\Activos\ActivoRepositorioInterface;
use App\Repository\Queries\Catalogos\ObtenerUbicacionAlmacen;
use App\Support\CachedOptions;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup as TableActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class ActivoTable
{
    public function __construct(
        private AsignarActivo $asignarActivo,
        private EnviarAMantenimiento $enviarAMantenimiento,
        private DarDeBajaActivo $darDeBajaActivo,
        private ActivoRepositorioInterface $activoRepositorio,
        private ActivoAsignacionRepositorioInterface $asignacionRepositorio,
        private ObtenerUbicacionAlmacen $obtenerAlmacen,
    ) {}

    public function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['producto', 'variante', 'moneda', 'asignacionActiva.asignable']))
            ->columns([
                TextColumn::make('codigo_inventario')
                    ->label('Código')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                TextColumn::make('nombre_descriptivo')
                    ->label('Nombre / Descripción')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('producto.nombre')
                    ->label('Tipo de Activo')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('numero_serie')
                    ->label('Nro. Serie')
                    ->searchable()
                    ->placeholder('-')
                    ->toggleable(),

                TextColumn::make('ubicacion_formateada')
                    ->label('Ubicación Actual')
                    ->state(function (Activo $record): string {
                        $asignacion = $record->asignacionActiva;
                        if (! $asignacion || ! $asignacion->asignable) {
                            return 'Sin asignar';
                        }
                        $tipo = class_basename($asignacion->asignable_type);
                        $nombre = data_get($asignacion, 'asignable.nombre') ?? 'Sin nombre';
                        $prefijo = match ($tipo) {
                            'Habitacion' => 'Hab.',
                            'Ubicacion' => 'Ubic.',
                            'Espacio' => 'Esp.',
                            default => $tipo,
                        };

                        $nombreStr = is_scalar($nombre) ? (string) $nombre : 'Sin nombre';

                        return $prefijo.' '.$nombreStr;
                    })
                    ->placeholder('Sin asignar')
                    ->badge()
                    ->color(fn (Activo $record): string => match ($record->asignacionActiva?->asignable_type) {
                        Habitacion::class => 'success',
                        Ubicacion::class => 'info',
                        Espacio::class => 'warning',
                        default => 'gray',
                    }),

                EstadoBadgeColumn::make(EstadoActivo::class)
                    ->sortable(),

                TextColumn::make('fecha_adquisicion')
                    ->label('Adquirido')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('valor_libros')
                    ->label('Valor Neto')
                    ->state(function (Activo $record) {
                        if ($record->costo_adquisicion === null || $record->vida_util_meses === null) {
                            return null;
                        }
                        $costo = (float) $record->costo_adquisicion;
                        $vidaUtil = (int) $record->vida_util_meses;
                        $meses = now()->diffInMonths($record->fecha_adquisicion);
                        if ($meses >= $vidaUtil) {
                            return 0.00;
                        }
                        $depAcumulada = ($costo / $vidaUtil) * $meses;

                        return max(0.00, $costo - $depAcumulada);
                    })
                    ->money(fn ($record) => $record->moneda->codigo ?? 'USD')
                    ->toggleable(),
            ])
            ->filters([
                FiltroEstado::make(EstadoActivo::class),
                SelectFilter::make('producto_id')
                    ->label('Producto')
                    ->relationship('producto', 'nombre'),
                SelectFilter::make('ubicacion_tipo')
                    ->label('Tipo de Ubicación')
                    ->options([
                        Habitacion::class => 'Habitación',
                        Ubicacion::class => 'Ubicación / Bodega',
                        Espacio::class => 'Espacio / Área Común',
                    ]),
            ])
            ->groupedBulkActions([
                ViewAction::make(),
                EditAction::make(),
                TableActionGroup::make([
                    Action::make('asignar_activo')
                        ->label('Asignar / Mover')
                        ->icon(Heroicon::ArrowsRightLeft)
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Asignar o Trasladar Activo')
                        ->schema([
                            Select::make('asignable_type')
                                ->label('Tipo de Destino')
                                ->options([
                                    Habitacion::class => 'Habitación',
                                    Ubicacion::class => 'Ubicación / Bodega',
                                    Espacio::class => 'Espacio / Área Común',
                                ])
                                ->required()
                                ->live(),

                            Select::make('asignable_id')
                                ->label('Destino Específico')
                                ->options(function (Get $get) {
                                    $type = $get('asignable_type');
                                    if ($type === Habitacion::class) {
                                        return CachedOptions::habitaciones();
                                    }
                                    if ($type === Ubicacion::class) {
                                        return CachedOptions::ubicacionesAlmacen();
                                    }
                                    if ($type === Espacio::class) {
                                        return CachedOptions::espacios();
                                    }

                                    return [];
                                })
                                ->required()
                                ->searchable(),

                            TextInput::make('motivo')
                                ->label('Motivo del traslado')
                                ->placeholder('Ej. Cambio de TV defectuosa en habitación 101')
                                ->required(),
                        ])
                        ->action(function (array $data, Activo $record) {
                            try {
                                $this->asignarActivo->ejecutar(
                                    $record->id,
                                    $data['asignable_type'],
                                    (int) $data['asignable_id'],
                                    (int) auth()->id(),
                                    $data['motivo']
                                );

                                Notification::make()
                                    ->title('Activo Trasladado')
                                    ->body("El activo $record->codigo_inventario ha sido asignado a su nueva ubicación.")
                                    ->success()
                                    ->send();
                            } catch (Throwable $e) {

                                Notification::make()
                                    ->title('Error en Asignación')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (Activo $record) => $record->estado !== EstadoActivo::DadoDeBaja),

                    Action::make('enviar_mantenimiento')
                        ->label('Enviar a Mantenimiento')
                        ->icon(Heroicon::Wrench)
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Ingresar Activo a Mantenimiento')
                        ->schema([
                            Select::make('tipo')
                                ->label('Tipo de Mantenimiento')
                                ->options(TipoMantenimiento::class)
                                ->required(),

                            Textarea::make('descripcion')
                                ->label('Descripción del Daño / Intervención')
                                ->required()
                                ->placeholder('Describa el motivo del ingreso a taller'),

                            TextInput::make('costo')
                                ->label('Costo Estimado')
                                ->numeric()
                                ->placeholder('0.00'),

                            MonedaSelect::make(),

                            ProveedorSelect::make()
                                ->placeholder('Seleccionar proveedor (opcional)'),

                            Textarea::make('notas')
                                ->label('Notas Adicionales'),
                        ])
                        ->action(function (array $data, Activo $record) {
                            try {
                                $this->enviarAMantenimiento->execute(
                                    $record->id,
                                    $data['tipo'] instanceof TipoMantenimiento ? $data['tipo'] : TipoMantenimiento::from($data['tipo']),
                                    $data['descripcion'],
                                    (int) auth()->id(),
                                    $data['costo'] !== null ? (float) $data['costo'] : null,
                                    $data['moneda_id'] !== null ? (int) $data['moneda_id'] : null,
                                    $data['proveedor_id'] !== null ? (int) $data['proveedor_id'] : null,
                                    $data['notas'] ?: null
                                );
                            } catch (Throwable) {

                            }
                        })
                        ->visible(fn (Activo $record) => $record->estado === EstadoActivo::Activo),

                    Action::make('dar_baja')
                        ->label('Dar de Baja')
                        ->icon(Heroicon::Trash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Registrar Baja Definitiva de Activo')
                        ->schema([
                            Select::make('motivo_tipo')
                                ->label('Motivo de la Baja')
                                ->options(TipoBaja::class)
                                ->required(),

                            Textarea::make('motivo_detalle')
                                ->label('Detalles / Justificación Técnica')
                                ->required(),

                            TextInput::make('valor_residual')
                                ->label('Valor de Recuperación / Residuo')
                                ->numeric()
                                ->placeholder('0.00'),

                            UserSelect::make('aprobado_por_id', 'Aprobado Por'),
                        ])
                        ->action(function (array $data, Activo $record) {
                            try {
                                $this->darDeBajaActivo->execute(
                                    $record->id,
                                    $data['motivo_tipo'] instanceof TipoBaja ? $data['motivo_tipo'] : TipoBaja::from($data['motivo_tipo']),
                                    $data['motivo_detalle'],
                                    (int) auth()->id(),
                                    $data['valor_residual'] !== null ? (float) $data['valor_residual'] : null,
                                    $data['aprobado_por_id'] !== null ? (int) $data['aprobado_por_id'] : null
                                );
                            } catch (Throwable) {

                            }
                        })
                        ->visible(fn (Activo $record) => $record->estado !== EstadoActivo::DadoDeBaja),

                    Action::make('marcar_repuestos')
                        ->label('Marcar para Repuestos')
                        ->icon(Heroicon::Scissors)
                        ->color('gray')
                        ->requiresConfirmation()
                        ->modalHeading('Designar Activo para Repuestos')
                        ->modalDescription('¿Está seguro de que desea marcar este activo para repuestos? Se desactivará su asignación actual y se trasladará a la bodega general.')
                        ->action(function (Activo $record) {
                            try {
                                DB::transaction(function () use ($record) {
                                    $record->estado = EstadoActivo::Repuesto;
                                    $this->activoRepositorio->guardar($record);

                                    $this->asignacionRepositorio->cerrarAsignacionesVigentes(
                                        activoId: $record->id,
                                        fechaFin: now()->toDateString(),
                                        estado: EstadoAsignacion::Cerrada->value
                                    );

                                    $almacen = $this->obtenerAlmacen->ejecutar();
                                    if ($almacen) {
                                        $this->asignacionRepositorio->crear([
                                            'activo_id' => $record->id,
                                            'asignable_type' => Ubicacion::class,
                                            'asignable_id' => $almacen->id,
                                            'fecha_inicio' => now()->toDateString(),
                                            'motivo' => 'Designado para repuestos (Canibalizado)',
                                            'asignado_por_id' => (int) auth()->id(),
                                            'estado' => EstadoAsignacion::Vigente,
                                        ]);
                                    }
                                });

                                Notification::make()
                                    ->title('Activo Designado para Repuestos')
                                    ->body("El activo $record->codigo_inventario ahora está marcado para repuestos.")
                                    ->success()
                                    ->send();
                            } catch (Throwable $e) {

                                Notification::make()
                                    ->title('Error')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (Activo $record) => $record->estado !== EstadoActivo::DadoDeBaja && $record->estado !== EstadoActivo::Repuesto),

                    Action::make('imprimir_ficha')
                        ->label('Imprimir Ficha')
                        ->icon(Heroicon::Printer)
                        ->color('info')
                        ->url(fn (Activo $record) => route('admin.activos.reportes.ficha.pdf', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (Activo $record) => ! $record->trashed() && auth()->user()?->can('Activos:ReporteFicha')),
                ])
                    ->label('Acciones Especiales')
                    ->icon(Heroicon::ChevronDown)
                    ->color('info'),
            ])
            ->headerActions([
                Action::make('exportar_excel')
                    ->label('Exportar Inventario (Excel)')
                    ->icon(Heroicon::DocumentArrowDown)
                    ->color('success')
                    ->url(fn () => route('admin.activos.reportes.inventario-general.excel', request()->query()))
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()?->can('Activos:ReporteInventario') ?? false),
                Action::make('imprimir_etiquetas')
                    ->label('Imprimir Códigos de Barras')
                    ->icon(Heroicon::QrCode)
                    ->color('warning')
                    ->url(fn () => route('admin.activos.reportes.etiquetas.pdf', request()->query()))
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()?->can('Activos:ReporteEtiquetas') ?? false),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('traslado_masivo')
                        ->label('Traslado Masivo')
                        ->icon(Heroicon::ArrowsRightLeft)
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Trasladar Activos Seleccionados')
                        ->modalDescription('Asigne los activos seleccionados a un nuevo espacio, habitación o ubicación física.')
                        ->schema([
                            Select::make('asignable_type')
                                ->label('Tipo de Destino')
                                ->options([
                                    Habitacion::class => 'Habitación',
                                    Espacio::class => 'Espacio Común',
                                    Ubicacion::class => 'Ubicación / Área',
                                ])
                                ->required()
                                ->live(),

                            Select::make('asignable_id')
                                ->label('Seleccionar Destino')
                                ->options(function (Get $get) {
                                    $type = $get('asignable_type');
                                    if ($type === Habitacion::class) {
                                        return CachedOptions::habitaciones();
                                    }
                                    if ($type === Espacio::class) {
                                        return CachedOptions::espacios();
                                    }
                                    if ($type === Ubicacion::class) {
                                        return CachedOptions::ubicacionesAlmacen();
                                    }

                                    return [];
                                })
                                ->required()
                                ->searchable(),

                            TextInput::make('motivo')
                                ->label('Motivo del traslado')
                                ->placeholder('Ej. Reubicación masiva de mobiliario por remodelación')
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            $successCount = 0;
                            foreach ($records as $record) {
                                /** @var Activo $record */
                                if ($record->estado === EstadoActivo::DadoDeBaja) {
                                    continue;
                                }
                                try {
                                    $this->asignarActivo->ejecutar(
                                        $record->id,
                                        $data['asignable_type'],
                                        (int) $data['asignable_id'],
                                        (int) auth()->id(),
                                        $data['motivo']
                                    );
                                    $successCount++;
                                } catch (Throwable) {

                                }
                            }

                            Notification::make()
                                ->title('Traslado Masivo Completado')
                                ->body("Se trasladaron exitosamente {$successCount} activos.")
                                ->success()
                                ->send();
                        }),
                ]),
            ]);
    }
}
