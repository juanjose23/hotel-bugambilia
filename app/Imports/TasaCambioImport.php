<?php

namespace App\Imports;

use App\Models\Monedas\Moneda;
use App\Models\Monedas\TasaCambio;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TasaCambioImport implements ToArray, WithHeadingRow
{
    protected ?int $defaultOrigenId;

    protected ?int $defaultDestinoId;

    protected int $importedCount = 0;

    public function __construct(?int $defaultOrigenId = null, ?int $defaultDestinoId = null)
    {
        $this->defaultOrigenId = $defaultOrigenId;
        $this->defaultDestinoId = $defaultDestinoId;
    }

    /**
     * @param  array<int, array<string, mixed>>  $array
     */
    public function array(array $array): void
    {
        foreach ($array as $row) {
            // 1. Intentar obtener valores usando cabeceras comunes
            $fechaRaw = $row['fecha'] ?? $row['date'] ?? $row['fecha_tasa'] ?? null;
            $origenCodigo = $row['moneda_origen'] ?? $row['origen'] ?? $row['from'] ?? null;
            $destinoCodigo = $row['moneda_destino'] ?? $row['destino'] ?? $row['to'] ?? null;
            $tasa = $row['tasa'] ??
                    $row['rate'] ??
                    $row['valor'] ??
                    $row['tipo_cambio'] ??
                    $row['tipo_de_cambio'] ??
                    $row['tasa_cambio'] ??
                    $row['tasa_de_cambio'] ??
                    $row['tipo de cambio'] ??
                    $row['tasa de cambio'] ??
                    null;

            // 2. Fallback a índices numéricos si las llaves no coincidieron o están vacías
            if (empty($fechaRaw) || empty($tasa)) {
                $values = array_values($row);
                $fechaRaw = $values[0] ?? null;
                // Si la columna 2 y 3 no tienen formato de moneda de 3 caracteres, no las asumimos como códigos
                $col1 = $values[1] ?? null;
                $col2 = $values[2] ?? null;

                $origenCodigo = (is_string($col1) && strlen(trim($col1)) === 3) ? $col1 : null;
                $destinoCodigo = (is_string($col2) && strlen(trim($col2)) === 3) ? $col2 : null;

                // Si las columnas intermedias de moneda no existen o no son códigos válidos, la tasa es la última columna
                $tasa = $values[3] ?? $values[1] ?? null;
            }

            if (empty($fechaRaw) || empty($tasa)) {
                continue;
            }

            try {
                if (is_numeric($fechaRaw)) {
                    $fecha = Carbon::instance(Date::excelToDateTimeObject($fechaRaw))->toDateString();
                } else {
                    $fecha = Carbon::parse($fechaRaw)->toDateString();
                }
            } catch (\Exception $e) {
                continue;
            }

            $tasa = (float) $tasa;
            $origen = null;
            $destino = null;

            if ($origenCodigo) {
                $origen = Moneda::where('codigo', strtoupper(trim($origenCodigo)))->first();
            }
            if ($destinoCodigo) {
                $destino = Moneda::where('codigo', strtoupper(trim($destinoCodigo)))->first();
            }

            // Fallback a las seleccionadas en el formulario
            if (! $origen && $this->defaultOrigenId) {
                $origen = Moneda::find($this->defaultOrigenId);
            }
            if (! $destino && $this->defaultDestinoId) {
                $destino = Moneda::find($this->defaultDestinoId);
            }

            if ($fecha && $origen && $destino && $tasa > 0) {
                TasaCambio::updateOrCreate(
                    [
                        'fecha' => $fecha,
                        'moneda_origen_id' => $origen->id,
                        'moneda_destino_id' => $destino->id,
                    ],
                    [
                        'tasa' => $tasa,
                    ]
                );
                $this->importedCount++;
            }
        }

        if ($this->importedCount === 0) {
            throw new \Exception(
                'No se importó ninguna tasa. Asegúrate de que las columnas contengan datos válidos de Fecha y Tasa, y de que las monedas origen/destino (ej: USD, NIO) ya estén creadas en el catálogo de Monedas.'
            );
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }
}
