<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activos\ActivoMantenimiento\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;

class ActivoMantenimientoInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // SECCIÓN 1: DATOS GENERALES DE LA ORDEN
            Section::make('Orden de Mantenimiento')
                ->description('Detalle general del mantenimiento correctivo/preventivo')
                ->icon(Heroicon::WrenchScrewdriver)
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('id')
                        ->label('Orden Nro.')
                        ->badge()
                        ->color('primary')
                        ->weight(FontWeight::Bold),

                    TextEntry::make('plan.tipo')
                        ->label('Tipo de Mantenimiento')
                        ->badge(),

                    TextEntry::make('estado')
                        ->label('Estado de la Orden')
                        ->badge(),

                    TextEntry::make('fecha_programada')
                        ->label('Fecha Programada')
                        ->date('d/m/Y')
                        ->icon(Heroicon::Calendar),

                    TextEntry::make('fecha_realizada')
                        ->label('Fecha Realizada')
                        ->date('d/m/Y')
                        ->placeholder('En taller / pendiente')
                        ->icon(Heroicon::CalendarDays),

                    TextEntry::make('realizadoPor.name')
                        ->label('Registrado / Asignado Por')
                        ->icon(Heroicon::User)
                        ->placeholder('No asignado'),
                ]),

            // SECCIÓN 2: DATOS DEL ACTIVO AFECTADO
            Section::make('Activo Fijo')
                ->description('Datos del activo en intervención')
                ->icon(Heroicon::CpuChip)
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('activo.codigo_inventario')
                        ->label('Código de Inventario')
                        ->badge()
                        ->color('info')
                        ->weight(FontWeight::Bold)
                        ->copyable()
                        ->icon(Heroicon::Hashtag),

                    TextEntry::make('activo.nombre_descriptivo')
                        ->label('Nombre del Activo')
                        ->weight(FontWeight::Bold)
                        ->icon(Heroicon::Tag),

                    TextEntry::make('activo.producto.nombre')
                        ->label('Categoría / Producto')
                        ->icon(Heroicon::Cube),
                ]),

            // SECCIÓN 3: COSTOS Y PROVEEDOR
            Section::make('Resolución y Costos')
                ->description('Información técnica, taller y costos asociados')
                ->icon(Heroicon::Banknotes)
                ->columns(3)
                ->columnSpanFull()
                ->schema([
                    TextEntry::make('costo_real')
                        ->label('Costo Real')
                        ->money(fn ($record) => $record->plan->moneda->codigo ?? 'USD')
                        ->placeholder('Sin costo / Garantía')
                        ->icon(Heroicon::CurrencyDollar),

                    TextEntry::make('plan.moneda.nombre')
                        ->label('Moneda de Pago')
                        ->placeholder('N/A')
                        ->icon(Heroicon::Banknotes),

                    TextEntry::make('plan.proveedor.codigo')
                        ->label('Taller / Proveedor Externo')
                        ->icon(Heroicon::BuildingOffice2)
                        ->formatStateUsing(fn ($state, $record) => $record->plan && $record->plan->proveedor
                            ? "{$record->plan->proveedor->codigo} - ".($record->plan->proveedor->persona->personaJuridica->razon_social
                                ?? "{$record->plan->proveedor->persona->primer_nombre} {$record->plan->proveedor->persona->personaNatural?->primer_apellido}")
                            : null)
                        ->placeholder('Mantenimiento Interno'),

                    TextEntry::make('plan.descripcion')
                        ->label('Detalle de Intervención / Falla')
                        ->icon(Heroicon::DocumentText)
                        ->columnSpan(3),

                    TextEntry::make('notas')
                        ->label('Notas Adicionales de Taller')
                        ->icon(Heroicon::PencilSquare)
                        ->placeholder('Ninguna nota adicional registrada')
                        ->columnSpan(3),
                ]),
        ]);
    }
}
