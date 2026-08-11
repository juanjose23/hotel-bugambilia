<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Schemas;

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
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class ReservaForm
{
    public function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Callout::make('Pago del 50 % pendiente')
                    ->description('Esta reserva aún no está pagada. Tiene que abonar el 50 % del total antes de confirmar la reserva.')
                    ->warning()
                    ->visibleOn('edit')
                    ->visible(fn ($record): bool => $record !== null && (float) $record->saldo > 0),

                Grid::make(['default' => 1, 'xl' => 3])
                    ->visibleOn('create')
                    ->schema([
                        Wizard::make([
                            Step::make('Reserva y cliente')
                                ->description('Tipo de reserva y datos del titular')
                                ->icon(Heroicon::User)
                                ->schema([
                                    InformacionGeneralSeccion::make(),
                                    DatosClienteSeccion::make(),
                                ]),

                            Step::make('Detalle')
                                ->description('Habitación, mesa, servicio o paquete')
                                ->icon(Heroicon::ClipboardDocumentList)
                                ->schema([
                                    ...EsquemaReservaHabitacion::make(),
                                    ...EsquemaReservaRestaurante::make(),
                                    ...EsquemaReservaEspacio::make(),
                                    ...EsquemaReservaServicio::make(),
                                    SelectorServiciosAdicionales::make(),
                                ]),

                            Step::make('Pago y confirmación')
                                ->description('Cobro inicial y notas')
                                ->icon(Heroicon::CreditCard)
                                ->schema([
                                    ResumenFinancieroYAbonoSeccion::makeCobro(),
                                    NotasReservaSeccion::make(),
                                ]),
                        ])
                            ->columnSpan(['default' => 1, 'xl' => 2])
                            ->submitAction(new HtmlString('
                                <div class="flex items-center gap-x-3">
                                    <button type="submit" class="fi-btn fi-btn-color-primary fi-btn-size-md relative inline-flex items-center justify-center font-semibold rounded-lg shadow-sm focus:outline-none bg-primary-600 hover:bg-primary-500 text-white px-4 py-2 text-sm gap-1.5 cursor-pointer">
                                        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15A2.25 2.25 0 0 0 2.25 6.75v10.5A2.25 2.25 0 0 0 4.5 19.5Z"/></svg>
                                        <span>Crear reserva y registrar pago</span>
                                    </button>
                                    <button type="button" wire:click="createAnother" class="fi-btn fi-btn-color-gray fi-btn-size-md relative inline-flex items-center justify-center font-semibold rounded-lg shadow-sm focus:outline-none bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 px-4 py-2 text-sm cursor-pointer">
                                        <span>Crear y crear otro</span>
                                    </button>
                                    <a href="/admin/reservas" class="fi-btn fi-btn-color-gray fi-btn-size-md relative inline-flex items-center justify-center font-semibold rounded-lg shadow-sm focus:outline-none text-gray-600 dark:text-gray-400 hover:underline px-3 py-2 text-sm">
                                        <span>Cancelar</span>
                                    </a>
                                </div>
                            '))
                            ->skippable(false),

                        ResumenFinancieroPreviewSeccion::make(),
                    ]),

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
                ])->visibleOn('edit'),
            ]);
    }
}
