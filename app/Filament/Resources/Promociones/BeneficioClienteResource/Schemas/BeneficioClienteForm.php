<?php

declare(strict_types=1);

namespace App\Filament\Resources\Promociones\BeneficioClienteResource\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Promociones\TipoBeneficioCliente;
use App\Enums\Promociones\TipoReglaBeneficioCliente;
use App\Repository\Models\Promociones\PromocionBeneficio;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class BeneficioClienteForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Beneficio')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código')
                            ->default(fn (?PromocionBeneficio $record) => $record !== null
                                ? $record->codigo
                                : 'BEN-'.mb_strtoupper(Str::random(6), 'UTF-8'))
                            ->required()
                            ->maxLength(40)
                            ->unique(ignoreRecord: true)
                            ->prefixIcon(Heroicon::Key),

                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(150)
                            ->prefixIcon(Heroicon::Tag),

                        Select::make('segmento_cliente_id')
                            ->label('Segmento de cliente')
                            ->relationship(
                                name: 'segmentoCliente',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                    'catalogoTipo',
                                    fn (Builder $q) => $q->where('codigo', CatalogoTipo::TIPO_CLIENTE->value)
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->prefixIcon(Heroicon::UserGroup)
                            ->helperText('Déjelo vacío si aplica a todos los clientes.'),

                        Select::make('promocion_id')
                            ->label('Promoción vinculada')
                            ->relationship('promocion', 'nombre')
                            ->searchable()
                            ->preload()
                            ->prefixIcon(Heroicon::Gift)
                            ->helperText('Opcional. Útil cuando el beneficio nace de una promoción concreta.'),

                        Select::make('tipo')
                            ->label('Tipo de beneficio')
                            ->options(self::tiposBeneficio())
                            ->required()
                            ->native(false)
                            ->prefixIcon(Heroicon::CheckBadge),

                        TextInput::make('valor')
                            ->label('Valor')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->prefixIcon(Heroicon::CurrencyDollar),

                        Toggle::make('es_porcentaje')
                            ->label('Valor porcentual')
                            ->default(true)
                            ->inline(false),

                        Toggle::make('combinable')
                            ->label('Combinable')
                            ->default(false)
                            ->inline(false),

                        TextInput::make('limite_usos_por_cliente')
                            ->label('Límite por cliente')
                            ->integer()
                            ->minValue(1)
                            ->prefixIcon(Heroicon::User),

                        Toggle::make('activo')
                            ->label('Activo')
                            ->default(true)
                            ->inline(false),

                        DatePicker::make('fecha_inicio')
                            ->label('Válido desde')
                            ->prefixIcon(Heroicon::Calendar),

                        DatePicker::make('fecha_fin')
                            ->label('Válido hasta')
                            ->prefixIcon(Heroicon::Calendar),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),

                Section::make('Reglas')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('reglas')
                            ->relationship('reglas')
                            ->hiddenLabel()
                            ->schema([
                                Select::make('tipo_regla')
                                    ->label('Regla')
                                    ->options(self::tiposRegla())
                                    ->required()
                                    ->native(false)
                                    ->columnSpan(3),

                                Select::make('operador')
                                    ->label('Operador')
                                    ->options([
                                        '>=' => 'Mayor o igual',
                                        '<=' => 'Menor o igual',
                                        '=' => 'Igual',
                                        '!=' => 'Diferente',
                                    ])
                                    ->default('>=')
                                    ->required()
                                    ->columnSpan(2),

                                TextInput::make('valor_numerico')
                                    ->label('Valor numérico')
                                    ->numeric()
                                    ->step(0.01)
                                    ->columnSpan(2),

                                TextInput::make('valor_texto')
                                    ->label('Valor texto')
                                    ->maxLength(150)
                                    ->columnSpan(3),

                                Toggle::make('obligatoria')
                                    ->label('Obligatoria')
                                    ->default(true)
                                    ->inline(false)
                                    ->columnSpan(2),
                            ])
                            ->columns(12)
                            ->defaultItems(0)
                            ->collapsible()
                            ->addActionLabel('Agregar regla'),
                    ]),
            ]);
    }

    /** @return array<string, string> */
    private static function tiposBeneficio(): array
    {
        $opciones = [];

        foreach (TipoBeneficioCliente::cases() as $tipo) {
            $opciones[$tipo->value] = $tipo->getLabel();
        }

        return $opciones;
    }

    /** @return array<string, string> */
    private static function tiposRegla(): array
    {
        $opciones = [];

        foreach (TipoReglaBeneficioCliente::cases() as $tipo) {
            $opciones[$tipo->value] = $tipo->getLabel();
        }

        return $opciones;
    }
}
