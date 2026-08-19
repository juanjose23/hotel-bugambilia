<?php

declare(strict_types=1);

use App\Support\Pdf\LayoutPdf;
use App\Support\Pdf\Orientacion;
use App\Support\Pdf\TamanoPapel;
use App\Support\ReportePaginador;

test('calcula menos filas cuando la pagina carta esta horizontal', function (): void {
    $vertical = new ReportePaginador(new LayoutPdf(
        tamano: TamanoPapel::LETTER,
        orientacion: Orientacion::Vertical,
    ));

    $horizontal = new ReportePaginador(new LayoutPdf(
        tamano: TamanoPapel::LETTER,
        orientacion: Orientacion::Horizontal,
    ));

    expect($vertical->filasPorPagina(altoFilaMm: 6))
        ->toBeGreaterThan($horizontal->filasPorPagina(altoFilaMm: 6));
});

test('calcula mas ancho util cuando la pagina carta esta horizontal', function (): void {
    $vertical = new LayoutPdf(
        tamano: TamanoPapel::LETTER,
        orientacion: Orientacion::Vertical,
    );

    $horizontal = new LayoutPdf(
        tamano: TamanoPapel::LETTER,
        orientacion: Orientacion::Horizontal,
    );

    expect($horizontal->anchoContenidoMm())
        ->toBeGreaterThan($vertical->anchoContenidoMm());
});

test('mueve filas a otra pagina cuando la primera pagina no tiene espacio suficiente', function (): void {
    $paginador = new ReportePaginador(new LayoutPdf(
        tamano: TamanoPapel::LETTER,
        orientacion: Orientacion::Vertical,
    ));

    $items = collect(range(1, 12));

    $paginas = $paginador->chunkParaPdf(
        items: $items,
        altoFilaMm: 20,
        altoExtraPrimeraPaginaMm: 50,
    );

    expect($paginas)->toHaveCount(3)
        ->and($paginas[0])->toHaveCount(5)
        ->and($paginas[1])->toHaveCount(6)
        ->and($paginas[2])->toHaveCount(1);
});

test('deja la primera pagina sin tabla cuando no alcanza ninguna fila', function (): void {
    $paginador = new ReportePaginador(new LayoutPdf(
        tamano: TamanoPapel::LETTER,
        orientacion: Orientacion::Vertical,
    ));

    $paginas = $paginador->chunkParaPdf(
        items: collect(range(1, 3)),
        altoFilaMm: 20,
        altoExtraPrimeraPaginaMm: 300,
    );

    expect($paginas[0])->toHaveCount(0)
        ->and($paginas[1])->toHaveCount(3);
});
