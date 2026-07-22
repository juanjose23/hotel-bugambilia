<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\PackResource\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Repository\Queries\Inventario\Pack\ObtenerVariantesParaPackQuery;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

readonly class PackForm
{
    public function __construct(
        private ObtenerVariantesParaPackQuery $obtenerVariantes,
    ) {}

    public static function configure(Schema $schema): Schema
    {
        return app(static::class)->doConfigure($schema);
    }

    private function doConfigure(Schema $schema): Schema
    {
        $variantes = $this->obtenerVariantes->ejecutar()
            ->mapWithKeys(fn (object $v) => [$v->id => $v->label]);

        return $schema
            ->components([
                Section::make('Información del Pack')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre del Pack')
                            ->required()
                            ->maxLength(255)
                            ->prefixIcon(Heroicon::Cube),

                        Select::make('categoria_id')
                            ->label('Categoría')
                            ->relationship(
                                name: 'categoria',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                    'catalogoTipo',
                                    fn (Builder $q) => $q->where('codigo', CatalogoTipo::CATEGORIA_PRODUCTO->value)
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon(Heroicon::Folder),

                        Select::make('marca_id')
                            ->label('Marca')
                            ->relationship(
                                name: 'marca',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                    'catalogoTipo',
                                    fn (Builder $q) => $q->where('codigo', CatalogoTipo::MARCA->value)
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->prefixIcon(Heroicon::Tag),

                        Select::make('unidad_medida_id')
                            ->label('Unidad de Medida')
                            ->relationship(
                                name: 'unidadMedida',
                                titleAttribute: 'nombre',
                                modifyQueryUsing: fn (Builder $query) => $query->whereHas(
                                    'catalogoTipo',
                                    fn (Builder $q) => $q->where('codigo', CatalogoTipo::UNIDAD_MEDIDA->value)
                                )
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->prefixIcon(Heroicon::Scale),

                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->nullable()
                            ->maxLength(1000)
                            ->columnSpanFull(),

                        Hidden::make('tipo')
                            ->default(2),

                        Hidden::make('estado')
                            ->default(1),
                    ]),

                Section::make('Items del Pack')
                    ->description('Seleccione los productos y variantes que componen este pack.')
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('kitItems')
                            ->relationship('kitItems')
                            ->schema([
                                Select::make('producto_variante_id')
                                    ->label('Producto / Variante')
                                    ->options(fn () => $variantes)
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->columnSpan(3),

                                TextInput::make('cantidad')
                                    ->label('Cantidad')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.0001)
                                    ->default(1)
                                    ->columnSpan(1),

                                TextInput::make('talla')
                                    ->label('Talla')
                                    ->nullable()
                                    ->maxLength(20)
                                    ->placeholder('S, M, L, XL…')
                                    ->columnSpan(1),
                            ])
                            ->columns(5)
                            ->defaultItems(0)
                            ->collapsible()
                            ->itemLabel(fn (array $state): string => isset($state['producto_variante_id'])
                                ? ($variantes->get($state['producto_variante_id']) ?? 'Item')
                                : 'Nuevo Item'),
                    ]),
            ]);
    }
}
