<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\Enums\Restaurante\UbicacionCocina;
use App\Interactors\Inventario\Lotes\RegistrarMerma\RegistrarMermaGlobalDiaria;
use App\Repository\Models\Catalogos\Producto;
use App\Repository\Models\Catalogos\Ubicacion;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

final class MermaGlobalDiariaAction
{
    public static function make(): Action
    {
        return Action::make('mermaGlobalDiaria')
            ->label('Merma Global del Día')
            ->icon('heroicon-o-arrow-trending-down')
            ->color('danger')
            ->modalHeading('Registro de Merma Global del Día')
            ->modalDescription('Registre todas las pérdidas y desperdicios del día (no vinculadas a un plato específico).')
            ->modalWidth('3xl')
            ->schema([
                DatePicker::make('fecha')
                    ->label('Fecha de Merma')
                    ->default(now())
                    ->required(),

                Select::make('ubicacion_id')
                    ->label('Ubicación / Bodega')
                    ->options(fn () => Ubicacion::pluck('nombre', 'id')->toArray())
                    ->searchable()
                    ->default(fn () => Ubicacion::where('nombre', UbicacionCocina::RESTAURANTE->value)->value('id'))
                    ->required(),

                Repeater::make('items')
                    ->label('Productos con Merma')
                    ->schema([
                        Select::make('producto_id')
                            ->label('Producto / Insumo')
                            ->options(fn () => Producto::pluck('nombre', 'id')->toArray())
                            ->searchable()
                            ->required()
                            ->columnSpan(5),

                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->default(1)
                            ->minValue(0.01)
                            ->required()
                            ->columnSpan(3),

                        Textarea::make('motivo')
                            ->label('Motivo')
                            ->placeholder('Ej. Caducidad, calidad, derrame...')
                            ->rows(1)
                            ->columnSpan(4),
                    ])
                    ->columns(12)
                    ->defaultItems(1)
                    ->addActionLabel('Agregar Producto'),
            ])
            ->action(function (array $data): void {
                $fecha = is_string($data['fecha'] ?? null) ? $data['fecha'] : now()->toDateString();
                $ubicacionId = is_numeric($data['ubicacion_id'] ?? null) ? (int) $data['ubicacion_id'] : 0;
                $items = is_array($data['items'] ?? null) ? $data['items'] : [];
                $userId = auth()->id() !== null ? (int) auth()->id() : null;

                if ($ubicacionId <= 0) {
                    Notification::make()
                        ->title('Error')
                        ->body('Debe seleccionar una ubicación.')
                        ->danger()
                        ->send();

                    return;
                }

                $itemsTipados = [];
                foreach ($items as $i) {
                    $item = is_array($i) ? $i : [];
                    $itemsTipados[] = [
                        'producto_id' => is_numeric($item['producto_id'] ?? null) ? (int) $item['producto_id'] : 0,
                        'cantidad' => is_numeric($item['cantidad'] ?? null) ? (float) $item['cantidad'] : 0.0,
                        'motivo' => isset($item['motivo']) && is_string($item['motivo']) ? $item['motivo'] : null,
                    ];
                }

                $resultado = app(RegistrarMermaGlobalDiaria::class)->ejecutar(
                    fecha: $fecha,
                    ubicacionId: $ubicacionId,
                    items: $itemsTipados,
                    usuarioId: $userId,
                );

                $fmtPerdida = number_format($resultado['total_perdida'], 2);

                Notification::make()
                    ->title("Merma Global Registrada — {$fecha}")
                    ->body("{$resultado['total_items']} producto(s) procesado(s). Pérdida total estimada: C$ {$fmtPerdida}")
                    ->success()
                    ->send();
            });
    }
}
