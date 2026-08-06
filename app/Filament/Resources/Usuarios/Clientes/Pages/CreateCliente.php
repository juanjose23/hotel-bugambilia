<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Clientes\Pages;

use App\Exceptions\YaTieneCuentaException;
use App\Filament\Resources\Usuarios\Clientes\ClienteResource;
use App\Interactors\Usuarios\Clientes\RegistrarCliente;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCliente extends CreateRecord
{
    protected static string $resource = ClienteResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            $interactor = app(RegistrarCliente::class);
            $resultado = $interactor->ejecutar($data);

            Notification::make()
                ->title('Cliente registrado exitosamente')
                ->body($resultado['persona']->nombre_completo)
                ->success()
                ->send();

            return $resultado['persona'];
        } catch (YaTieneCuentaException $e) {
            Notification::make()
                ->title('Esta persona ya tiene una cuenta')
                ->body('La persona ya está registrada en el sistema. Puede iniciar sesión o recuperar su contraseña.')
                ->warning()
                ->send();

            $this->halt();
        } catch (\RuntimeException $e) {
            Notification::make()
                ->title('Conflicto de identidad detectado')
                ->body($e->getMessage())
                ->danger()
                ->send();

            $this->halt();
        }

        throw new \RuntimeException('No se pudo crear el cliente.');
    }
}
