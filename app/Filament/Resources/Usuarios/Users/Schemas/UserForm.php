<?php

namespace App\Filament\Resources\Usuarios\Users\Schemas;

use App\Models\Personas\Persona;
use App\Models\User;
use App\UseCases\Usuarios\Mutations\GenerarCredencialesUsuario;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;

class UserForm
{
    public function configure(Schema $schema): Schema
    {
        return $schema->components(self::getSchema());
    }

    /** @return array<int, Htmlable|string> */
    public static function getSchema(): array
    {
        return [
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    Select::make('persona_id')
                        ->label('Trabajador')
                        ->options(function (?User $record = null): array {
                            $excludedIds = User::whereNotNull('persona_id')
                                ->pluck('persona_id')
                                ->toArray();

                            $excludedIds = array_map(fn (mixed $id): int => is_numeric($id) ? (int) $id : 0, $excludedIds);

                            if ($record && $record->persona_id) {
                                $excludedIds = array_diff($excludedIds, [(int) $record->persona_id]);
                            }

                            $personas = Persona::whereHas('colaborador')
                                ->with(['colaborador', 'personaNatural'])
                                ->whereNotIn('id', $excludedIds)
                                ->get();

                            $result = [];

                            foreach ($personas as $p) {
                                $result[$p->id] = ($p->colaborador ? $p->colaborador->codigo : '').' - '.
                                    $p->primer_nombre.' '.
                                    ($p->segundo_nombre ?? '').' '.
                                    ($p->personaNatural ? $p->personaNatural->primer_apellido : '').' '.
                                    ($p->personaNatural ? $p->personaNatural->segundo_apellido : '');
                            }

                            return $result;
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

                            $credenciales = app(GenerarCredencialesUsuario::class)->execute($persona);

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
