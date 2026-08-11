<?php

namespace App\Filament\Resources\Colaboradores\Colaboradors\Pages;

use App\Filament\Resources\Colaboradores\Colaboradors\ColaboradorResource;
use App\Repository\Models\Personas\Persona;
use App\Repository\Queries\Colaboradores\ObtenerDatosCarnet;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Illuminate\Database\Eloquent\Model;

class CarnetColaborador extends Page
{
    protected ObtenerDatosCarnet $obtenerDatosCarnet;

    public function boot(ObtenerDatosCarnet $obtenerDatosCarnet): void
    {
        $this->obtenerDatosCarnet = $obtenerDatosCarnet;
    }

    use InteractsWithRecord;

    protected static string $resource = ColaboradorResource::class;

    protected string $view = 'filament.resources.colaboradores.colaboradors.pages.carnet-colaborador';

    /** @var array<string, mixed> */
    public array $carnetData = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $this->authorizeAccess();

        if (! $this->record instanceof Model) {
            return;
        }

        $this->record->loadMissing([
            'personaNatural',
            'colaborador.imagen',
            'colaborador.datosMedicos',
            'colaborador.cargosHistorial.cargo',
            'colaborador.cargosHistorial.departamento',
        ]);

        /** @var Persona $persona */
        $persona = $this->record;
        $this->carnetData = $this->obtenerDatosCarnet->ejecutar($persona);
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
