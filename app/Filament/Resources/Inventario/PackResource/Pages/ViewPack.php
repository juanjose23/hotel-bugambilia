<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\PackResource\Pages;

use App\Filament\Resources\Inventario\PackResource\PackResource;
use App\Filament\Shared\Forms\ColaboradorSelect;
use App\Interactors\Habitaciones\AsignarPackAHabitacion;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Queries\Inventario\Pack\ObtenerStockItemsPackQuery;
use App\Support\CachedOptions;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;

/**
 * @property Producto $record
 */
class ViewPack extends ViewRecord
{
    protected AsignarPackAHabitacion $asignarPackAHabitacion;

    protected ObtenerStockItemsPackQuery $obtenerStockItemsPack;

    public function boot(
        AsignarPackAHabitacion $asignarPackAHabitacion,
        ObtenerStockItemsPackQuery $obtenerStockItemsPack,
    ): void {
        $this->asignarPackAHabitacion = $asignarPackAHabitacion;
        $this->obtenerStockItemsPack = $obtenerStockItemsPack;
    }

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

                    ColaboradorSelect::make('colaborador_id')
                        ->label('Colaborador que llevó el pack'),

                    Repeater::make('items_preview')
                        ->label('Items incluidos en el pack')
                        ->schema([
                            TextInput::make('variante')->disabled(),
                            TextInput::make('cantidad')->disabled(),
                            TextInput::make('stock')->disabled()->label('Stock en bodega'),
                            TextInput::make('estado')
                                ->disabled()
                                ->hiddenLabel()
                                ->prefixIcon(fn ($state) => $state === 'Suficiente' ? Heroicon::CheckCircle : Heroicon::XCircle)
                                ->prefixIconColor(fn ($state) => $state === 'Suficiente' ? Color::Green : Color::Red),
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
                    $stockItems = $this->obtenerStockItemsPack->ejecutar($record->id);
                    $preview = [];

                    foreach ($stockItems as $item) {
                        $suficiente = $item->stockTotal >= $item->cantidadNecesaria;
                        $preview[] = [
                            'variante' => $item->nombreVariante.' ('.$item->codigo.')',
                            'cantidad' => "$item->cantidadNecesaria x pack",
                            'stock' => (string) $item->stockTotal,
                            'estado' => $suficiente ? 'Suficiente' : 'Insuficiente',
                        ];
                    }

                    $form->fill(['items_preview' => $preview]);
                })
                ->action(function (array $data, Action $action): void {
                    try {
                        $this->asignarPackAHabitacion->execute(
                            destinoId: (int) $data['habitacion_id'],
                            productoPackId: $this->record->id,
                            bodegaOrigenId: (int) $data['bodega_origen_id'],
                            cantidadPacks: (float) $data['cantidad_packs'],
                            creadoPorId: (int) auth()->id(),
                            colaboradorId: $data['colaborador_id'] ? (int) $data['colaborador_id'] : null,
                        );

                        Notification::make()
                            ->title('Pack surtido exitosamente')
                            ->success()
                            ->send();
                    } catch (Exception $e) {
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
        $stockItems = $this->obtenerStockItemsPack->ejecutar($this->record->id);
        $stockRows = [];

        foreach ($stockItems as $item) {
            $necesario = $item->cantidadNecesaria;
            $suficiente = $item->stockTotal >= $necesario;

            $stockRows[] = [
                'producto' => $item->nombreVariante,
                'variante' => $item->codigo,
                'necesario' => (string) $necesario,
                'disponible' => (string) $item->stockTotal,
                'estado' => $suficiente ? 'Suficiente' : 'Insuficiente',
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
                                ->prefixIcon(fn ($state) => $state === 'Suficiente' ? Heroicon::CheckCircle : Heroicon::XCircle)
                                ->prefixIconColor(fn ($state) => $state === 'Suficiente' ? Color::Green : Color::Red)
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
