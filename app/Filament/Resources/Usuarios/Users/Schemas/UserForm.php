<?php

declare(strict_types=1);

namespace App\Filament\Resources\Usuarios\Users\Schemas;

use App\Interactors\Usuarios\Credenciales\GenerarCredencialesUsuario;
use App\Repository\Models\Personas\Persona;
use App\Repository\Models\User;
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
            Section::make('Identidad y Credenciales')
                ->description('Seleccione la persona vinculada o configure los datos de acceso')
                ->icon(Heroicon::UserCircle)
                ->columnSpanFull()
                ->schema([
                    Select::make('persona_id')
                        ->label('Persona / Trabajador')
                        ->placeholder('Buscar por nombre o código de colaborador...')
                        ->options(function (?User $record = null): array {
                            $currentPersonaId = $record?->persona_id
                                ? (int) $record->persona_id
                                : null;

                            return $this->personasDisponibles->ejecutar($currentPersonaId);
                        })
                        ->getOptionLabelUsing(function (mixed $value): ?string {
                            if (! $value) {
                                return null;
                            }

                            $persona = Persona::query()
                                ->with(['colaborador', 'personaNatural'])
                                ->find($value);

                            if (! ($persona instanceof Persona)) {
                                return null;
                            }

                            $colaboradorCodigo = $persona->colaborador ? $persona->colaborador->codigo : '';
                            $natural = $persona->personaNatural;

                            $partes = array_filter([
                                $persona->primer_nombre,
                                $persona->segundo_nombre ?? '',
                                $natural ? $natural->primer_apellido : '',
                                $natural ? $natural->segundo_apellido : '',
                            ]);

                            $nombreCompleto = implode(' ', $partes);

                            return filled($colaboradorCodigo)
                                ? "{$colaboradorCodigo} - {$nombreCompleto}"
                                : $nombreCompleto;
                        })
                        ->searchable()
                        ->preload()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function ($state, $set): void {
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
                        ->prefixIcon(Heroicon::UserCircle)
                        ->columnSpanFull(),

                    Grid::make(2)
                        ->schema([
                            TextInput::make('name')
                                ->label('Nombre de usuario')
                                ->placeholder('Ej: juan.perez')
                                ->required()
                                ->maxLength(255)
                                ->prefixIcon(Heroicon::Identification),

                            TextInput::make('email')
                                ->label('Correo electrónico')
                                ->placeholder('usuario@hotelbugambilias.com')
                                ->email()
                                ->required()
                                ->maxLength(255)
                                ->unique(ignoreRecord: true)
                                ->prefixIcon(Heroicon::Envelope),
                        ]),

                    TextInput::make('password')
                        ->label('Contraseña')
                        ->password()
                        ->placeholder('Dejar en blanco para no cambiar')
                        ->helperText(fn (string $operation): string => $operation === 'create'
                            ? 'Mínimo 8 caracteres recomendados'
                            : 'Dejar en blanco para mantener la contraseña actual')
                        ->formatStateUsing(fn (): string => '')
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->revealable()
                        ->dehydrated(fn (?string $state): bool => filled($state))
                        ->maxLength(255)
                        ->prefixIcon(Heroicon::LockClosed)
                        ->columnSpanFull(),
                ]),

            Section::make('Seguridad y Roles')
                ->description('Asigne los roles de Spatie y configure los privilegios del sistema')
                ->icon(Heroicon::ShieldCheck)
                ->columnSpanFull()
                ->schema([
                    Select::make('roles')
                        ->label('Roles de Usuario')
                        ->placeholder('Seleccione roles...')
                        ->relationship('roles', 'name')
                        ->multiple()
                        ->preload()
                        ->searchable()
                        ->prefixIcon(Heroicon::ShieldCheck)
                        ->columnSpanFull(),

                    Toggle::make('is_admin')
                        ->label('¿Acceso al Panel Administrativo?')
                        ->helperText('Permite al usuario iniciar sesión en el panel de gestión Filament')
                        ->default(true)
                        ->required()
                        ->inline(false)
                        ->columnSpanFull(),
                ]),
        ];
    }
}
