<?php

declare(strict_types=1);

namespace App\Support\Pdf;

final readonly class LayoutPdf
{
    public int $areaUtilMm;

    public function __construct(
        public TamanoPapel $tamano = TamanoPapel::A4,
        public Orientacion $orientacion = Orientacion::Vertical,
        public int $margenSuperiorMm = 5,
        public int $margenInferiorMm = 5,
        public int $altoEncabezadoMm = 22,
        public int $altoPieMm = 6,
    ) {
        $this->areaUtilMm = $this->calcularAreaUtil();
    }

    public function altoPaginaMm(): int
    {
        return $this->orientacion->altoPaginaMm($this->tamano);
    }

    public function altoContenidoMm(): int
    {
        return $this->altoPaginaMm() - ($this->margenSuperiorMm + $this->margenInferiorMm);
    }

    private function calcularAreaUtil(): int
    {
        return max(1,
            $this->altoContenidoMm()
            - $this->altoEncabezadoMm
            - $this->altoPieMm
        );
    }
}
