<?php

declare(strict_types=1);

namespace App\UseCases\Espacios\Mutations;

use App\Enums\HabitacionesEspacios\TipoEspacio;
use App\Models\Espacios\Espacio;
use Illuminate\Support\Facades\DB;

class GenerarCodigosMasivos
{
    /**
     * Genera códigos secuenciales en lote para espacios según su tipo.
     *
     * @param  TipoEspacio  $tipo  Tipo de espacio (MESA, SALON, etc.)
     * @param  int  $cantidad  Número de espacios a generar
     * @param  int|null  $padre_id  Espacio padre opcional (ej. restaurante padre de mesas)
     * @param  string|null  $nombreBase  Base del nombre (ej. "Mesa" → "Mesa 1", "Mesa 2")
     * @return list<array{id: int, codigo: string, nombre: string}> IDs y códigos generados
     *
     * @throws \InvalidArgumentException
     */
    public function execute(
        TipoEspacio $tipo,
        int $cantidad,
        ?int $padre_id = null,
        ?string $nombreBase = null,
    ): array {
        if ($cantidad < 1 || $cantidad > 100) {
            throw new \InvalidArgumentException('La cantidad debe estar entre 1 y 100.');
        }

        $prefijo = $this->resolvePrefijo($tipo);
        $nombreBase ??= $this->resolveNombreBase($tipo);
        $ultimoNumero = $this->getUltimoNumero($prefijo);

        $resultados = [];

        DB::transaction(function () use ($tipo, $cantidad, $padre_id, $prefijo, $nombreBase, $ultimoNumero, &$resultados) {
            $inicio = $ultimoNumero + 1;

            for ($i = 0; $i < $cantidad; $i++) {
                $numero = $inicio + $i;
                $codigo = $prefijo.'-'.str_pad((string) $numero, 4, '0', STR_PAD_LEFT);

                $nombre = $nombreBase.' '.($tipo === TipoEspacio::RESTAURANTE ? '' : $numero);

                $espacio = Espacio::create([
                    'codigo' => $codigo,
                    'nombre' => $nombreBase.' '.$numero,
                    'tipo' => $tipo->value,
                    'padre_id' => $padre_id,
                    'capacidad_personas' => match ($tipo) {
                        TipoEspacio::MESA => 4,
                        TipoEspacio::RESTAURANTE => 80,
                        TipoEspacio::SALON => 100,
                        TipoEspacio::GYM => 20,
                        TipoEspacio::SPA => 4,
                        TipoEspacio::PISCINA => 50,
                        TipoEspacio::CANCHA => 20,
                        default => 1,
                    },
                    'capacidad_mesas' => $tipo === TipoEspacio::RESTAURANTE ? $cantidad : null,
                    'estado' => 1,
                    'orden' => $numero,
                ]);

                $resultados[] = [
                    'id' => $espacio->id,
                    'codigo' => $codigo,
                    'nombre' => $espacio->nombre,
                ];
            }
        });

        return $resultados;
    }

    private function resolvePrefijo(TipoEspacio $tipo): string
    {
        return match ($tipo) {
            TipoEspacio::RESTAURANTE => 'REST',
            TipoEspacio::MESA => 'MESA',
            TipoEspacio::GYM => 'GYM',
            TipoEspacio::SALON => 'SALON',
            TipoEspacio::SPA => 'SPA',
            TipoEspacio::PISCINA => 'PISC',
            TipoEspacio::CANCHA => 'CANCH',
            TipoEspacio::OTRO => 'ESP',
        };
    }

    private function resolveNombreBase(TipoEspacio $tipo): string
    {
        return match ($tipo) {
            TipoEspacio::RESTAURANTE => 'Restaurante',
            TipoEspacio::MESA => 'Mesa',
            TipoEspacio::GYM => 'Gimnasio',
            TipoEspacio::SALON => 'Salón',
            TipoEspacio::SPA => 'Spa',
            TipoEspacio::PISCINA => 'Piscina',
            TipoEspacio::CANCHA => 'Cancha',
            TipoEspacio::OTRO => 'Espacio',
        };
    }

    private function getUltimoNumero(string $prefijo): int
    {
        $offset = strlen($prefijo) + 2;

        $ultimo = Espacio::withTrashed()
            ->where('codigo', 'like', $prefijo.'-%')
            ->orderByRaw('CAST(SUBSTRING(codigo, LENGTH(?) + 2) AS INTEGER) DESC', [$prefijo])
            ->lockForUpdate()
            ->first();

        if ($ultimo && preg_match('/^'.$prefijo.'-(\d+)$/', $ultimo->codigo, $matches)) {
            return (int) $matches[1];
        }

        $maxRow = Espacio::withTrashed()
            ->where('codigo', 'like', $prefijo.'-%')
            ->selectRaw('MAX(CAST(SUBSTRING(codigo, LENGTH(?) + 2) AS INTEGER)) as max_val', [$prefijo])
            ->first();
        $max = $maxRow ? $maxRow->getAttribute('max_val') : null;

        return is_numeric($max) ? (int) $max : 0;
    }
}
