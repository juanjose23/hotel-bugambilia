<?php

namespace App\Filament\Resources\Catalogos\Productos\Schemas;

use App\Enums\Catalogos\CatalogoTipo;
use App\Enums\Catalogos\TipoProducto;
use App\Enums\Shared\EstadoGeneral;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

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
                        Select::make('categoria_id')
                            ->label('Categoría')
                            ->placeholder('Seleccionar categoría')
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
                            ->prefixIcon(Heroicon::ArchiveBox)
                            ->helperText('Categoría a la que pertenece el producto.'),
                        Select::make('marca_id')
                            ->label('Marca')
                            ->placeholder('Seleccionar marca')
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
                            ->prefixIcon(Heroicon::ShoppingBag)
                            ->helperText('Marca del producto (opcional).'),

                        Select::make('unidad_medida_id')
                            ->label('Unidad de Medida')
                            ->placeholder('Seleccionar unidad')
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
