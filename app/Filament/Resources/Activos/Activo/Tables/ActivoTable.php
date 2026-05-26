<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\Activo\Tables;

use App\Enums\Activos\EstadoActivo;
use App\Enums\Activos\TipoBaja;
use App\Enums\Activos\TipoMantenimiento;
use App\Models\Activos\Activo;
use App\Models\Catalogos\Ubicacion;
use App\Models\Compras\Proveedor;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use App\Models\Monedas\Moneda;
use App\Models\User;
use App\UseCases\Activos\Mutations\AsignarActivo;
use App\UseCases\Activos\Mutations\DarDeBajaActivo;
use App\UseCases\Activos\Mutations\EnviarAMantenimiento;
use App\UseCases\Shared\Queries\ObtenerNombrePersona;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup as TableActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ActivoTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['producto', 'variante', 'asignacionActiva.asignable']))
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

                        return "{$prefijo} {$nombre}";
                    })
                    ->placeholder('Sin asignar')
                    ->badge()
                    ->color(fn (Activo $record): string => match ($record->asignacionActiva?->asignable_type) {
                        Habitacion::class => 'success',
                        Ubicacion::class => 'info',
                        Espacio::class => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('estado')
                    ->label('Estado')
                    ->badge()
                    ->sortable(),

                TextColumn::make('fecha_adquisicion')
                    ->label('Adquirido')
                    ->date('d/m/Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('estado')
                    ->options(EstadoActivo::class),
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
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                TableActionGroup::make([
                    Action::make('asignar_activo')
                        ->label('Asignar / Mover')
                        ->icon(Heroicon::ArrowsRightLeft)
                        ->color('info')
                        ->requiresConfirmation()
                        ->modalHeading('Asignar o Trasladar Activo')
                        ->form([
                            Select::make('asignable_type')
                                ->label('Tipo de Destino')
                                ->options([
                                    Habitacion::class => 'Habitación',
                                    Ubicacion::class => 'Ubicación / Bodega',
                                    Espacio::class => 'Espacio / Área Común',
                                ])
                                ->required()
                                ->reactive(),

                            Select::make('asignable_id')
                                ->label('Destino Específico')
                                ->options(function (callable $get) {
                                    $type = $get('asignable_type');
                                    if ($type === Habitacion::class) {
                                        return Habitacion::pluck('nombre', 'id');
                                    }
                                    if ($type === Ubicacion::class) {
                                        return Ubicacion::pluck('nombre', 'id');
                                    }
                                    if ($type === Espacio::class) {
                                        return Espacio::pluck('nombre', 'id');
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
                                app(AsignarActivo::class)->execute(
                                    $record->id,
                                    $data['asignable_type'],
                                    (int) $data['asignable_id'],
                                    auth()->id() ?? 1,
                                    $data['motivo']
                                );

                                Notification::make()
                                    ->title('Activo Trasladado')
                                    ->body("El activo {$record->codigo_inventario} ha sido asignado a su nueva ubicación.")
                                    ->success()
                                    ->send();
                            } catch (\Throwable $e) {
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
                        ->form([
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

                            Select::make('moneda_id')
                                ->label('Moneda')
                                ->options(Moneda::pluck('nombre', 'id')),

                            Select::make('proveedor_id')
                                ->label('Proveedor / Taller Externo')
                                ->options(
                                    fn () => Proveedor::with(['persona.personaNatural', 'persona.personaJuridica'])
                                        ->get()
                                        ->mapWithKeys(fn (Proveedor $p) => [
                                            $p->id => ObtenerNombrePersona::desde($p->persona).' ('.$p->codigo.')',
                                        ])
                                )
                                ->searchable()
                                ->placeholder('Seleccionar proveedor (opcional)'),

                            Textarea::make('notas')
                                ->label('Notas Adicionales'),
                        ])
                        ->action(function (array $data, Activo $record) {
                            try {
                                app(EnviarAMantenimiento::class)->execute(
                                    $record->id,
                                    $data['tipo'] instanceof TipoMantenimiento ? $data['tipo'] : TipoMantenimiento::from($data['tipo']),
                                    $data['descripcion'],
                                    auth()->id() ?? 1,
                                    $data['costo'] !== null ? (float) $data['costo'] : null,
                                    $data['moneda_id'] !== null ? (int) $data['moneda_id'] : null,
                                    $data['proveedor_id'] !== null ? (int) $data['proveedor_id'] : null,
                                    $data['notas'] ?: null
                                );

                                Notification::make()
                                    ->title('Activo en Mantenimiento')
                                    ->body("El activo {$record->codigo_inventario} ha sido ingresado a taller.")
                                    ->warning()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Error en Registro')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (Activo $record) => $record->estado === EstadoActivo::Activo),

                    Action::make('dar_baja')
                        ->label('Dar de Baja')
                        ->icon(Heroicon::Trash)
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Registrar Baja Definitiva de Activo')
                        ->form([
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

                            Select::make('aprobado_por_id')
                                ->label('Aprobado Por')
                                ->options(User::pluck('name', 'id'))
                                ->searchable(),
                        ])
                        ->action(function (array $data, Activo $record) {
                            try {
                                app(DarDeBajaActivo::class)->execute(
                                    $record->id,
                                    $data['motivo_tipo'] instanceof TipoBaja ? $data['motivo_tipo'] : TipoBaja::from($data['motivo_tipo']),
                                    $data['motivo_detalle'],
                                    auth()->id() ?? 1,
                                    $data['valor_residual'] !== null ? (float) $data['valor_residual'] : null,
                                    $data['aprobado_por_id'] !== null ? (int) $data['aprobado_por_id'] : null
                                );

                                Notification::make()
                                    ->title('Activo Dado de Baja')
                                    ->body("El activo {$record->codigo_inventario} ha sido retirado definitivamente del inventario.")
                                    ->danger()
                                    ->send();
                            } catch (\Throwable $e) {
                                Notification::make()
                                    ->title('Error en Registro')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        })
                        ->visible(fn (Activo $record) => $record->estado !== EstadoActivo::DadoDeBaja),

                    Action::make('imprimir_ficha')
                        ->label('Imprimir Ficha')
                        ->icon(Heroicon::Printer)
                        ->color('info')
                        ->url(fn (Activo $record) => route('reporte.activos.ficha.pdf', $record))
                        ->openUrlInNewTab()
                        ->visible(fn (Activo $record) => ! $record->trashed() && auth()->user()->can('Activos:ReporteFicha')),
                ])
                    ->label('Acciones Especiales')
                    ->icon(Heroicon::ChevronDown)
                    ->color('info'),
            ])
            ->headerActions([
                Action::make('reportes_activos')
                    ->label('Reportes de Activos')
                    ->icon(Heroicon::DocumentChartBar)
                    ->color('gray')
                    ->url('/admin/reportes-activos')
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()->can('Activos:ReportePorUbicacion')
                        || auth()->user()->can('Activos:ReporteHistorial')
                        || auth()->user()->can('Activos:ReporteMantenimientoActivos')
                        || auth()->user()->can('Activos:ReporteHojaHabitacion')),
                Action::make('imprimir_reporte')
                    ->label('Imprimir Inventario (PDF)')
                    ->icon(Heroicon::Printer)
                    ->color('info')
                    ->url(fn () => route('reporte.activos.inventario-general.pdf', request()->query()))
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()->can('Activos:ReporteInventario')),
                Action::make('exportar_excel')
                    ->label('Exportar Inventario (Excel)')
                    ->icon(Heroicon::DocumentArrowDown)
                    ->color('success')
                    ->url(fn () => route('reporte.activos.inventario-general.excel', request()->query()))
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()->can('Activos:ReporteInventario')),
                Action::make('imprimir_etiquetas')
                    ->label('Imprimir Códigos de Barras')
                    ->icon(Heroicon::QrCode)
                    ->color('warning')
                    ->url(fn () => route('reporte.activos.etiquetas.pdf', request()->query()))
                    ->openUrlInNewTab()
                    ->visible(fn () => auth()->user()->can('Activos:ReporteEtiquetas')),
            ]);
    }
}
