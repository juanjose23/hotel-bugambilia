<?php

declare(strict_types=1);

namespace App\Filament\Resources\Catalogos\Productos\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Catalogos\TipoProducto;
use App\Enums\Shared\EstadoGeneral;
use App\Filament\Shared\Forms\CategoriaSelect;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ProductoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Información General')
                    ->description('Detalles del producto')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('nombre')
                            ->label('Nombre')
                            ->placeholder('Nombre del producto')
                            ->maxLength(200)
                            ->required()
                            ->prefixIcon('heroicon-o-tag')
                            ->helperText('Nombre único dentro de la categoría seleccionada.'),
                        CategoriaSelect::make(CatalogoTipo::CATEGORIA_PRODUCTO)
                            ->placeholder('Seleccionar categoría')
                            ->required()
                            ->prefixIcon(Heroicon::ArchiveBox)
                            ->helperText('Categoría a la que pertenece el producto.'),

                        CategoriaSelect::make(
                            tipo: CatalogoTipo::MARCA,
                            column: 'marca_id',
                            label: 'Marca',
                        )
                            ->placeholder('Seleccionar marca')
                            ->nullable()
                            ->prefixIcon(Heroicon::ShoppingBag)
                            ->helperText('Marca del producto (opcional).'),

                        CategoriaSelect::make(
                            tipo: CatalogoTipo::UNIDAD_MEDIDA,
                            column: 'unidad_medida_id',
                            label: 'Unidad de Medida',
                        )
                            ->placeholder('Seleccionar unidad')
                            ->nullable()
                            ->prefixIcon(Heroicon::Scale)
                            ->helperText('Unidad de medida base (ej. Unidad, Litro, Kg).'),
                        Select::make('tipo')
                            ->label('Tipo de Producto')
                            ->options(TipoProducto::options())
                            ->default(TipoProducto::NoPerecedero->value)
                            ->required()
                            ->prefixIcon(Heroicon::ArchiveBox)
                            ->helperText('Indica si el producto es perecedero o de larga duración.'),
                        TextInput::make('rendimiento_porciones')
                            ->label('Rendimiento de receta')
                            ->numeric()
                            ->default(1)
                            ->minValue(0.01)
                            ->step(0.01)
                            ->suffix('porciones')
                            ->required()
                            ->helperText('Cuando este producto sea una receta de cocina, indica cuántas porciones rinde la preparación base.'),
                        Textarea::make('descripcion')
                            ->label('Descripción')
                            ->placeholder('Descripción opcional del producto')
                            ->nullable()
                            ->columnSpanFull()
                            ->helperText('Detalles adicionales del producto.'),
                        Select::make('estado')
                            ->label('Estado')
                            ->options(EstadoGeneral::options())
                            ->default(EstadoGeneral::Activo->value)
                            ->required()
                            ->prefixIcon(Heroicon::CheckCircle),
                        FileUpload::make('imagen_upload')
                            ->label('Imagen del Producto')
                            ->image()
                            ->disk('public')
                            ->directory('productos')
                            ->maxSize(4096)
                            ->columnSpanFull()
                            ->helperText('Sube una imagen representativa del producto.'),
                    ]),
            ]);
    }
}
