<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\SolicitudLimpiezaResource\Pages;

use App\Filament\Resources\Limpieza\SolicitudLimpiezaResource\Schemas\SolicitudLimpiezaForm;
use App\Filament\Resources\Limpieza\SolicitudLimpiezaResource\SolicitudLimpiezaResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;

final class EditSolicitudLimpieza extends EditRecord
{
    protected static string $resource = SolicitudLimpiezaResource::class;

    /**
     * @return array<string, mixed>
     */
    protected function getFormSchema(): array
    {
        $schema = app(Schema::class, ['livewire' => $this]);
        SolicitudLimpiezaForm::configure($schema);

        return $schema->getComponents();
    }
}
