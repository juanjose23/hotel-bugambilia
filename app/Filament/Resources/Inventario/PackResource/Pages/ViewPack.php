<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\PackResource\Pages;

use App\Filament\Resources\Inventario\PackResource\PackResource;
use App\Models\Catalogos\Producto;
use App\Models\Habitaciones\Habitacion;
use App\Models\Inventario\ProductoKit;
use App\Models\Inventario\Stock;
use App\Support\CachedOptions;
use App\UseCases\Habitaciones\Mutations\AsignarPackAHabitacion;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * @property Producto $record
 */
class ViewPack extends ViewRecord
{
    protected static string $resource = PackResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),

            Action::make('surtir')
                ->label('Surtir Pack a Habitación')
                ->icon(Heroicon::ArchiveBoxArrowDown)
                ->color('success')
                ->modalHeading('Surtir Pack a Habitación')
                ->modalDescription("Seleccione la habitación destino y la bodega de origen para surtir el pack \"{$this->record->nombre}\".")
                ->schema([
                    Select::make('habitacion_id')
                        ->label('Habitación destino')
                        ->options(Habitacion::pluck('nombre', 'id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('bodega_origen_id')
                        ->label('Bodega de origen')
                        ->options(CachedOptions::ubicacionesAlmacen())
                        ->searchable()
                        ->preload()
                        ->required(),

                    TextInput::make('cantidad_packs')
                        ->label('Cantidad de packs')
                        ->numeric()
                        ->required()
                        ->minValue(1)
                        ->default(1),

                    Repeater::make('items_preview')
                        ->label('Items incluidos en el pack')
                        ->schema([
                            TextInput::make('variante')->disabled(),
                            TextInput::make('cantidad')->disabled(),
                            TextInput::make('stock')->disabled()->label('Stock en bodega'),
                            TextInput::make('estado')->disabled()->hiddenLabel(),
                        ])
                        ->disabled()
                        ->columns(4)
                        ->defaultItems(0)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false)
                        ->visible(fn () => $this->record->kitItems()->count() > 0),
                ])
                ->mountUsing(function ($form, $record) {
                    $items = ProductoKit::with('variante')
                        ->where('producto_padre_id', $record->id)
                        ->get();
                    $preview = [];
                    foreach ($items as $item) {
                        $stockTotal = Stock::where('producto_variante_id', $item->producto_variante_id)
                            ->sum('cantidad');
                        $variante = $item->variante;
                        $suficiente = $stockTotal >= (float) $item->cantidad;
                        $preview[] = [
                            'variante' => $variante !== null ? "{$variante->nombre_variante} ({$variante->codigo})" : 'N/A',

                            'cantidad' => "{$item->cantidad} x pack",
                            'stock' => (string) $stockTotal,
                            'estado' => $suficiente ? '✅ Suficiente' : '❌ Insuficiente',
                        ];
                    }
                    $form->fill(['items_preview' => $preview]);
                })
                ->action(function (array $data, Action $action): void {
                    try {
                        app(AsignarPackAHabitacion::class)->execute(
                            habitacionId: (int) $data['habitacion_id'],
                            productoPackId: (int) $this->record->id,
                            bodegaOrigenId: (int) $data['bodega_origen_id'],
                            cantidadPacks: (float) $data['cantidad_packs'],
                            creadoPorId: (int) auth()->id(),
                        );

                        Notification::make()
                            ->title('Pack surtido exitosamente')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al surtir pack')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();

                        $action->halt();
                    }
                }),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $items = $this->record->kitItems()->with('variante.producto')->get();
        $stockRows = [];

        foreach ($items as $item) {
            $stockTotal = Stock::where('producto_variante_id', $item->producto_variante_id)
                ->sum('cantidad');
            $necesario = (float) $item->cantidad;
            $suficiente = $stockTotal >= $necesario;

            $stockRows[] = [
                'producto' => ($item->variante && $item->variante->producto) ? $item->variante->producto->nombre : '—',
                'variante' => $item->variante->nombre_variante ?? '—',
                'necesario' => (string) $necesario,
                'disponible' => (string) $stockTotal,
                'estado' => $suficiente ? '✅ Suficiente' : '❌ Insuficiente',
            ];
        }

        $data['_stock_items'] = $stockRows;

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        $schema = parent::form($schema);

        $existingComponents = $schema->getComponents();

        $schema->components([
            ...$existingComponents,

            Section::make('Stock Disponible en Bodegas')
                ->description('Inventario actual disponible para cada componente del pack (todas las bodegas).')
                ->columnSpanFull()
                ->visible(fn () => $this->record->kitItems()->count() > 0)
                ->schema([
                    Repeater::make('_stock_items')
                        ->schema([
                            TextInput::make('producto')
                                ->label('Producto')
                                ->disabled()
                                ->columnSpan(2),

                            TextInput::make('variante')
                                ->label('Variante')
                                ->disabled()
                                ->columnSpan(2),

                            TextInput::make('necesario')
                                ->label('Necesario')
                                ->disabled()
                                ->numeric()
                                ->columnSpan(1),

                            TextInput::make('disponible')
                                ->label('Disponible')
                                ->disabled()
                                ->numeric()
                                ->columnSpan(1),

                            TextInput::make('estado')
                                ->label('Estado')
                                ->disabled()
                                ->columnSpan(1)
                                ->extraAttributes(['class' => 'text-center font-bold']),
                        ])
                        ->columns(7)
                        ->defaultItems(0)
                        ->addable(false)
                        ->deletable(false)
                        ->reorderable(false),
                ]),
        ]);

        return $schema;
    }
}
