<?php

declare(strict_types=1);

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\BusinessLogic\Compras\VerificarEdicionCotizacion;
use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Repository\Models\Compras\Cotizacion;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditCotizacion extends EditRecord
{
    protected static string $resource = CotizacionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn (Cotizacion $record) => ! app(VerificarEdicionCotizacion::class)->puedeEditar($record)),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function afterFill(): void
    {
        /** @var Cotizacion $record */
        $record = $this->getRecord();

        if (! app(VerificarEdicionCotizacion::class)->puedeEditar($record)) {
            Notification::make()
                ->title('Cotización bloqueada')
                ->body('Esta cotización no puede ser editada porque tiene órdenes de compra activas.')
                ->warning()
                ->send();
        }
    }
}
