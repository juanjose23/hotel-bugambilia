<?php

declare(strict_types=1);

namespace App\Filament\Pages\Activos;

use App\Enums\Activos\EstadoActivo;
use App\Filament\Pages\Activos\Widgets\EstadisticasActivosWidget;
use App\Models\Activos\Activo;
use App\Models\Espacios\Espacio;
use App\Models\Habitaciones\Habitacion;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ReportesActivos extends Page
{
    protected string $view = 'filament.pages.activos.reportes-activos';

    protected static ?string $slug = 'reportes-activos';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::DocumentChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Activos Fijos';

    protected static ?string $navigationLabel = 'Reportes de Activos';

    protected static ?string $title = 'Reportes del Módulo de Activos Fijos';

    protected static ?int $navigationSort = 99;

    protected function getHeaderWidgets(): array
    {
        return [
            EstadisticasActivosWidget::class,
        ];
    }

    // ─── Permisos ────────────────────────────────────────────────────────────

    public static function canAccess(): bool
    {
        return self::tieneAlgunPermisoReporte();
    }

    private static function tieneAlgunPermisoReporte(): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->can('Activos:ReporteInventario')
            || $user->can('Activos:ReportePorUbicacion')
            || $user->can('Activos:ReporteHistorial')
            || $user->can('Activos:ReporteMantenimientoActivos')
            || $user->can('Activos:ReporteHojaHabitacion')
            || $user->can('Activos:ReporteFicha')
            || $user->can('Activos:ReporteEtiquetas');
    }

    // ─── Actions ─────────────────────────────────────────────────────────────

    public function inventarioGeneralAction(): Action
    {
        return Action::make('inventarioGeneral')
            ->label('Generar Reporte PDF')
            ->color('primary')
            ->icon(Heroicon::ArrowDownTray)
            ->modalHeading('Reporte: Inventario General')
            ->modalDescription('Filtros opcionales para el reporte general de activos.')
            ->form([
                Select::make('estado')
                    ->label('Filtrar por Estado')
                    ->options(EstadoActivo::class)
                    ->placeholder('Todos los estados'),
            ])
            ->action(fn (array $data) => redirect()->route('reporte.activos.inventario-general.pdf', [
                'estado' => $data['estado'] ?? null,
            ]));
    }

    public function porUbicacionAction(): Action
    {
        return Action::make('porUbicacion')
            ->label('Generar Reporte PDF')
            ->color('success')
            ->icon(Heroicon::ArrowDownTray)
            ->modalHeading('Reporte: Activos por Ubicación')
            ->modalDescription('Agrupa los activos según su asignación actual.')
            ->form([
                Select::make('ubicacion_tipo')
                    ->label('Filtrar por tipo de ubicación')
                    ->options([
                        '' => 'Todas las ubicaciones',
                        Habitacion::class => 'Habitaciones',
                        Espacio::class => 'Espacios / Áreas Comunes',
                    ])
                    ->placeholder('Todas'),
            ])
            ->action(fn (array $data) => redirect()->route('reporte.activos.por-ubicacion.pdf', [
                'ubicacion_tipo' => $data['ubicacion_tipo'] ?: null,
            ]));
    }

    public function hojaHabitacionAction(): Action
    {
        return Action::make('hojaHabitacion')
            ->label('Generar Reporte PDF')
            ->color('info')
            ->icon(Heroicon::ArrowDownTray)
            ->modalHeading('Reporte: Hoja de Habitación o Espacio')
            ->modalDescription('Genera el inventario de activos de una habitación o espacio.')
            ->form([
                Radio::make('tipo')
                    ->label('Tipo')
                    ->options([
                        'habitacion' => 'Habitación',
                        'espacio' => 'Espacio / Área Común',
                    ])
                    ->default('habitacion')
                    ->required()
                    ->reactive(),

                Select::make('entidad_id')
                    ->label('Seleccionar')
                    ->options(fn (callable $get) => $get('tipo') === 'habitacion'
                        ? Habitacion::pluck('nombre', 'id')
                        : Espacio::pluck('nombre', 'id')
                    )
                    ->searchable()
                    ->required(),
            ])
            ->action(fn (array $data) => redirect()->route('reporte.activos.hoja-habitacion.pdf', [
                'tipo' => $data['tipo'],
                'id' => $data['entidad_id'],
            ]));
    }

    public function enMantenimientoAction(): Action
    {
        return Action::make('enMantenimiento')
            ->label('Generar Reporte PDF')
            ->color('warning')
            ->icon(Heroicon::ArrowDownTray)
            ->action(fn () => redirect()->route('reporte.activos.en-mantenimiento.pdf'));
    }

    public function manttosVencidosAction(): Action
    {
        return Action::make('manttosVencidos')
            ->label('Generar Reporte PDF')
            ->color('danger')
            ->icon(Heroicon::ArrowDownTray)
            ->action(fn () => redirect()->route('reporte.activos.mantenimientos-vencidos.pdf'));
    }

    public function garantiasAction(): Action
    {
        return Action::make('garantias')
            ->label('Generar Reporte PDF')
            ->color('primary')
            ->icon(Heroicon::ArrowDownTray)
            ->modalHeading('Reporte: Garantías Próximas a Vencer')
            ->form([
                TextInput::make('dias')
                    ->label('Días de anticipación')
                    ->numeric()
                    ->default(90)
                    ->minValue(1)
                    ->required(),
            ])
            ->action(fn (array $data) => redirect()->route('reporte.activos.garantias-proximas.pdf', [
                'dias' => $data['dias'],
            ]));
    }

    public function historialAction(): Action
    {
        return Action::make('historial')
            ->label('Generar Reporte PDF')
            ->color('gray')
            ->icon(Heroicon::ArrowDownTray)
            ->modalHeading('Reporte: Historial de Movimientos')
            ->form([
                Select::make('activo_id')
                    ->label('Seleccionar Activo')
                    ->options(Activo::pluck('codigo_inventario', 'id'))
                    ->searchable()
                    ->required(),
            ])
            ->action(fn (array $data) => redirect()->route('reporte.activos.historial-movimientos.pdf', [
                'activo_id' => $data['activo_id'],
            ]));
    }

    public function bajasAction(): Action
    {
        return Action::make('bajas')
            ->label('Generar Reporte PDF')
            ->color('danger')
            ->icon(Heroicon::ArrowDownTray)
            ->action(fn () => redirect()->route('reporte.activos.dados-de-baja.pdf'));
    }

    public function extraviadosAction(): Action
    {
        return Action::make('extraviados')
            ->label('Extraviados (PDF)')
            ->color('warning')
            ->icon(Heroicon::ArrowDownTray)
            ->action(fn () => redirect()->route('reporte.activos.extraviados.pdf'));
    }

    public function sinAsignacionAction(): Action
    {
        return Action::make('sinAsignacion')
            ->label('Sin Asignar (PDF)')
            ->color('gray')
            ->icon(Heroicon::ArrowDownTray)
            ->action(fn () => redirect()->route('reporte.activos.sin-asignacion.pdf'));
    }
}
