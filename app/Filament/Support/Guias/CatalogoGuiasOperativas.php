<?php

declare(strict_types=1);

namespace App\Filament\Support\Guias;

final readonly class CatalogoGuiasOperativas
{
    /** @param array<string> $scopes
     * @return array<array-key, mixed>|null
     */
    public function paraPagina(array $scopes): ?array
    {
        $paginas = config('guias_operativas.paginas');
        if (! is_array($paginas)) {
            return null;
        }
        foreach ($scopes as $scope) {
            $guia = $paginas[$scope] ?? null;
            if (is_array($guia)) {
                return $guia;
            }
        }

        return null;
    }
}
