<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\BusinessLogic\Personas\PersonaNatural\ValidCedulaNicaragua;
use App\Interactors\Restaurante\Pedidos\RegistrarClienteRapido;
use App\Repository\Models\Clientes\Cliente;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

final class RegistrarClienteRapidoAction
{
    /**
     * Acción reutilizable para registrar un cliente con datos mínimos desde el módulo de restaurante.
     *
     * @param  \Closure(Cliente $cliente): void  $onClienteRegistrado
     */
    public static function make(?\Closure $onClienteRegistrado = null): Action
    {
        return Action::make('registrarClienteRapido')
            ->label('Registrar Cliente')
            ->icon(Heroicon::UserPlus)
            ->color('success')
            ->modalWidth('md')
            ->schema([
                Select::make('tipo_identificacion')
                    ->label('Tipo de Documento')
                    ->options([
                        'cedula' => 'Cédula',
                        'ruc' => 'RUC',
                        'pasaporte' => 'Pasaporte',
                    ])
                    ->default('cedula')
                    ->required()
                    ->live()
                    ->native(false),
                TextInput::make('identificacion')
                    ->label('Número de Identificación')
                    ->maxLength(30)
                    ->placeholder('Ej. 001-010190-0001A')
                    ->rules([
                        fn (Get $get) => $get('tipo_identificacion') === 'cedula' ? new ValidCedulaNicaragua : null,
                    ]),
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
                $cliente = app(RegistrarClienteRapido::class)->ejecutar([
                    'primer_nombre' => $data['nombre'],
                    'primer_apellido' => $data['apellido'] ?? '',
                    'tipo_identificacion' => $data['tipo_identificacion'] ?? 'cedula',
                    'identificacion' => $data['identificacion'] ?? null,
                    'telefono' => $data['telefono'] ?? null,
                ]);

                if ($onClienteRegistrado !== null) {
                    $onClienteRegistrado($cliente);
                }

                Notification::make()
                    ->title('Cliente registrado')
                    ->body($cliente->persona->nombre_completo ?? 'Cliente registrado')
                    ->success()
                    ->send();
            });
    }
}
