<?php

declare(strict_types=1);

namespace Tests\Feature\Shared;

use App\Filament\Clusters\Auditoria\AuditoriaCluster;
use App\Filament\Clusters\Cajas\CajasCluster;
use App\Filament\Clusters\Configuracion\ConfiguracionCluster;
use App\Filament\Clusters\Facturacion\FacturacionCluster;
use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Filament\Clusters\Tesoreria\TesoreriaCluster;
use App\Filament\Resources\Auditoria\Audits\AuditResource;
use App\Filament\Resources\Catalogos\Moneda\MonedaResource;
use App\Filament\Resources\Catalogos\Pais\PaisResource;
use App\Filament\Resources\Catalogos\Politicas\PoliticasResource;
use App\Filament\Resources\Catalogos\Ubicaciones\UbicacionResource;
use App\Filament\Resources\Cuentas\CargoFacturacionResource\CargoFacturacionResource;
use App\Filament\Resources\Cuentas\CuentaResource;
use App\Filament\Resources\Cuentas\VentaResource\VentaResource;
use App\Filament\Resources\Facturacion\FacturaAutorizacionDgiResource\FacturaAutorizacionDgiResource;
use App\Filament\Resources\Facturacion\FacturaFolioResource\FacturaFolioResource;
use App\Filament\Resources\Facturacion\FacturaResource\FacturaResource;
use App\Filament\Resources\Facturacion\FacturaSerieResource\FacturaSerieResource;
use App\Filament\Resources\Facturacion\PagoConciliacionResource\PagoConciliacionResource;
use App\Filament\Resources\Facturacion\PagoTransaccionResource\PagoTransaccionResource;
use App\Filament\Resources\Facturacion\PasarelaPagoResource\PasarelaPagoResource;
use App\Filament\Resources\Usuarios\ConflictosIdentidad\ConflictoIdentidadResource;
use Filament\Pages\Enums\SubNavigationPosition;
use Tests\TestCase;

final class PanelClustersTest extends TestCase
{
    public function test_clusters_conservan_su_posicion_de_subnavegacion(): void
    {
        $this->assertEquals(SubNavigationPosition::End, ConfiguracionCluster::getSubNavigationPosition());
        $this->assertEquals(SubNavigationPosition::Start, FacturacionConfigCluster::getSubNavigationPosition());
        $this->assertEquals(SubNavigationPosition::Start, CajasCluster::getSubNavigationPosition());
        $this->assertEquals(SubNavigationPosition::Start, FacturacionCluster::getSubNavigationPosition());
        $this->assertEquals(SubNavigationPosition::Start, TesoreriaCluster::getSubNavigationPosition());
        $this->assertEquals(SubNavigationPosition::End, AuditoriaCluster::getSubNavigationPosition());
    }

    public function test_recursos_estan_asignados_al_cluster_de_configuracion(): void
    {
        $this->assertEquals(ConfiguracionCluster::class, MonedaResource::getCluster());
        $this->assertEquals(ConfiguracionCluster::class, PaisResource::getCluster());
        $this->assertEquals(ConfiguracionCluster::class, UbicacionResource::getCluster());
        $this->assertEquals(ConfiguracionCluster::class, PoliticasResource::getCluster());
    }

    public function test_recursos_estan_asignados_al_cluster_de_facturacion(): void
    {
        $this->assertEquals(FacturacionCluster::class, CuentaResource::getCluster());
        $this->assertEquals(FacturacionCluster::class, FacturaResource::getCluster());
        $this->assertEquals(FacturacionCluster::class, VentaResource::getCluster());
        $this->assertEquals(FacturacionCluster::class, PagoTransaccionResource::getCluster());
    }

    public function test_recursos_estan_asignados_al_cluster_de_tesoreria(): void
    {
        $this->assertEquals(TesoreriaCluster::class, PagoConciliacionResource::getCluster());
    }

    public function test_recursos_estan_asignados_al_cluster_de_configuracion_fiscal(): void
    {
        $this->assertEquals(FacturacionConfigCluster::class, FacturaSerieResource::getCluster());
        $this->assertEquals(FacturacionConfigCluster::class, FacturaFolioResource::getCluster());
        $this->assertEquals(FacturacionConfigCluster::class, FacturaAutorizacionDgiResource::getCluster());
        $this->assertEquals(FacturacionConfigCluster::class, PasarelaPagoResource::getCluster());
        $this->assertEquals(FacturacionConfigCluster::class, CargoFacturacionResource::getCluster());
    }

    public function test_recursos_estan_asignados_al_cluster_de_auditoria(): void
    {
        $this->assertEquals(AuditoriaCluster::class, AuditResource::getCluster());
        $this->assertEquals(AuditoriaCluster::class, ConflictoIdentidadResource::getCluster());
    }
}
