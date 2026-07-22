<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorSalario\Pages;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Resources\Colaboradores\ColaboradorSalario\ColaboradorSalarioResource;
use App\Interactors\Colaboradores\CrearNuevoSalario;
use App\Repository\Models\Colaboradores\ColaboradorSalario;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateColaboradorSalario extends CreateRecord
{
    protected CrearNuevoSalario $crearNuevoSalario;

    public function boot(CrearNuevoSalario $crearNuevoSalario): void
    {
        $this->crearNuevoSalario = $crearNuevoSalario;
    }

    protected static string $resource = ColaboradorSalarioResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $salarioAnterior = ColaboradorSalario::where('colaborador_id', $data['colaborador_id'])
            ->where('estado', EstadoGeneral::Activo->value)
            ->latest('fecha_inicio')
            ->first();

        $estadoVal = $data['estado'] ?? EstadoGeneral::Activo->value;
        $estadoInt = is_scalar($estadoVal) ? (int) $estadoVal : EstadoGeneral::Activo->value;

        if ($salarioAnterior && ($estadoInt === EstadoGeneral::Activo->value)) {
            Notification::make()
                ->title('Salario Anterior será Inactivo')
                ->body("El salario anterior de NIO {$salarioAnterior->salario} será puesto automáticamente en Inactivo")
                ->warning()
                ->send();
        }

        $colaboradorId = is_numeric($data['colaborador_id'] ?? null) ? (int) $data['colaborador_id'] : 0;

        $record = ($this->crearNuevoSalario)(
            $colaboradorId,
            $data
        );

        Notification::make()
            ->title('Salario Creado')
            ->body('El nuevo salario ha sido registrado y el anterior ha sido puesto en Inactivo.')
            ->success()
            ->send();

        return $record;
    }
}
