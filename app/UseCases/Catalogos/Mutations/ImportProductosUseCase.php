<?php

namespace App\UseCases\Catalogos\Mutations;

use App\Models\Catalogos\Producto;
use Illuminate\Support\Str;

class ImportProductosUseCase
{
    /**
     * @param  string  $path  Ruta absoluta al CSV
     * @return array{processed: int, errors: array<int, string>}
     */
    public function importarDesdeCsv(string $path): array
    {
        $processed = 0;
        $errors = [];

        if (! file_exists($path)) {
            return ['processed' => 0, 'errors' => ['file_not_found']];
        }

        $handle = fopen($path, 'r');
        if ($handle === false) {
            return ['processed' => 0, 'errors' => ['cannot_open_file']];
        }

        $header = null;

        while (($row = fgetcsv($handle, 0)) !== false) {
            if (! $header) {
                $header = array_map(fn ($h) => trim($h), $row);

                continue;
            }

            if (count($header) !== count($row)) {
                continue;
            }
            /** @var array<string, string|null> $dataRow */
            $dataRow = array_combine($header, $row);

            try {
                $producto = Producto::firstOrCreate([
                    'nombre' => $dataRow['nombre'] ?? 'Sin nombre',
                ], [
                    'descripcion' => $dataRow['descripcion'] ?? null,
                    'categoria_id' => $dataRow['categoria_id'] ?? null,
                    'marca_id' => $dataRow['marca_id'] ?? null,
                    'tipo' => $dataRow['tipo'] ?? 2,
                    'estado' => isset($dataRow['estado']) ? (int) $dataRow['estado'] : 1,
                ]);

                if (! empty($dataRow['variante_codigo']) || ! empty($dataRow['variante_nombre'])) {
                    $attrs = $dataRow['atributos'] ?? null;
                    if (! empty($attrs)) {
                        $decoded = json_decode($attrs, true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $attrs = $decoded;
                        }
                    }

                    $producto->variantes()->create([
                        'codigo' => $dataRow['variante_codigo'] ?? Str::upper(Str::random(8)),
                        'nombre_variante' => $dataRow['variante_nombre'] ?? 'Principal',
                        'atributos' => $attrs,
                        'peso' => $dataRow['peso'] ?? null,
                        'volumen' => $dataRow['volumen'] ?? null,
                        'estado' => isset($dataRow['variante_estado']) ? (int) $dataRow['variante_estado'] : 1,
                    ]);
                }

                $processed++;
            } catch (\Throwable $e) {
                $errors[] = $e->getMessage();

                continue;
            }
        }

        fclose($handle);

        return ['processed' => $processed, 'errors' => $errors];
    }
}
