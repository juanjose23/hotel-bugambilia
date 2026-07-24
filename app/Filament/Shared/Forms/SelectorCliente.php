<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Repository\Queries\Reservas\BuscarClientesReservaQuery;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;

class SelectorCliente
{
    /** @return array<int, Select|TextInput> */
    public static function make(
        string $columnClienteId = 'cliente_id',
        string $columnNombre = 'nombre_cliente',
        string $columnTelefono = 'telefono_cliente',
        string $columnEmail = 'email_cliente',
        int $columnSpan = 1,
    ): array {
        return [
            Select::make($columnClienteId)
                ->label('Cliente registrado')
                ->placeholder('Buscar por nombre, correo o teléfono')
                ->searchable()
                ->getSearchResultsUsing(fn (string $search): array => app(BuscarClientesReservaQuery::class)->buscar($search))
                ->getOptionLabelUsing(fn ($value): ?string => is_numeric($value)
                    ? app(BuscarClientesReservaQuery::class)->etiquetaPorId((int) $value)
                    : null)
                ->afterStateUpdated(function ($state, Set $set) use ($columnNombre, $columnTelefono, $columnEmail): void {
                    if (! is_numeric($state)) {
                        return;
                    }

                    $datos = app(BuscarClientesReservaQuery::class)->datosPorId((int) $state);
                    if ($datos === null) {
                        return;
                    }

                    $set($columnNombre, $datos['nombre']);
                    $set($columnTelefono, $datos['telefono']);
                    $set($columnEmail, $datos['email']);
                })
                ->live()
                ->nullable()
                ->native(false)
                ->columnSpan($columnSpan),

            TextInput::make($columnNombre)
                ->label('Nombre del Huésped')
                ->placeholder('Nombre completo')
                ->required()
                ->maxLength(150)
                ->columnSpan($columnSpan),

            TextInput::make($columnTelefono)
                ->label('Teléfono de Contacto')
                ->placeholder('Ej. +505 8888 8888')
                ->columnSpan($columnSpan),

            TextInput::make($columnEmail)
                ->label('Correo Electrónico')
                ->email()
                ->placeholder('cliente@ejemplo.com')
                ->columnSpan($columnSpan),
        ];
    }
}
