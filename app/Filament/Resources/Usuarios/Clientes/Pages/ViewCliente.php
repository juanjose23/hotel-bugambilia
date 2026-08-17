<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Clientes\Pages;

use App\Exceptions\YaTieneCuentaException;
use App\Filament\Resources\Usuarios\Clientes\ClienteResource;
use App\Interactors\Usuarios\Identidad\VincularPersonaAUser;
use App\Repository\Models\Personas\Persona;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Str;

class ViewCliente extends ViewRecord
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
                ->action(function () {
                    /** @var Persona $persona */
                    $persona = $this->getRecord();
                    $vincular = app(VincularPersonaAUser::class);
                    $user = null;

                    try {
                        $user = $vincular->ejecutar($persona, [
                            'email' => $persona->email ?? null,
                            'password' => Str::random(12),
                        ]);
                    } catch (YaTieneCuentaException $e) {
                        Notification::make()
                            ->title('Esta persona ya tiene una cuenta')
                            ->body('La persona ya está registrada en el sistema. Puede iniciar sesión o recuperar su contraseña.')
                            ->warning()
                            ->send();

                        $this->halt();
                    }

                    if ($user === null) {
                        throw new \RuntimeException('No se pudo crear la cuenta de acceso.');
                    }

                    Notification::make()
                        ->title('Cuenta creada exitosamente')
                        ->body("Usuario: {$user->name}. La contraseña ha sido generada automáticamente.")
                        ->success()
                        ->send();

                    $this->redirect($this->getResource()::getUrl('view', ['record' => $persona]));
                }),
        ];
    }
}
