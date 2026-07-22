<?php

declare(strict_types=1);

namespace App\Support\Excel;

use Illuminate\Support\Collection;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Options as XlsxOptions;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class GeneradorExcel
{
    private const COLOR_HEADER_BG = '711C37';

    private const COLOR_HEADER_FONT = 'FFFFFF';

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $coleccion
     * @param  array<int, ColumnaExcel>  $columnas
     */
    public function descargar(
        Collection $coleccion,
        array $columnas,
        string $nombre = 'reporte.xlsx',
        ?string $hoja = null,
    ): StreamedResponse {
        return response()->streamDownload(
            function () use ($coleccion, $columnas, $hoja): void {
                $this->escribir($coleccion, $columnas, $hoja);
            },
            $nombre,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ],
        );
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $coleccion
     * @param  array<int, ColumnaExcel>  $columnas
     */
    public function almacenar(
        Collection $coleccion,
        array $columnas,
        string $ruta,
        string $disk = 'local',
        ?string $hoja = null,
    ): string {
        $opciones = new XlsxOptions;
        $opciones->SHOULD_USE_INLINE_STRINGS = true;

        $writer = new XlsxWriter($opciones);

        $rutaCompleta = storage_path("app/{$ruta}");
        $directorio = dirname($rutaCompleta);

        if (! is_dir($directorio)) {
            mkdir($directorio, recursive: true, permissions: 0755);
        }

        $writer->openToFile($rutaCompleta);

        $this->escribirFilas($writer, $coleccion, $columnas, $hoja);

        $writer->close();

        return $rutaCompleta;
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $coleccion
     * @param  array<int, ColumnaExcel>  $columnas
     */
    private function escribir(
        Collection $coleccion,
        array $columnas,
        ?string $hoja,
    ): void {
        $opciones = new XlsxOptions;
        $opciones->SHOULD_USE_INLINE_STRINGS = true;

        $writer = new XlsxWriter($opciones);

        $writer->openToBrowser('reporte.xlsx');

        $this->escribirFilas($writer, $coleccion, $columnas, $hoja);

        $writer->close();
    }

    /**
     * @template TKey of array-key
     * @template TValue
     *
     * @param  Collection<TKey, TValue>  $coleccion
     * @param  array<int, ColumnaExcel>  $columnas
     */
    private function escribirFilas(
        XlsxWriter $writer,
        Collection $coleccion,
        array $columnas,
        ?string $hoja,
    ): void {
        if ($hoja !== null) {
            $writer->getCurrentSheet()->setName($hoja);
        }

        $estiloHeader = $this->crearEstiloHeader();

        $writer->addRow(Row::fromValues(
            array_values(array_map(fn (ColumnaExcel $col) => $col->encabezado, $columnas)),
            $estiloHeader,
        ));

        $estiloNumero = $this->crearEstiloNumero();

        foreach ($coleccion as $fila) {
            $valores = [];
            $estilos = [];

            foreach ($columnas as $col) {
                $valor = ($col->accesor)($fila);
                $valores[] = $this->normalizarValor($valor);
                $estilos[] = $col->numerica ? $estiloNumero : null;
            }

            $writer->addRow(Row::fromValuesWithStyles(
                $valores,
                null,
                array_values(array_filter($estilos)),
            ));
        }
    }

    private function crearEstiloHeader(): Style
    {
        $estilo = new Style;
        $estilo->setFontBold();
        $estilo->setFontColor(self::COLOR_HEADER_FONT);
        $estilo->setFontSize(11);
        $estilo->setBackgroundColor(self::COLOR_HEADER_BG);

        return $estilo;
    }

    private function crearEstiloNumero(): Style
    {
        $estilo = new Style;
        $estilo->setFormat('#,##0.00');

        return $estilo;
    }

    private function normalizarValor(mixed $valor): null|bool|\DateInterval|\DateTimeInterface|float|int|string
    {
        if ($valor === null || $valor === '') {
            return null;
        }

        if (is_bool($valor)) {
            return $valor;
        }

        if (is_int($valor) || is_float($valor)) {
            return $valor;
        }

        if ($valor instanceof \DateTimeInterface) {
            return $valor;
        }

        if ($valor instanceof \DateInterval) {
            return $valor;
        }

        if (is_string($valor)) {
            return $valor;
        }

        return null;
    }
}
