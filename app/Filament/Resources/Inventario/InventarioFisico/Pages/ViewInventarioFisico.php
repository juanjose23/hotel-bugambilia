<?php

declare(strict_types=1);

namespace App\Filament\Resources\Inventario\InventarioFisico\Pages;

use App\Enums\Inventario\EstadoInventarioFisico;
use App\Filament\Resources\Inventario\InventarioFisico\InventarioFisicoResource;
use App\Models\Inventario\InventarioFisico;
use App\UseCases\Inventario\InventarioFisico\Mutations\ProcesarInventarioFisico;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;

/**
 * @property InventarioFisico $record
 */
class ViewInventarioFisico extends ViewRecord
{
    protected static string $resource = InventarioFisicoResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn () => $this->record->estado === EstadoInventarioFisico::Borrador),

            Action::make('procesar')
                ->label('Procesar Conciliación')
                ->icon(Heroicon::CheckCircle)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Procesar Conciliación de Inventario')
                ->modalDescription('Esta acción comparará la cantidad física registrada en la hoja de cálculo con el stock actual del sistema, generará los movimientos de ajuste (MOV_AJUSTE) en los lotes con discrepancia, y cerrará esta sesión como PROCESADO. Esta acción no se puede deshacer.')
                ->action(function () {
                    try {
                        app(ProcesarInventarioFisico::class)->execute($this->record, auth()->id());

                        Notification::make()
                            ->title('Conciliación Procesada')
                            ->body("La toma de inventario {$this->record->codigo} ha sido procesada de manera exitosa y los ajustes han sido aplicados.")
                            ->success()
                            ->send();

                        $this->refreshFormData(['estado']);
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error al procesar conciliación')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->visible(fn () => $this->record->estado === EstadoInventarioFisico::Borrador),
        ];
    }
}
