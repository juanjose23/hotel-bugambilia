<?php

declare(strict_types=1);

namespace App\Support\Pdf;

final readonly class LayoutPdf
{
    public const int MARGEN_LATERAL_MM = 2;

    public int $areaUtilMm;

    public function __construct(
        public TamanoPapel $tamano = TamanoPapel::LETTER,
        public Orientacion $orientacion = Orientacion::Vertical,
        public int $margenSuperiorMm = 5,
        public int $margenInferiorMm = 5,
        public int $margenLateralMm = self::MARGEN_LATERAL_MM,
        public int $altoEncabezadoMm = 32,
        public int $altoPieMm = 14,
    ) {
        $this->areaUtilMm = $this->calcularAreaUtil();
    }

    public function altoPaginaMm(): int
    {
        return $this->orientacion->altoPaginaMm($this->tamano);
    }

    public function anchoPaginaMm(): int
    {
        return match ($this->orientacion) {
            Orientacion::Vertical => $this->tamano->anchoMm(),
            Orientacion::Horizontal => $this->tamano->altoMm(),
        };
    }

    public function altoContenidoMm(): int
    {
        return $this->altoPaginaMm() - ($this->margenSuperiorMm + $this->margenInferiorMm);
    }

    public function anchoContenidoMm(): int
    {
        return max(1, $this->anchoPaginaMm() - ($this->margenLateralMm * 2));
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
