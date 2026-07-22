<?php

declare(strict_types=1);

namespace App\Repository\Queries\Inventario\Lotes;

use App\Repository\Models\Catalogos\Ubicacion;

class ObtenerSubUbicacionesAgrupadas
{
    /** @var array<int, array{id: int, nombre: string, tipo: string, padre_id: int|null}> */
    private array $ubicaciones = [];

    public function __construct(
        private readonly Ubicacion $ubicacion,
    ) {}

    /** @return array<string, array<int, array{id: int, nombre: string, children: list<array{id: int, nombre: string, children: list<array{id: int, nombre: string}>}>}>> */
    public function execute(): array
    {
        $this->ubicaciones = $this->loadAll();

        $almacenes = $this->filterByTipo(['almacen', 'bodega']);

        $arbol = [];

        foreach ($almacenes as $almacen) {
            $arbol[$almacen['nombre']] = $this->buildTree($almacen['id']);
        }

        return $arbol;
    }

    /**
     * @return array<int, string>
     */
    public function formatOptions(?int $almacenId = null): array
    {
        if ($this->ubicaciones === []) {
            $this->ubicaciones = $this->loadAll();
        }

        $hijos = $almacenId !== null
            ? $this->getDescendantsFlat($almacenId)
            : $this->ubicaciones;

        $options = [];

        foreach ($hijos as $ubicacion) {
            $path = $this->buildPath($ubicacion['id']);
            $options[$ubicacion['id']] = $path;
        }

        return $options;
    }

    /** @return list<array{id: int, nombre: string, tipo: string, padre_id: int|null}> */
    private function loadAll(): array
    {
        $models = $this->ubicacion->query()
            ->whereIn('tipo', ['almacen', 'bodega', 'estante', 'nivel', 'posicion'])
            ->where('estado', 1)
            ->orderBy('orden')
            ->get(['id', 'nombre', 'tipo', 'padre_id']);

        $result = [];
        foreach ($models as $model) {
            $result[] = [
                'id' => (int) $model->id,
                'nombre' => (string) $model->nombre,
                'tipo' => (string) $model->tipo,
                'padre_id' => $model->padre_id !== null ? (int) $model->padre_id : null,
            ];
        }

        return $result;
    }

    /**
     * @param  array<int, string>  $tipos
     * @return array<int, array{id: int, nombre: string, tipo: string, padre_id: int|null}>
     */
    private function filterByTipo(array $tipos): array
    {
        return array_values(array_filter(
            $this->ubicaciones,
            fn ($u) => in_array($u['tipo'], $tipos, true),
        ));
    }

    /** @return list<array{id: int, nombre: string, children: list<array{id: int, nombre: string, children: list<array{id: int, nombre: string}>}>}> */
    private function buildTree(int $padreId): array
    {
        $children = array_values(array_filter(
            $this->ubicaciones,
            fn ($u) => $u['padre_id'] === $padreId,
        ));

        $tree = [];

        foreach ($children as $child) {
            $tree[] = [
                'id' => $child['id'],
                'nombre' => $child['nombre'],
                'children' => $this->buildTree($child['id']),
            ];
        }

        return $tree;
    }

    /**
     * @return array<int, array{id: int, nombre: string, tipo: string, padre_id: int|null}>
     */
    private function getDescendantsFlat(int $padreId): array
    {
        $result = [];
        $toProcess = [$padreId];

        while ($toProcess !== []) {
            $currentId = array_shift($toProcess);

            $children = array_values(array_filter(
                $this->ubicaciones,
                fn ($u) => $u['padre_id'] === $currentId,
            ));

            foreach ($children as $child) {
                $result[] = $child;
                $toProcess[] = $child['id'];
            }
        }

        return $result;
    }

    private function buildPath(int $ubicacionId): string
    {
        $parts = [];
        $currentId = $ubicacionId;

        while ($currentId !== null) {
            $ubicacion = $this->findById($currentId);

            if ($ubicacion === null) {
                break;
            }

            if (in_array($ubicacion['tipo'], ['almacen', 'bodega'], true)) {
                break;
            }

            array_unshift($parts, $ubicacion['nombre']);
            $currentId = $ubicacion['padre_id'];
        }

        return implode(' / ', $parts);
    }

    /**
     * @return array{id: int, nombre: string, tipo: string, padre_id: int|null}|null
     */
    private function findById(int $id): ?array
    {
        foreach ($this->ubicaciones as $ubicacion) {
            if ($ubicacion['id'] === $id) {
                return $ubicacion;
            }
        }

        return null;
    }
}
