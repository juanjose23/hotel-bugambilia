<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Clientes\Pages;

use App\Filament\Resources\Usuarios\Clientes\ClienteResource;
use App\Interactors\Usuarios\ActualizarCliente;
use App\Interactors\Usuarios\VincularPersonaExistenteAUser;
use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class EditCliente extends EditRecord
{
    protected static string $resource = ClienteResource::class;

    protected function getHeaderActions(): array
    {
        $record = $this->getRecord();
        $tieneUsuario = $record instanceof Persona && $record->user !== null;

        return [
            Action::make('crearUsuario')
                ->label('Crear cuenta de acceso')
                ->icon('heroicon-o-key')
                ->color('success')
                ->visible(! $tieneUsuario)
                ->schema([
                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->required()
                        ->helperText('Ingrese el correo para la cuenta de acceso del cliente.'),
                ])
                ->action(function (array $data) {
                    /** @var Persona $persona */
                    $persona = $this->getRecord();
                    $password = Str::random(12);

                    $vincular = app(VincularPersonaExistenteAUser::class);
                    $user = $vincular->ejecutar($persona, [
                        'email' => $data['email'],
                        'password' => $password,
                    ]);

                    $user->update(['password_change_required' => true]);

                    Notification::make()
                        ->title('Cuenta creada')
                        ->body("Usuario: {$data['email']}. Contraseña temporal generada. Deberá cambiarla al iniciar sesión.")
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('edit', ['record' => $persona]));
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $persona = $this->getRecord();
        $persona->loadMissing('cliente', 'user');
        if ($persona instanceof Persona) {
            if ($persona->personaNatural) {
                $data['primer_apellido'] = $persona->personaNatural->primer_apellido;
                $data['segundo_apellido'] = $persona->personaNatural->segundo_apellido;
                $data['tipo_identificacion'] = $persona->personaNatural->tipo_identificacion;
                $data['numero_identificacion'] = $persona->personaNatural->numero_identificacion;
                $data['fecha_nacimiento'] = $persona->personaNatural->fecha_nacimiento;
            }
            if ($persona->cliente instanceof Cliente) {
                $data['catalogo_id'] = $persona->cliente->catalogo_id;
            }
            if ($persona->user !== null) {
                $data['email'] = $persona->user->email;
            }
        }

        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var Persona $record */
        $record->loadMissing('cliente');
        $cliente = $record->cliente;

        if (! $cliente instanceof Cliente) {
            throw new \RuntimeException('La persona no tiene un registro de cliente.');
        }

        $interactor = app(ActualizarCliente::class);
        $interactor->ejecutar($cliente, $data);

        Notification::make()
            ->title('Cliente actualizado')
            ->success()
            ->send();

        $refrescado = $record->fresh();

        if (! $refrescado instanceof Model) {
            throw new \RuntimeException('No se pudo refrescar el registro.');
        }

        return $refrescado;
    }
}
