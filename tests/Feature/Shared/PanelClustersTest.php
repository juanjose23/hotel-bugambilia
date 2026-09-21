<?php

declare(strict_types=1);

namespace Tests\Feature\Shared;

use App\Filament\Clusters\Auditoria\AuditoriaCluster;
use App\Filament\Clusters\Configuracion\ConfiguracionCluster;
use App\Filament\Clusters\FacturacionConfig\FacturacionConfigCluster;
use App\Filament\Resources\Auditoria\Audits\AuditResource;
use App\Filament\Resources\Catalogos\Moneda\MonedaResource;
use App\Filament\Resources\Catalogos\Pais\PaisResource;
use App\Filament\Resources\Catalogos\Politicas\PoliticasResource;
use App\Filament\Resources\Catalogos\Ubicaciones\UbicacionResource;
use App\Filament\Resources\Facturacion\FacturaAutorizacionDgiResource\FacturaAutorizacionDgiResource;
use App\Filament\Resources\Facturacion\FacturaFolioResource\FacturaFolioResource;
use App\Filament\Resources\Facturacion\FacturaSerieResource\FacturaSerieResource;
use App\Filament\Resources\Facturacion\PagoConciliacionResource\PagoConciliacionResource;
use App\Filament\Resources\Facturacion\PasarelaPagoResource\PasarelaPagoResource;
use App\Filament\Resources\Usuarios\ConflictosIdentidad\ConflictoIdentidadResource;
use Filament\Pages\Enums\SubNavigationPosition;
use Tests\TestCase;

final class PanelClustersTest extends TestCase
{
    public function test_clusters_tienen_posicion_subnavegacion_lateral_derecha(): void
    {
        $this->assertEquals(SubNavigationPosition::End, ConfiguracionCluster::getSubNavigationPosition());
        $this->assertEquals(SubNavigationPosition::End, FacturacionConfigCluster::getSubNavigationPosition());
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
        $this->assertEquals(FacturacionConfigCluster::class, FacturaSerieResource::getCluster());
        $this->assertEquals(FacturacionConfigCluster::class, FacturaFolioResource::getCluster());
        $this->assertEquals(FacturacionConfigCluster::class, FacturaAutorizacionDgiResource::getCluster());
        $this->assertEquals(FacturacionConfigCluster::class, PasarelaPagoResource::getCluster());
        $this->assertEquals(FacturacionConfigCluster::class, PagoConciliacionResource::getCluster());
    }

    public function test_recursos_estan_asignados_al_cluster_de_auditoria(): void
    {
        $this->assertEquals(AuditoriaCluster::class, AuditResource::getCluster());
        $this->assertEquals(AuditoriaCluster::class, ConflictoIdentidadResource::getCluster());
    }
}
