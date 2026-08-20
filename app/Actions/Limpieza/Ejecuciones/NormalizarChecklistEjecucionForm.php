<?php

declare(strict_types=1);

namespace App\Actions\Limpieza\Ejecuciones;

use Illuminate\Support\Str;

final class NormalizarChecklistEjecucionForm
{
    /**
     * @return array<int, array{tarea: string, completada: bool}>
     */
    public function paraFormulario(mixed $checklist): array
    {
        if (! is_array($checklist)) {
            return [];
        }

        $items = [];

        foreach ($checklist as $tarea => $completada) {
            if (is_array($completada)) {
                $nombreTarea = trim($this->texto($completada['tarea'] ?? $completada['task'] ?? $tarea));
                $valor = $completada['completada'] ?? $completada['completed'] ?? false;
            } else {
                $nombreTarea = trim($this->texto($tarea));
                $valor = $completada;
            }

            if ($nombreTarea === '') {
                continue;
            }

            $items[] = [
                'tarea' => $nombreTarea,
                'completada' => $this->valorBooleano($valor),
            ];
        }

        return $items;
    }

    /**
     * @return array<string, bool>
     */
    public function paraPersistencia(mixed $items): array
    {
        if (! is_array($items)) {
            return [];
        }

        $checklist = [];

        foreach ($items as $item) {
            if (! is_array($item)) {
                continue;
            }

            $tarea = trim($this->texto($item['tarea'] ?? ''));

            if ($tarea === '') {
                continue;
            }

            $checklist[$tarea] = $this->valorBooleano($item['completada'] ?? false);
        }

        return $checklist;
    }

    private function valorBooleano(mixed $valor): bool
    {
        if (is_bool($valor)) {
            return $valor;
        }

        if (is_numeric($valor)) {
            return (int) $valor === 1;
        }

        if (! is_string($valor)) {
            return false;
        }

        return in_array(Str::lower(trim($valor)), ['1', 'true', 'si', 'sí', 'yes', 'on'], true);
    }

    private function texto(mixed $valor): string
    {
        if (is_scalar($valor) || $valor === null) {
            return (string) $valor;
        }

        return '';
    }
}
