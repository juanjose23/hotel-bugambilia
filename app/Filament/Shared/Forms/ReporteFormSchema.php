<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Support\Barcode\Support\ReporteConfig;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Component;

final class ReporteFormSchema
{
    /**
     * Construye los componentes compartidos del formulario de configuración de reportes.
     *
     * @param  string  $modulo  Identificador del módulo en ReporteConfig (e.g. 'reservas', 'financiero')
     * @param  array<int, mixed>  $camposEspecificos  Campos o filtros propios del módulo inyectados dinámicamente
     * @param  string  $labelReporte  Etiqueta para el selector principal de reporte
     * @param  bool  $incluirFechas  Determina si se agregan los selectores de Fecha Inicio y Fecha Fin
     * @param  Closure|null  $onFechasActualizadas  Callback a ejecutar tras actualizar una fecha (e.g. recargar métricas)
     * @return array<int, Component>
     */
    public static function construir(
        string $modulo,
        array $camposEspecificos = [],
        string $labelReporte = 'Reporte Analítico',
        bool $incluirFechas = true,
        ?Closure $onFechasActualizadas = null,
    ): array {
        /** @var array<int, Component> $schema */
        $schema = [];

        // 1. Selector principal de reporte analítico
        $schema[] = Select::make('reporte')
            ->label($labelReporte)
            ->options(ReporteConfig::getSelectOptions($modulo))
            ->required()
            ->live()
            ->native(false)
            ->searchable()
            ->placeholder('Selecciona un reporte de la lista...');

        // 2. Descripción dinámica del reporte seleccionado
        $schema[] = TextEntry::make('reporte_descripcion')
            ->hiddenLabel()
            ->state(fn ($get): string => ReporteConfig::getDescripcion($modulo, (string) ($get('reporte') ?? '')) ?? 'Seleccione un reporte de la lista para ver su descripción...')
            ->extraAttributes(['class' => 'text-sm text-gray-500 italic mt-1 dark:text-gray-400']);

        // 3. Inyección de campos específicos propios del módulo (con su visibilidad condicional)
        foreach ($camposEspecificos as $campo) {
            if ($campo instanceof Component) {
                $schema[] = $campo;
            }
        }

        // 4. Fechas estándar estandarizadas (inicio y fin)
        if ($incluirFechas) {
            $fechaInicio = DatePicker::make('fecha_inicio')
                ->label('Fecha Inicio')
                ->default(now()->startOfMonth()->format('Y-m-d'))
                ->required()
                ->native(false);

            $fechaFin = DatePicker::make('fecha_fin')
                ->label('Fecha Fin')
                ->default(now()->format('Y-m-d'))
                ->required()
                ->native(false);

            if ($onFechasActualizadas !== null) {
                $fechaInicio->live()->afterStateUpdated($onFechasActualizadas);
                $fechaFin->live()->afterStateUpdated($onFechasActualizadas);
            }

            $schema[] = $fechaInicio;
            $schema[] = $fechaFin;
        }

        return $schema;
    }
}
