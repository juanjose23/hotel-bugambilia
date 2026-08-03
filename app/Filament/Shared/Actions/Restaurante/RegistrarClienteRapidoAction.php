<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\Interactors\Restaurante\Pedidos\RegistrarClienteRapido;
use App\Repository\Models\Personas\Persona;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

final class RegistrarClienteRapidoAction
{
    /**
     * Acción reutilizable para registrar un cliente con datos mínimos desde el módulo de restaurante.
     *
     * @param  \Closure(Persona $persona): void  $onClienteRegistrado
     */
    public static function make(?\Closure $onClienteRegistrado = null): Action
    {
        return Action::make('registrarClienteRapido')
            ->label('Registrar Cliente')
            ->icon(Heroicon::UserPlus)
            ->color('success')
            ->modalWidth('md')
            ->schema([
                TextInput::make('identificacion')
                    ->label('Identificación (Cédula / RUC)')
                    ->maxLength(30)
                    ->placeholder('Ej. 001-010190-0001A'),
                TextInput::make('nombre')
                    ->label('Nombre')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Ej. María'),
                TextInput::make('apellido')
                    ->label('Apellido')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('Ej. Sánchez'),
                TextInput::make('telefono')
                    ->label('Teléfono')
                    ->tel()
                    ->maxLength(20)
                    ->placeholder('Ej. +505 8888 8888'),
            ])
            ->action(function (array $data) use ($onClienteRegistrado): void {
                $persona = app(RegistrarClienteRapido::class)->ejecutar([
                    'primer_nombre' => $data['nombre'],
                    'primer_apellido' => $data['apellido'] ?? '',
                    'identificacion' => $data['identificacion'] ?? null,
                    'telefono' => $data['telefono'] ?? null,
                ]);

                if ($onClienteRegistrado !== null) {
                    $onClienteRegistrado($persona);
                }

                Notification::make()
                    ->title('Cliente registrado')
                    ->body($persona->nombre_completo ?? $persona->primer_nombre)
                    ->success()
                    ->send();
            });
    }
}
