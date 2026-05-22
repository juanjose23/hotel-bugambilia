<?php

declare(strict_types=1);

namespace App\UseCases\Servicios\Reportes;

use App\UseCases\Reportes\Mutations\RegistrarAuditoriaReporteUseCase;
use Illuminate\Support\Facades\Auth;

class BaseReporteServicio
{
    /** @return array<string, mixed> */
    protected function getBaseData(): array
    {
        $logoPath = public_path('img/logo-horizontal.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $logoBase64 = 'data:image/'.$type.';base64,'.base64_encode((string) file_get_contents($logoPath));
        }

        return [
            'logo_base64' => $logoBase64,
            'hotelInfo' => [
                'telefono' => '+505 8713 6805',
                'email' => 'recepcion@bugambiliashotel.com',
                'direccion' => 'Salida Sur Estelí, Restaurante Absoluto 1c. Oeste, 2c. Sur, 1c. Oeste',
            ],
            'generadoEn' => now()->format('d/m/Y H:i'),
            'fecha' => now()->format('d/m/Y H:i'),
            'usuario' => Auth::user()->name ?? 'Sistema',
        ];
    }

    protected function registrarAuditoria(string $codigo): void
    {
        app(RegistrarAuditoriaReporteUseCase::class)->ejecutar($codigo, [
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
