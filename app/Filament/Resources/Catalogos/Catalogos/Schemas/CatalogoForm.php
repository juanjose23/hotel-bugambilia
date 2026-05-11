<?php

namespace App\Filament\Resources\Catalogos\Catalogos\Schemas;

use App\Enums\EstadoCatalogo;
use App\Models\Catalogos\Catalogo;
use App\Models\Catalogos\CatalogoTipo;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rule;

class CatalogoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos generales')
                    ->columnSpanFull()
                    ->columns()
                    ->schema([
                        Select::make('catalogo_tipo_id')
                            ->label('Tipo')
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->prefixIcon(Heroicon::Tag)
                            ->helperText('Selecciona el tipo de catálogo. Determina el conjunto de opciones disponibles.')
                            ->options(fn () => CatalogoTipo::query()->orderBy('nombre')->pluck('nombre', 'id')->all()),

                        Select::make('padre_id')
                            ->label('Padre')
                            ->nullable()
                            ->searchable()
                            ->prefixIcon(Heroicon::Square2Stack)
                            ->helperText('Elemento Categoria opcional para estructuras jerárquicas.')
                            ->options(fn (callable $get) => Catalogo::query()
                                ->when($get('catalogo_tipo_id'), fn ($query, $catalogoTipoId) => $query->where('catalogo_tipo_id', $catalogoTipoId))
                                ->orderBy('nombre')
                                ->pluck('nombre', 'id')
                                ->all())
                            ->rules(fn (callable $get) => [
                                function ($attribute, $value, $fail) use ($get) {
                                    if ($value && $get('id') && (int) $value === (int) $get('id')) {
                                        $fail('El padre no puede ser el mismo registro.');
                                    }
                                },
                            ]),

                        TextInput::make('codigo')
                            ->label('Código')
                            ->required()
                            ->maxLength(50)
                            ->reactive()
                            ->prefixIcon(Heroicon::Hashtag)
                            ->helperText('Código único dentro del tipo; usado en integraciones y seeds.')
                            ->rules(fn (callable $get) => [
                                Rule::unique('catalogos', 'codigo')
                                    ->where(fn ($query) => $query->where('catalogo_tipo_id', $get('catalogo_tipo_id')))
                                    ->ignore($get('id') ?? null),
                            ]),

                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->required()
                            ->maxLength(200)
                            ->prefixIcon(Heroicon::Ticket)
                            ->helperText('Nombre legible que se mostrará en la interfaz.'),

                        Textarea::make('descripcion')
                            ->label('Descripción')

                            ->columnSpanFull()
                            ->helperText('Descripción opcional para documentación interna.'),

                        Grid::make()
                            ->schema([

                                TextInput::make('orden')
                                    ->label('Orden')
                                    ->required()
                                    ->numeric()
                                    ->default(0)
                                    ->prefixIcon(Heroicon::ArrowDownCircle),

                                Select::make('estado')
                                    ->label('Estado')
                                    ->options(EstadoCatalogo::options())
                                    ->default(EstadoCatalogo::Activo->value)
                                    ->required()
                                    ->prefixIcon(Heroicon::CheckCircle)
                                    ->helperText('Controla si el elemento está activo y visible.'),
                            ]),
                    ]),
            ]);
    }
}
