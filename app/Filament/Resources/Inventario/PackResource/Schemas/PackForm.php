<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\PackResource\Schemas;

use App\BusinessLogic\Inventario\Data\Pack\VarianteData;
use App\Enums\Catalogos\CatalogoTipo;
use App\Filament\Shared\Forms\CategoriaSelect;
use App\Repository\Queries\Inventario\Pack\ObtenerVariantesParaPackQuery;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

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
        /** @var array<int, string> $variantesMap */
        $variantesMap = $this->obtenerVariantes->ejecutar()
            ->mapWithKeys(fn (VarianteData $v) => [(int) $v->id => (string) $v->label])
            ->toArray();

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

                        CategoriaSelect::make(CatalogoTipo::CATEGORIA_PRODUCTO)
                            ->required()
                            ->prefixIcon(Heroicon::Folder),

                        CategoriaSelect::make(
                            tipo: CatalogoTipo::MARCA,
                            column: 'marca_id',
                            label: 'Marca',
                        )
                            ->nullable()
                            ->prefixIcon(Heroicon::Tag),

                        CategoriaSelect::make(
                            tipo: CatalogoTipo::UNIDAD_MEDIDA,
                            column: 'unidad_medida_id',
                            label: 'Unidad de Medida',
                        )
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
                                    ->options($variantesMap)
                                    ->getOptionLabelUsing(fn (mixed $value): ?string => is_numeric($value)
                                        ? ($variantesMap[(int) $value] ?? (string) $value)
                                        : null)
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
                            ->itemLabel(function (array $state) use ($variantesMap): string {
                                $varianteId = $state['producto_variante_id'] ?? null;

                                if (! is_numeric($varianteId)) {
                                    return 'Nuevo Item';
                                }

                                return $variantesMap[(int) $varianteId] ?? 'Item #'.$varianteId;
                            }),
                    ]),
            ]);
    }
}
