<?php

declare(strict_types=1);

namespace App\Filament\Resources\Reservas\ReservaResource\Pages;

use App\Filament\Resources\Reservas\ReservaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewReserva extends ViewRecord
{
    protected static string $resource = ReservaResource::class;

    protected static ?string $title = 'Detalle de Reserva';

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('voucher')
                ->label('Imprimir emisión de reserva')
                ->icon('heroicon-o-printer')
                ->url(fn (): string => route('reservas.voucher', ['reserva' => $this->getRecord()->getKey()]))
                ->openUrlInNewTab(),
            Actions\EditAction::make(),
        ];
    }
}
