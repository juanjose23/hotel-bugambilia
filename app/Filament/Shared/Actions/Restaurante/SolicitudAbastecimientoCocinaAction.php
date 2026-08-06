<?php

declare(strict_types=1);

namespace App\Filament\Shared\Actions\Restaurante;

use App\Interactors\Restaurante\Cocina\CrearSolicitudAbastecimientoCocina;
use App\Repository\Models\Catalogos\ProductoVariante;
use App\Repository\Queries\Compras\Shared\ObtenerColaboradorDeSesion;
use App\Repository\Queries\Restaurante\Cocina\ObtenerAbastecimientoInteligenteCocina;
use DomainException;
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
            ->modalDescription('Revise las sugerencias calculadas por stock de cocina y pedidos bloqueados antes de enviar a Bodega / Compras.')
            ->modalWidth('2xl')
            ->fillForm(fn (): array => app(ObtenerAbastecimientoInteligenteCocina::class)->ejecutar())
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
                        Select::make('producto_variante_id')
                            ->label('Producto / Variante')
                            ->options(fn (): array => self::opcionesVariantes())
                            ->searchable()
                            ->preload()
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

                /** @var array<int, array{producto_variante_id?: int|null, cantidad: float, justificacion?: string|null}> $items */
                $items = is_array($data['items'] ?? null) ? $data['items'] : [];
                $colaborador = app(ObtenerColaboradorDeSesion::class)->ejecutar();

                if ($colaborador === null) {
                    throw new DomainException('No hay un colaborador asociado al usuario actual.');
                }

                $solicitud = app(CrearSolicitudAbastecimientoCocina::class)->ejecutar(
                    motivo: $motivo,
                    items: $items,
                    fechaNecesita: $fechaNecesita,
                    colaboradorId: (int) $colaborador->id,
                );

                Notification::make()
                    ->title('Solicitud de Abastecimiento Creada')
                    ->body("Solicitud #{$solicitud->codigo} enviada con éxito.")
                    ->success()
                    ->send();
            });
    }

    /** @return array<int, string> */
    private static function opcionesVariantes(): array
    {
        return ProductoVariante::query()
            ->with(['producto', 'unidadMedida'])
            ->where('estado', 1)
            ->orderBy('codigo')
            ->get()
            ->mapWithKeys(function (ProductoVariante $variante): array {
                $producto = $variante->producto->nombre ?? 'Producto sin nombre';
                $nombre = $variante->nombre_variante ?: $variante->codigo;
                $unidad = $variante->unidadMedida?->nombre;
                $suffix = $unidad !== null ? " ({$unidad})" : '';

                return [(int) $variante->id => "{$producto} - {$nombre}{$suffix}"];
            })
            ->all();
    }
}
