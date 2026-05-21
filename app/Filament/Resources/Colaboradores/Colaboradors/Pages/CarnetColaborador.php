<?php

namespace App\Filament\Resources\Colaboradores\Colaboradors\Pages;

use App\Filament\Resources\Colaboradores\Colaboradors\ColaboradorResource;
use App\Models\Personas\Persona;
use App\UseCases\Colaboradores\Queries\ObtenerDatosCarnet;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class CarnetColaborador extends Page
{
    use InteractsWithRecord;

    protected static string $resource = ColaboradorResource::class;

    protected string $view = 'filament.resources.colaboradores.colaboradors.pages.carnet-colaborador';

    /** @var array<string, mixed> */
    public array $carnetData = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        $this->record->loadMissing([
            'personaNatural',
            'colaborador.imagen',
            'colaborador.datosMedicos',
            'colaborador.cargosHistorial.cargo',
            'colaborador.cargosHistorial.departamento',
        ]);

        /** @var Persona $persona */
        $persona = $this->record;
        $this->carnetData = app(ObtenerDatosCarnet::class)->ejecutar($persona);
    }

    protected function authorizeAccess(): void
    {
        abort_unless(static::getResource()::canView($this->getRecord()), 403);
    }

    public function getTitle(): string
    {
        return 'Carnet del trabajador';
    }

    public function getMaxContentWidth(): string
    {
        return 'full';
    }
}
