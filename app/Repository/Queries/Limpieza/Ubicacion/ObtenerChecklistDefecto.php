<?php

declare(strict_types=1);

namespace App\Repository\Queries\Limpieza\Ubicacion;

class ObtenerChecklistDefecto
{
    /**
     * @return array<int, array{task: string, completed: bool}>
     */
    public function execute(?string $tipo = null): array
    {
        if ($tipo === 'espacio') {
            return [
                ['task' => 'Barrer y trapear los pisos', 'completed' => false],
                ['task' => 'Limpiar y desinfectar superficies', 'completed' => false],
                ['task' => 'Vaciar papeleras y colocar bolsas nuevas', 'completed' => false],
                ['task' => 'Limpiar ventanas y espejos', 'completed' => false],
                ['task' => 'Reponer insumos (papel, jabón)', 'completed' => false],
            ];
        }

        return [
            ['task' => 'Tender camas y cambiar sábanas', 'completed' => false],
            ['task' => 'Sacudir polvo de superficies y mobiliario', 'completed' => false],
            ['task' => 'Limpiar y desinfectar el cuarto de baño', 'completed' => false],
            ['task' => 'Barrer y trapear los pisos', 'completed' => false],
            ['task' => 'Reponer toallas limpias', 'completed' => false],
            ['task' => 'Reponer amenidades (jabón, shampoo, café)', 'completed' => false],
            ['task' => 'Vaciar papeleras y colocar bolsas nuevas', 'completed' => false],
        ];
    }
}
