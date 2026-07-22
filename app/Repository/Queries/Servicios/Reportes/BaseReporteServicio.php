<?php

declare(strict_types=1);

namespace App\Repository\Queries\Servicios\Reportes;

use App\Interactors\Reportes\RegistrarAuditoriaReporte;
use App\Support\HotelInfo;
use Illuminate\Support\Facades\Auth;

class BaseReporteServicio
{
    public function __construct(
        protected readonly RegistrarAuditoriaReporte $registrarAuditoria,
    ) {}

    /** @return array<string, mixed> */
    protected function getBaseData(): array
    {
        return array_merge(HotelInfo::getBaseData(), [
            'fecha' => now()->format('d/m/Y H:i'),
        ]);
    }

    protected function registrarAuditoria(string $codigo): void
    {
        $this->registrarAuditoria->ejecutar($codigo, [
            'usuario' => Auth::id(),
            'ip' => request()->ip(),
        ]);
    }

    /**
     * @param  iterable<string, iterable<mixed>>  $agrupado
     * @return array<int, array<int, array{tipo: string, categoria?: string, item?: mixed}>>
     */
    protected function paginarPorCategoria(iterable $agrupado, int $filasPorPagina): array
    {
        $filas = [];
        foreach ($agrupado as $categoria => $items) {
            $filas[] = ['tipo' => 'categoria', 'categoria' => $categoria];
            foreach ($items as $item) {
                $filas[] = ['tipo' => 'item', 'item' => $item];
            }
        }

        if (empty($filas)) {
            return [];
        }

        $filasCollection = collect($filas);

        $chunks = $filasCollection->chunk($filasPorPagina)
            ->map(fn ($c) => $c->values()->all())
            ->values()
            ->all();

        $totalChunks = count($chunks);
        for ($i = 0; $i < $totalChunks - 1; $i++) {
            $lastIndex = count($chunks[$i]) - 1;
            if ($lastIndex >= 0 && $chunks[$i][$lastIndex]['tipo'] === 'categoria') {
                $header = $chunks[$i][$lastIndex];
                array_splice($chunks[$i], $lastIndex, 1);
                array_unshift($chunks[$i + 1], $header);
            }
        }

        return array_values(array_filter($chunks, fn ($c) => count($c) > 0));
    }
}
