<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\Pages;

use App\Actions\Limpieza\Ejecuciones\NormalizarChecklistEjecucionForm;
use App\BusinessLogic\Limpieza\ValidarCambioColaboradorEjecucion;
use App\Filament\Resources\Limpieza\LimpiezaEjecucionResource\LimpiezaEjecucionResource;
use App\Interactors\Limpieza\Procesos\ProcesarOperacionLimpieza;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Queries\Limpieza\Carrito\BloquearCarritoParaLimpieza;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;

class EditLimpiezaEjecucion extends EditRecord
{
    protected ProcesarOperacionLimpieza $procesarOperacionLimpieza;

    protected ValidarCambioColaboradorEjecucion $validadorCambioColaborador;

    protected NormalizarChecklistEjecucionForm $normalizarChecklist;

    protected BloquearCarritoParaLimpieza $bloquearCarrito;

    public function boot(
        ProcesarOperacionLimpieza $procesarOperacionLimpieza,
        ValidarCambioColaboradorEjecucion $validadorCambioColaborador,
        NormalizarChecklistEjecucionForm $normalizarChecklist,
        BloquearCarritoParaLimpieza $bloquearCarrito,
    ): void {
        $this->procesarOperacionLimpieza = $procesarOperacionLimpieza;
        $this->validadorCambioColaborador = $validadorCambioColaborador;
        $this->normalizarChecklist = $normalizarChecklist;
        $this->bloquearCarrito = $bloquearCarrito;
    }

    protected static string $resource = LimpiezaEjecucionResource::class;

    public function getMaxContentWidth(): string
    {
        return '5xl';
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['detalles_checklist_items'] = $this->normalizarChecklist->paraFormulario(
            $data['detalles_checklist'] ?? []
        );

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['detalles_checklist'] = $this->normalizarChecklist->paraPersistencia(
            $data['detalles_checklist_items'] ?? []
        );

        unset($data['detalles_checklist_items']);
        unset($data['hora_fin']);

        return $data;
    }

    protected function beforeSave(): void
    {
        assert($this->record instanceof LimpiezaEjecucion);
        $this->validadorCambioColaborador->validar($this->record);

        if ($this->record->isDirty('carrito_id') && $this->record->carrito_id !== null) {
            $this->bloquearCarrito->execute(
                (int) $this->record->carrito_id,
                (int) $this->record->id,
                $this->record->colaborador_id !== null ? (int) $this->record->colaborador_id : null,
            );
        }
    }

    protected function afterSave(): void
    {
        try {
            $data = $this->data ?? [];
            $checklist = $this->normalizarChecklist->paraPersistencia(
                $data['detalles_checklist_items'] ?? []
            );
            $data['checklist'] = $checklist;
            $data['detalles_checklist'] = $checklist;

            $orquestador = $this->procesarOperacionLimpieza;
            $orquestador->ejecutar(
                ejecucionId: $this->record->id ?? 0,
                data: $data,
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
