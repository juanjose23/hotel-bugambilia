<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorSalario\Pages;

use App\Enums\EstadoCatalogo;
use App\Filament\Resources\Colaboradores\ColaboradorSalario\ColaboradorSalarioResource;
use App\Models\Colaboradores\ColaboradorSalario;
use App\UseCases\Colaboradores\CrearNuevoSalario;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateColaboradorSalario extends CreateRecord
{
    protected static string $resource = ColaboradorSalarioResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $salarioAnterior = ColaboradorSalario::where('colaborador_id', $data['colaborador_id'])
            ->where('estado', EstadoCatalogo::Activo->value)
            ->latest('fecha_inicio')
            ->first();

        if ($salarioAnterior && ((int) ($data['estado'] ?? EstadoCatalogo::Activo->value) === EstadoCatalogo::Activo->value)) {
            Notification::make()
                ->title('Salario Anterior será Inactivo')
                ->body("El salario anterior de NIO {$salarioAnterior->salario} será puesto automáticamente en Inactivo")
                ->warning()
                ->send();
        }

        $record = app(CrearNuevoSalario::class)(
            $data['colaborador_id'],
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
