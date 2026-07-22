<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Clientes\Pages;

use App\Filament\Resources\Usuarios\Clientes\ClienteResource;
use App\Interactors\Usuarios\VincularPersonaExistenteAUser;
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
                    $vincular = app(VincularPersonaExistenteAUser::class);
                    $user = $vincular->ejecutar($persona, [
                        'email' => $persona->email ?? null,
                        'password' => Str::random(12),
                    ]);

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
