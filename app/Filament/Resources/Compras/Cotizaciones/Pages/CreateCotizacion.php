<?php

namespace App\Filament\Resources\Compras\Cotizaciones\Pages;

use App\Filament\Resources\Compras\Cotizaciones\CotizacionResource;
use App\Filament\Resources\Compras\Cotizaciones\Schemas\CotizacionForm;
use App\Models\Compras\Cotizacion;
use App\Services\Compras\NotificadorCompras;
use Filament\Resources\Pages\CreateRecord;

class CreateCotizacion extends CreateRecord
{
    protected static string $resource = CotizacionResource::class;

    public function mount(): void
    {
        parent::mount();

        $solicitudId = request()->query('solicitud_id');
        if ($solicitudId) {
            $this->data['solicitud_id'] = (int) $solicitudId;
            CotizacionForm::loadSolicitudItems((int) $solicitudId, function (string $key, mixed $value): void {
                data_set($this->data, $key, $value);
            });
        }
    }

    protected function afterCreate(): void
    {
        /** @var Cotizacion $record */
        $record = $this->getRecord();
        app(NotificadorCompras::class)->cotizacionCreada($record);
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
