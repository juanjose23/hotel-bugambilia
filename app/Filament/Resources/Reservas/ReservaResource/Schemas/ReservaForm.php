<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Schemas;

use App\Enums\Reservas\TipoPagoReserva;
use App\Filament\Resources\Reservas\Schemas\Reserva\Comun\DatosClienteSeccion;
use App\Filament\Resources\Reservas\Schemas\Reserva\Comun\InformacionGeneralSeccion;
use App\Filament\Resources\Reservas\Schemas\Reserva\Comun\NotasReservaSeccion;
use App\Filament\Resources\Reservas\Schemas\Reserva\Comun\ResumenFinancieroPreviewSeccion;
use App\Filament\Resources\Reservas\Schemas\Reserva\Comun\ResumenFinancieroYAbonoSeccion;
use App\Filament\Resources\Reservas\Schemas\Reserva\Espacio\EsquemaReservaEspacio;
use App\Filament\Resources\Reservas\Schemas\Reserva\Habitacion\EsquemaReservaHabitacion;
use App\Filament\Resources\Reservas\Schemas\Reserva\Restaurante\EsquemaReservaRestaurante;
use App\Filament\Resources\Reservas\Schemas\Reserva\SelectorServiciosAdicionales;
use App\Filament\Resources\Reservas\Schemas\Reserva\Servicio\EsquemaReservaServicio;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class ReservaForm
{
    public function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                // Aviso de pago pendiente en edición
                Callout::make('Pago del 50 % pendiente')
                    ->description('Esta reserva aún no está pagada. Tiene que abonar el 50 % del total antes de confirmar la reserva.')
                    ->warning()
                    ->visibleOn('edit')
                    ->visible(fn ($record): bool => $record !== null && $record->tipo_pago !== TipoPagoReserva::PAGO_COMPLETO && (float) $record->total_pagado < $record->tipo_pago->monto((float) $record->total)),

                // =====================================================================
                // LAYOUT CREATE — panel izquierdo (detalles) + panel derecho (resumen+pago)
                // =====================================================================
                Grid::make(['default' => 1, 'lg' => 12])
                    ->visibleOn('create')
                    ->schema([
                        // --- Columna izquierda: flujo de creación ---
                        Group::make([
                            // 1. Tipo de reserva (ToggleButtons visual) + moneda + promoción
                            InformacionGeneralSeccion::make(),

                            // 2. Cliente / Huésped titular
                            DatosClienteSeccion::make(),

                            // 3. Campos contextuales por tipo
                            //    Cada esquema ya maneja su propia visibilidad condicional
                            ...EsquemaReservaHabitacion::make(),
                            ...EsquemaReservaRestaurante::make(),
                            ...EsquemaReservaEspacio::make(),
                            ...EsquemaReservaServicio::make(),

                            // 4. Recursos adicionales (colapsado por defecto)
                            SelectorServiciosAdicionales::make(),

                            // 5. Notas (colapsado por defecto)
                            NotasReservaSeccion::make(),
                        ])
                            ->columnSpan(['default' => 1, 'lg' => 7, 'xl' => 8]),

                        // --- Columna derecha: resumen en vivo + cobro ---
                        Group::make([
                            // Resumen financiero en vivo (sticky)
                            ResumenFinancieroPreviewSeccion::make(),

                            // Cobro inicial + cargos de facturación
                            // Mover aquí elimina el scroll para llegar al botón de crear
                            ResumenFinancieroYAbonoSeccion::makeCobro(),
                        ])
                            ->columnSpan(['default' => 1, 'lg' => 5, 'xl' => 4]),
                    ]),

                // =====================================================================
                // LAYOUT EDIT — sin cambios respecto a la implementación original
                // =====================================================================
                Grid::make(['default' => 1, 'lg' => 12])
                    ->visibleOn('edit')
                    ->schema([
                        Group::make([
                            InformacionGeneralSeccion::make(),
                            DatosClienteSeccion::make(),
                            ...EsquemaReservaHabitacion::make(),
                            ...EsquemaReservaRestaurante::make(),
                            ...EsquemaReservaEspacio::make(),
                            ...EsquemaReservaServicio::make(),
                            SelectorServiciosAdicionales::make(),
                            ResumenFinancieroYAbonoSeccion::make(),
                            NotasReservaSeccion::make(),
                        ])
                            ->columnSpan(['default' => 1, 'lg' => 7, 'xl' => 8]),

                        ResumenFinancieroPreviewSeccion::make()
                            ->columnSpan(['default' => 1, 'lg' => 5, 'xl' => 4]),
                    ]),
            ]);
    }
}
