<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\TurnoResource\Schemas;

use App\Models\Catalogos\Ubicacion;
use App\Models\Colaboradores\Colaborador;
use App\UseCases\Shared\Queries\ObtenerNombrePersona;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class TurnoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del Turno')
                    ->description('Registre el nombre del bloque de turno, horarios y responsables.')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del Turno')
                            ->placeholder('Ej: Turno Mañana A')
                            ->required()
                            ->maxLength(100)
                            ->prefixIcon(Heroicon::Sparkles),

                        Toggle::make('estado')
                            ->label('Turno Activo')
                            ->default(true)
                            ->inline(false),

                        Select::make('lider_id')
                            ->label('Líder de Turno')
                            ->placeholder('Seleccione el líder')
                            ->options(self::getColaboradoresOptions())
                            ->searchable()
                            ->required()
                            ->native(false)
                            ->prefixIcon(Heroicon::User),

                        Select::make('apoyo_id')
                            ->label('Colaborador de Apoyo')
                            ->placeholder('Seleccione el apoyo (opcional)')
                            ->options(self::getColaboradoresOptions())
                            ->searchable()
                            ->native(false)
                            ->prefixIcon(Heroicon::UserGroup),

                        Select::make('carritos_ids')
                            ->label('Carritos / Bodegas de Limpieza')
                            ->placeholder('Seleccione los carritos o bodegas')
                            ->options(fn () => Ubicacion::whereIn('tipo', ['almacen', 'bodega', 'zona'])->pluck('nombre', 'id')->toArray())
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false)
                            ->prefixIcon(Heroicon::ShoppingBag),

                        TimePicker::make('hora_inicio')
                            ->label('Hora de Inicio')
                            ->required()
                            ->prefixIcon(Heroicon::Clock),

                        TimePicker::make('hora_fin')
                            ->label('Hora de Fin')
                            ->required()
                            ->prefixIcon(Heroicon::Clock),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    protected static function getColaboradoresOptions(): array
    {
        /** @var array<int, string> $options */
        $options = Colaborador::query()
            ->with(['persona.personaNatural', 'persona.personaJuridica'])
            ->get()
            ->mapWithKeys(function (Colaborador $c) {
                $p = $c->persona;
                $name = $p
                    ? ObtenerNombrePersona::desde($p)
                    : "Colaborador #{$c->id}";

                return [$c->id => $name];
            })
            ->toArray();

        return $options;
    }
}
