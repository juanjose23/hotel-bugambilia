<?php

declare(strict_types=1);

namespace App\Filament\Shared\Forms;

use App\Repository\Models\Clientes\Cliente;
use App\Repository\Models\Personas\Persona;
use App\Repository\Queries\Clientes\BuscarClientesQuery;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;

final class SelectorCliente
{
    /**
     * Devuelve un componente Select único estandarizado para seleccionar clientes.
     */
    public static function single(
        string $name = 'cliente_id',
        string $label = 'Asignar Cliente',
        string $placeholder = 'Cliente General / Consumidor Final',
        ?string $dusk = null,
    ): Select {
        $select = Select::make($name)
            ->label($label)
            ->placeholder($placeholder)
            ->selectablePlaceholder(true)
            ->searchable()
            ->getSearchResultsUsing(fn (string $search): array => app(BuscarClientesQuery::class)->paraSelect($search))
            ->options(fn (): array => app(BuscarClientesQuery::class)->paraSelect('', 20))
            ->getOptionLabelUsing(function (mixed $value) use ($placeholder): ?string {
                if (! is_numeric($value)) {
                    return $placeholder;
                }

                $cliente = Cliente::query()
                    ->with(['persona.personaNatural', 'persona.personaJuridica', 'tipoCliente'])
                    ->find((int) $value);

                if (! $cliente instanceof Cliente) {
                    $persona = Persona::query()
                        ->with(['personaNatural', 'personaJuridica', 'cliente.tipoCliente'])
                        ->find((int) $value);

                    $cliente = $persona?->cliente;
                }

                $persona = $cliente?->persona;

                if (! $cliente instanceof Cliente || ! $persona instanceof Persona) {
                    return null;
                }

                $nombre = app(ObtenerNombrePersona::class)->ejecutar($persona);
                $tipo = $persona->cliente?->tipoCliente?->nombre;
                $tipoStr = filled($tipo) ? " ({$tipo})" : '';

                $natural = $persona->personaNatural;
                $juridica = $persona->personaJuridica;
                $identificacion = $natural !== null
                    ? $natural->numero_identificacion
                    : ($juridica !== null ? $juridica->numero_identificacion : null);
                $identStr = filled($identificacion) ? " · {$identificacion}" : '';

                return "{$nombre}{$tipoStr}{$identStr}";
            })
            ->preload()
            ->native(false);

        if ($dusk !== null) {
            $select->extraAttributes(['dusk' => $dusk]);
        }

        return $select;
    }

    /**
     * Devuelve un grupo de campos estandarizado para seleccionar cliente y autocompletar datos de contacto.
     *
     * @return array<int, Select|TextInput>
     */
    public static function make(
        string $columnClienteId = 'cliente_id',
        string $columnNombre = 'nombre_cliente',
        string $columnTelefono = 'telefono_cliente',
        string $columnEmail = 'email_cliente',
        int $columnSpan = 1,
        ?string $dusk = null,
    ): array {
        return [
            self::single($columnClienteId, 'Cliente registrado', 'Buscar por nombre, RUC, cédula, razón social o código', $dusk)
                ->afterStateUpdated(function ($state, Set $set) use ($columnNombre, $columnTelefono, $columnEmail): void {
                    if (! is_numeric($state)) {
                        return;
                    }

                    $cliente = Cliente::query()
                        ->with(['persona.personaNatural', 'persona.personaJuridica', 'persona.user'])
                        ->find((int) $state);

                    if (! $cliente instanceof Cliente) {
                        $persona = Persona::query()
                            ->with(['personaNatural', 'personaJuridica', 'user'])
                            ->find((int) $state);

                        $cliente = $persona?->cliente;
                    }

                    $persona = $cliente?->persona;

                    if (! $cliente instanceof Cliente || ! $persona instanceof Persona) {
                        return;
                    }

                    $nombre = app(ObtenerNombrePersona::class)->ejecutar($persona);
                    $telefono = $persona->telefono;
                    $email = $persona->email ?? $persona->user?->email;

                    $set($columnNombre, $nombre);
                    if (filled($telefono)) {
                        $set($columnTelefono, $telefono);
                    }
                    if (filled($email)) {
                        $set($columnEmail, $email);
                    }
                })
                ->live()
                ->columnSpan($columnSpan),

            TextInput::make($columnNombre)
                ->label('Nombre del Cliente / Titular')
                ->placeholder('Nombre completo o Razón Social')
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
