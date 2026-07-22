<?php

namespace App\Filament\Resources\Compras\DevolucionCompra\Schemas;

use App\Enums\Compras\EstadoOrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Inventario\Lote;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class DevolucionCompraForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Cabecera de Devolución')
                    ->description('Vincule la devolución a una orden de compra y registre los detalles de la salida')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('codigo')
                            ->label('Código Devolución')
                            ->disabled()
                            ->dehydrated()
                            ->placeholder('DEV-YYYY-NNN')
                            ->prefixIcon(Heroicon::QrCode),

                        Select::make('orden_compra_id')
                            ->label('Orden de Compra')
                            ->relationship(
                                name: 'ordenCompra',
                                titleAttribute: 'codigo',
                                modifyQueryUsing: fn (Builder $query) => $query->whereIn('estado', [
                                    EstadoOrdenCompra::Recibida,
                                    EstadoOrdenCompra::Emitida,
                                ])
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->prefixIcon(Heroicon::DocumentText)
                            ->afterStateUpdated(function ($state, $set) {
                                $set('recepcion_compra_id', null);
                                $set('items', []);
                            }),

                        Select::make('recepcion_compra_id')
                            ->label('Recepción de Compra (Opcional)')
                            ->options(fn ($get) => $get('orden_compra_id')
                                ? RecepcionCompra::where('orden_compra_id', $get('orden_compra_id'))->pluck('codigo', 'id')
                                : []
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->prefixIcon(Heroicon::ArchiveBox),

                        DatePicker::make('fecha_devolucion')
                            ->label('Fecha de Devolución')
                            ->default(now())
                            ->required()
                            ->prefixIcon(Heroicon::Calendar),

                        TextInput::make('documento_externo')
                            ->label('Documento Externo / Guía / Nota de Crédito')
                            ->placeholder('Número o referencia...')
                            ->maxLength(100)
                            ->prefixIcon(Heroicon::Hashtag),

                        Select::make('creado_por_id')
                            ->label('Responsable')
                            ->relationship('creador', 'name')
                            ->default(auth()->id())
                            ->required()
                            ->prefixIcon(Heroicon::User),

                        Textarea::make('motivo')
                            ->label('Motivo / Justificación de la Devolución (Obligatorio)')
                            ->placeholder('Escriba detalladamente el motivo del rechazo o error...')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Detalle de Ítems a Devolver')
                    ->description('Seleccione los lotes de inventario que desea retornar al proveedor')
                    ->icon(Heroicon::Trash)
                    ->columnSpanFull()
                    ->schema([
                        Repeater::make('items')
                            ->hiddenLabel()
                            ->relationship('items')
                            ->minItems(1)
                            ->schema([
                                Select::make('lote_id')
                                    ->label('Lote de Inventario')
                                    ->options(function ($get) {
                                        $ordenId = $get('../../orden_compra_id');
                                        $recepcionId = $get('../../recepcion_compra_id');

                                        $query = Lote::query()
                                            ->with(['producto', 'ubicacion'])
                                            ->where('cantidad_disponible', '>', 0);

                                        if ($recepcionId) {
                                            $query->whereHas('recepcionItem', fn ($q) => $q->where('recepcion_id', $recepcionId));
                                        } elseif ($ordenId) {
                                            $query->whereHas('recepcionItem.ordenItem', fn ($q) => $q->where('orden_compra_id', $ordenId));
                                        }

                                        return $query->get()->mapWithKeys(function (Lote $lote) {
                                            $nombreUbicacion = $lote->ubicacion ? $lote->ubicacion->nombre : 'N/A';
                                            $prodNombre = $lote->producto ? $lote->producto->nombre : 'N/A';

                                            return [$lote->id => '['.$lote->codigo_lote.'] '.$prodNombre.' — Disp: '.$lote->cantidad_disponible.' ('.$nombreUbicacion.')'];
                                        });
                                    })
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->columnSpan(6)
                                    ->prefixIcon(Heroicon::Inbox)
                                    ->afterStateUpdated(function ($state, $set) {
                                        if (! $state) {
                                            $set('producto_id', null);
                                            $set('producto_variante_id', null);
                                            $set('unidad_medida_id', null);
                                            $set('cantidad_devolver', 0);

                                            return;
                                        }

                                        /** @var Lote|null $lote */
                                        $lote = Lote::find($state);
                                        if ($lote) {
                                            $set('producto_id', $lote->producto_id);
                                            $set('producto_variante_id', $lote->producto_variante_id);
                                            $set('recepcion_item_id', $lote->recepcion_item_id);
                                            $set('cantidad_devolver', $lote->cantidad_disponible);

                                            $umId = $lote->recepcionItem ? $lote->recepcionItem->unidad_medida_id : ($lote->producto ? $lote->producto->unidad_medida_id : null);
                                            $set('unidad_medida_id', $umId);
                                        }
                                    }),

                                TextInput::make('cantidad_devolver')
                                    ->label('Cant. a Devolver')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0.01)
                                    ->maxValue(function ($get) {
                                        $loteId = $get('lote_id');
                                        if ($loteId) {
                                            $loteRec = Lote::find((int) $loteId);

                                            return $loteRec ? (float) $loteRec->cantidad_disponible : 0.0;
                                        }

                                        return 99999.0;
                                    })
                                    ->columnSpan(4)
                                    ->prefixIcon(Heroicon::Hashtag),

                                Hidden::make('producto_id'),
                                Hidden::make('producto_variante_id'),
                                Hidden::make('unidad_medida_id'),
                                Hidden::make('recepcion_item_id'),
                            ])
                            ->columns(10)
                            ->addActionLabel('Agregar otro lote a la devolución')
                            ->defaultItems(1),
                    ]),
            ]);
    }
}
