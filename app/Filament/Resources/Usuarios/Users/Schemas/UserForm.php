<?php

namespace App\Filament\Resources\Usuarios\Users\Schemas;

use App\Interactors\Usuarios\Credenciales\GenerarCredencialesUsuario;
use App\Models\Personas\Persona;
use App\Models\User;
use App\Repository\Queries\Usuarios\ObtenerPersonasDisponibles;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class UserForm
{
    public function __construct(
        private readonly ObtenerPersonasDisponibles $personasDisponibles,
        private readonly GenerarCredencialesUsuario $credencialesUsuario,
    ) {}

    public function configure(Schema $schema): Schema
    {
        return $schema->components($this->getSchema());
    }

    /** @return array<int, Htmlable|string> */
    public function getSchema(): array
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('persona_id')
                        ->label('Trabajador')
                        ->options(function (?User $record = null): array {
                            $currentPersonaId = $record?->persona_id
                                ? (int) $record->persona_id
                                : null;

                            return $this->personasDisponibles->ejecutar($currentPersonaId);
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set) {
                            if (! $state) {
                                return;
                            }

                            $persona = Persona::with(['personaNatural', 'pais'])->find($state);

                            if (! ($persona instanceof Persona)) {
                                return;
                            }

                            $credenciales = $this->credencialesUsuario->execute($persona);

                            $set('name', $credenciales['name']);
                            $set('email', $credenciales['email']);
                        })
                        ->prefixIcon(Heroicon::User),

                    TextInput::make('name')
                        ->label('Nombre de usuario')
                        ->required()
                        ->maxLength(255)
                        ->prefixIcon(Heroicon::Identification),

                    TextInput::make('email')
                        ->label('Correo electrónico')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true)
                        ->prefixIcon(Heroicon::Envelope),

                    TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->revealable()
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->maxLength(255)
                        ->prefixIcon(Heroicon::LockClosed),

                    Toggle::make('is_admin')
                        ->label('¿Es Administrador / Acceso al Panel?')
                        ->default(true)
                        ->required(),
                ]),

            Section::make('Roles y Permisos')
                ->description('Asigne los roles para este usuario')
                ->columnSpanFull()
                ->schema([
                    Select::make('roles')
                        ->label('Roles')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->prefixIcon(Heroicon::ShieldCheck),
                ]),
        ];
    }
}
