<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Pages;

use App\BusinessLogic\Limpieza\ValidarCambioColaboradorEjecucion;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource;
use App\Interactors\Limpieza\Procesos\ProcesarOperacionLimpiezaOrquestador;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditLimpiezaEjecucion extends EditRecord
{
    protected ProcesarOperacionLimpiezaOrquestador $orquestadorLimpieza;

    protected ValidarCambioColaboradorEjecucion $validadorCambioColaborador;

    public function boot(
        ProcesarOperacionLimpiezaOrquestador $orquestadorLimpieza,
        ValidarCambioColaboradorEjecucion $validadorCambioColaborador,
    ): void {
        $this->orquestadorLimpieza = $orquestadorLimpieza;
        $this->validadorCambioColaborador = $validadorCambioColaborador;
    }

    protected static string $resource = LimpiezaEjecucionResource::class;

    public function getMaxContentWidth(): string
    {
        return '5xl';
    }

    protected function beforeSave(): void
    {
        assert($this->record instanceof LimpiezaEjecucion);
        $this->validadorCambioColaborador->validar($this->record);
    }

    protected function afterSave(): void
    {
        try {
            $orquestador = $this->orquestadorLimpieza;
            $orquestador->ejecutar(
                ejecucionId: $this->record->id ?? 0,
                data: $this->data ?? [],
                usuarioId: (int) auth()->id()
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error al procesar la limpieza')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();

            throw new Halt;
        }
    }
}
