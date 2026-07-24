<?php

namespace App\Filament\Resources\Colaboradores\ColaboradorSalario\Schemas;

use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Concerns\InyectaDesdeContenedor;
use App\Repository\Models\Colaboradores\ColaboradorSalario;
use App\Repository\Queries\Shared\ObtenerNombrePersona;
use Closure;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ColaboradorSalarioForm
{
    use InyectaDesdeContenedor;

    public static function configure(Schema $schema): Schema
    {
        return static::make()->doConfigure($schema);
    }

    private function doConfigure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Remuneración')
                ->description('Configuración salarial del colaborador.')
                ->columnSpanFull()
                ->schema([
                    Select::make('colaborador_id')
                        ->relationship('colaborador')
                        ->getOptionLabelFromRecordUsing(
                            fn ($record) => $record->codigo.' - '.ObtenerNombrePersona::desde($record->persona)
                        )
                        ->searchable()
                        ->preload()
                        ->required()
                        ->prefixIcon(Heroicon::User)
                        ->helperText('Seleccione el colaborador.')
                        ->columnSpanFull(),

                    TextInput::make('salario')
                        ->label('Salario Mensual')
                        ->placeholder('Ej. 15000.00')
                        ->numeric()
                        ->required()
                        ->prefix('NIO')
                        ->prefixIcon(Heroicon::Banknotes)
                        ->helperText('Monto del salario mensual en córdobas.')
                        ->rules([
                            function (Get $get): Closure {
                                return function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                    $colaboradorId = $get('colaborador_id');

                                    if (! $colaboradorId) {
                                        return;
                                    }

                                    $salarioAnterior = ColaboradorSalario::where('colaborador_id', $colaboradorId)
                                        ->where('estado', 1)
                                        ->latest('fecha_inicio')
                                        ->value('salario');

                                    $salarioAnteriorStr = is_scalar($salarioAnterior) ? (string) $salarioAnterior : '0';
                                    if ($salarioAnterior && $value < $salarioAnterior) {
                                        $fail('El nuevo salario no puede ser menor al salario anterior (NIO '.$salarioAnteriorStr.').');
                                    }
                                };
                            },
                        ]),

                    Select::make('estado')
                        ->label('Estado')
                        ->options(EstadoGeneral::options())
                        ->default(EstadoGeneral::Activo->value)
                        ->required()
                        ->selectablePlaceholder(false)
                        ->prefixIcon(Heroicon::CheckCircle)
                        ->helperText('Indica si este es el salario actual.'),

                    DatePicker::make('fecha_inicio')
                        ->label('Fecha de Inicio')
                        ->default(now())
                        ->required()
                        ->prefixIcon(Heroicon::Calendar)
                        ->helperText('Fecha de vigencia.'),

                    DatePicker::make('fecha_fin')
                        ->label('Fecha de Fin')
                        ->prefixIcon(Heroicon::CalendarDays)
                        ->helperText('Opcional.'),
                ])->columns(2),
        ]);
    }
}
