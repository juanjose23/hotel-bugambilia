<?php

declare(strict_types=1);

namespace App\Filament\Resources\Restaurante\ProcesoCocinaResource\Tables;

use App\Filament\Shared\Actions\Restaurante\ReporteCostosCocinaAction;
use App\Filament\Shared\Columns\FechaStandardColumn;
use App\Filament\Shared\Columns\MontoMonedaColumn;
use App\Repository\Models\Restaurante\ProcesoCocina;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

final class ProcesoCocinaTable
{
    public static function configure(Table $table): Table
    {
        return $table->modifyQueryUsing(fn (Builder $query) => $query->with(['loteResultado.variante.producto.unidadMedida', 'loteResultado.variante.unidadMedida']))
            ->columns([
                TextColumn::make('plato.nombre')->label('Platillo')->searchable()->placeholder('—'),
                TextColumn::make('estado_operacion')->label('Estado')->state(fn (ProcesoCocina $record): string => $record->getAttribute('confirmado_at') !== null ? 'Preparación terminada' : match ($record->getAttribute('tipo_registro')) {
                    'borrador' => 'Plan sin confirmar', 'pedido' => 'Consumo de comanda', default => 'Registro anterior'
                })->badge(),
                TextColumn::make('cantidad_platos')->label('Porciones previstas'),
                TextColumn::make('loteResultado.variante.producto.nombre')->label('Producto terminado')->placeholder('Pendiente de confirmar'),
                TextColumn::make('cantidad_obtenida')->label('Cantidad obtenida')->formatStateUsing(function (ProcesoCocina $record): string {
                    $cantidad = $record->getAttribute('cantidad_obtenida');
                    if (! is_numeric($cantidad)) {
                        return 'Sin confirmar';
                    }
                    $variante = $record->loteResultado?->variante;

                    return number_format((float) $cantidad, 3, ',', '.').' '.($variante->unidadMedida->nombre ?? $variante->producto->unidadMedida->nombre ?? 'unidades');
                })->placeholder('Sin confirmar'),
                MontoMonedaColumn::make('costo_total')->label('Costo de ingredientes')->tooltip('Es una estimación mientras sea un plan. Al confirmar se calcula con los lotes consumidos.'),
                MontoMonedaColumn::make('costo_por_plato')->label('Costo / porción prevista')->state(fn (ProcesoCocina $record): float => $record->costo_por_plato)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('items_count')->counts('items')->label('Ingredientes del plan')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('codigo')->label('Código')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('confirmado_at')->label('Confirmado el')->dateTime()->placeholder('Sin confirmar')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('realizadoPor.name')->label('Responsable')->placeholder('—')->toggleable(isToggledHiddenByDefault: true),
                FechaStandardColumn::make('created_at', 'Fecha'),
            ])
            ->headerActions([ReporteCostosCocinaAction::make()])
            ->recordActions([EditAction::make()->label(fn (ProcesoCocina $record): string => $record->getAttribute('confirmado_at') !== null ? 'Consultar' : 'Continuar')]);
    }
}
