<?php

declare(strict_types=1);

namespace App\Filament\Resources\Limpieza\LimpiezaHorarioResource\Pages;

use App\Actions\Limpieza\Horarios\NormalizarDestinosHorarioPlanificado;
use App\Actions\Limpieza\Horarios\ValidarCargaTurnoHorarioPlanificado;
use App\Filament\Resources\Limpieza\LimpiezaHorarioResource\LimpiezaHorarioResource;
use App\Repository\Models\Limpieza\LimpiezaHorario;
use Filament\Resources\Pages\CreateRecord;

class CreateLimpiezaHorario extends CreateRecord
{
    protected static string $resource = LimpiezaHorarioResource::class;

    /** @var list<array{limpiable_type: string, limpiable_id: int}> */
    private array $destinosNormalizados = [];

    public function getMaxContentWidth(): string
    {
        return '5xl';
    }

    /** @param array<string, mixed> $data */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = app(NormalizarDestinosHorarioPlanificado::class)->ejecutar($data);
        $this->destinosNormalizados = $this->extraerDestinos($data);
        app(ValidarCargaTurnoHorarioPlanificado::class)->ejecutar($data);
        unset($data['detalles']);

        return $data;
    }

    protected function afterCreate(): void
    {
        if (! $this->record instanceof LimpiezaHorario) {
            return;
        }

        foreach ($this->destinosNormalizados as $destino) {
            $this->record->detalles()->firstOrCreate($destino);
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<array{limpiable_type: string, limpiable_id: int}>
     */
    private function extraerDestinos(array $data): array
    {
        $detalles = is_array($data['detalles'] ?? null) ? $data['detalles'] : [];

        $destinos = collect($detalles)
            ->filter(fn (mixed $detalle): bool => is_array($detalle)
                && is_string($detalle['limpiable_type'] ?? null)
                && is_numeric($detalle['limpiable_id'] ?? null))
            ->map(fn (array $detalle): array => [
                'limpiable_type' => $detalle['limpiable_type'],
                'limpiable_id' => (int) $detalle['limpiable_id'],
            ])
            ->values()
            ->all();

        /** @var list<array{limpiable_type: string, limpiable_id: int}> $destinos */
        return $destinos;
    }
}
