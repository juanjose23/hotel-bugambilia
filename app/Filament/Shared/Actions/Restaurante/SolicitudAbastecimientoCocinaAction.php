<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\Interactors\Restaurante\Cocina\CrearSolicitudAbastecimientoCocina;
use App\Repository\Models\Catalogos\Producto;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

final class SolicitudAbastecimientoCocinaAction
{
    public static function make(): Action
    {
        return Action::make('solicitarAbastecimientoCocina')
            ->label('Solicitud de Abastecimiento')
            ->icon('heroicon-o-archive-box-arrow-down')
            ->color('warning')
            ->modalHeading('Solicitud de Abastecimiento para Cocina')
            ->modalDescription('Cree una solicitud formal de insumos e ingredientes para enviar a Bodega / Compras.')
            ->modalWidth('2xl')
            ->schema([
                Textarea::make('motivo')
                    ->label('Motivo / Justificación de la Solicitud')
                    ->placeholder('Ej. Reabastecimiento de insumos para servicio de cenas...')
                    ->required()
                    ->rows(2),

                DatePicker::make('fecha_necesita')
                    ->label('Fecha Requerida')
                    ->default(now()->addDays(1))
                    ->required(),

                Repeater::make('items')
                    ->label('Productos / Insumos Requeridos')
                    ->schema([
                        Select::make('producto_id')
                            ->label('Producto')
                            ->options(fn () => Producto::pluck('nombre', 'id')->toArray())
                            ->searchable()
                            ->required()
                            ->columnSpan(6),

                        TextInput::make('cantidad')
                            ->label('Cantidad')
                            ->numeric()
                            ->default(1)
                            ->minValue(0.01)
                            ->required()
                            ->columnSpan(3),

                        TextInput::make('justificacion')
                            ->label('Observaciones')
                            ->placeholder('Ej. Marca específica...')
                            ->columnSpan(3),
                    ])
                    ->columns(12)
                    ->defaultItems(1)
                    ->addActionLabel('Agregar Insumo Requerido'),
            ])
            ->action(function (array $data): void {
                $motivo = (string) ($data['motivo'] ?? 'Solicitud desde módulo de cocina');
                $fechaNecesita = is_string($data['fecha_necesita'] ?? null) ? $data['fecha_necesita'] : null;

                /** @var array<int, array{producto_id: int, cantidad: float, justificacion?: string|null}> $items */
                $items = is_array($data['items'] ?? null) ? $data['items'] : [];
                $userId = auth()->id() !== null ? (int) auth()->id() : null;

                $solicitud = app(CrearSolicitudAbastecimientoCocina::class)->ejecutar(
                    motivo: $motivo,
                    items: $items,
                    fechaNecesita: $fechaNecesita,
                    usuarioId: $userId,
                );

                Notification::make()
                    ->title('Solicitud de Abastecimiento Creada')
                    ->body("Solicitud #{$solicitud->codigo} enviada con éxito.")
                    ->success()
                    ->send();
            });
    }
}
