<?php

declare(strict_types=1);

namespace Database\Seeders\Configuracion;

use App\Enums\Facturacion\EstadoFolioFactura;
use App\Repository\Models\Facturacion\FacturaAutorizacionDgi;
use App\Repository\Models\Facturacion\FacturaFolio;
use App\Repository\Models\Facturacion\FacturaSerie;
use Illuminate\Database\Seeder;

/**
 * Configuración fiscal del Hotel Bugambilias adaptada a la normativa DGI
 * (Dirección General de Ingresos) de Nicaragua.
 *
 * - Serie A: Factura de Crédito Fiscal (factura original pre-impresa).
 * - Serie B: Factura de Consumidor Final (FCF / ticket de máquina fiscal).
 *
 * Cada serie tiene su Aprobación/Resolución DGI con RUC del emisor, rango
 * autorizado y pie de imprenta fiscal. Los folios se reservan bajo demanda a
 * través de ReservarFolioFactura; este seeder solo materializa los folios que
 * quedaron "quemados" (anulados) como es habitual en los pre-impresos.
 */
class FacturaFiscalSeeder extends Seeder
{
    private const RUC_EMISOR = 'J0310000523456';

    private const RAZON_SOCIAL = 'Hotel Bugambilias, S.A.';

    private const NOMBRE_COMERCIAL = 'Hotel Bugambilias';

    private const DIRECCION = 'Carretera a Masaya Km. 8.5, Managua, Nicaragua';

    private const IMPRENTA = 'GRAFICAS MODERNAS, S.A. RUC: J0310000876543';

    public function run(): void
    {
        $this->crearSerieFacturaOriginal();
        $this->crearSerieFacturaConsumidorFinal();
    }

    private function crearSerieFacturaOriginal(): void
    {
        $serie = $this->crearSerie([
            'codigo' => 'A',
            'nombre' => 'Factura de Crédito Fiscal – Original',
            'sucursal_codigo' => 'SUC-001',
            'caja_codigo' => 'CAJA-01',
        ]);

        $autorizacion = $this->crearAutorizacion($serie, [
            'numero_autorizacion' => 'DGI-0042-2026',
            'fecha_autorizacion' => '2026-01-15',
            'rango_desde' => 1,
            'rango_hasta' => 25000,
            'pie_imprenta_fiscal' => sprintf(
                'AUTORIZADO POR LA DGI. RES. N° 0042-2026 DEL 15/01/2026. '.
                'RANGO 00000001 - 00025000. IMPRESO POR: %s',
                self::IMPRENTA,
            ),
        ]);

        // Folio pre-impreso dañado en imprenta (es práctica común anularlo en sitio).
        $this->crearFolioAnulado(
            $serie,
            $autorizacion,
            correlativo: 1,
            motivo: 'Folio dañado durante la impresión inicial de la serie pre-impresa.',
        );
    }

    private function crearSerieFacturaConsumidorFinal(): void
    {
        $serie = $this->crearSerie([
            'codigo' => 'B',
            'nombre' => 'Factura de Consumidor Final (FCF)',
            'sucursal_codigo' => 'SUC-001',
            'caja_codigo' => 'CAJA-02',
        ]);

        $this->crearAutorizacion($serie, [
            'numero_autorizacion' => 'DGI-0043-2026',
            'fecha_autorizacion' => '2026-02-01',
            'rango_desde' => 1,
            'rango_hasta' => 40000,
            'pie_imprenta_fiscal' => sprintf(
                'AUTORIZADO POR LA DGI. RES. N° 0043-2026 DEL 01/02/2026. '.
                'RANGO 00000001 - 00040000. EQUIPO FISCAL HOMOLOGADO. IMPRESO POR: %s',
                self::IMPRENTA,
            ),
        ]);
    }

    /**
     * @param  array<string, string>  $datos
     */
    private function crearSerie(array $datos): FacturaSerie
    {
        $serie = FacturaSerie::query()->updateOrCreate(
            ['codigo' => $datos['codigo']],
            array_merge($datos, [
                'activa' => true,
            ]),
        );

        if ($serie->wasRecentlyCreated) {
            $serie->update(['siguiente_numero' => 1]);
        }

        return $serie;
    }

    /**
     * @param  array<string, string|int>  $datos
     */
    private function crearAutorizacion(FacturaSerie $serie, array $datos): FacturaAutorizacionDgi
    {
        /** @var FacturaAutorizacionDgi $autorizacion */
        $autorizacion = FacturaAutorizacionDgi::query()->updateOrCreate(
            ['numero_autorizacion' => $datos['numero_autorizacion']],
            array_merge($datos, [
                'factura_serie_id' => $serie->id,
                'ruc_emisor' => self::RUC_EMISOR,
                'razon_social_emisor' => self::RAZON_SOCIAL,
                'nombre_comercial_emisor' => self::NOMBRE_COMERCIAL,
                'direccion_emisor' => self::DIRECCION,
                'vence_at' => null,
                'activa' => true,
            ]),
        );

        return $autorizacion;
    }

    /**
     * Materializa un folio anulado dentro del rango autorizado y avanza el
     * correlativo de la serie para que las próximas facturas comiencen después.
     */
    private function crearFolioAnulado(
        FacturaSerie $serie,
        FacturaAutorizacionDgi $autorizacion,
        int $correlativo,
        string $motivo,
    ): void {
        FacturaFolio::query()->updateOrCreate(
            [
                'factura_serie_id' => $serie->id,
                'numero_correlativo' => $correlativo,
            ],
            [
                'factura_autorizacion_dgi_id' => $autorizacion->id,
                'numero' => $serie->codigo.'-'.str_pad((string) $correlativo, 8, '0', STR_PAD_LEFT),
                'estado' => EstadoFolioFactura::Anulado,
                'anulado_at' => now(),
                'motivo' => $motivo,
            ],
        );

        if ((int) $serie->siguiente_numero <= $correlativo) {
            $serie->update(['siguiente_numero' => $correlativo + 1]);
        }
    }
}
