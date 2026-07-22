<?php

declare(strict_types=1);

namespace App\Filament\Shared\RelationManagers;

use App\BusinessLogic\Shared\ServicioPrecios;
use App\Filament\Shared\Concerns\TieneFormularioPrecios;
use App\Interactors\Shared\AsignarPrecio;
use App\Repository\Models\Monedas\Moneda;
use App\Repository\Models\Servicios\Servicio;
use App\Repository\Models\Shared\Precio;
use App\Repository\Queries\Shared\VerificarPrecioDuplicado;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PreciosRelationManager extends RelationManager
{
    use TieneFormularioPrecios;

    public function boot(
        VerificarPrecioDuplicado $verificarPrecioDuplicado,
        AsignarPrecio $asignarPrecio,
        ServicioPrecios $servicioPrecios
    ): void {

        $this->verificarPrecioDuplicado = $verificarPrecioDuplicado;
        $this->asignarPrecio = $asignarPrecio;
        $this->servicioPrecios = $servicioPrecios;
    }

    protected static string $relationship = 'precios';

    protected static ?string $label = 'Precio';

    protected static ?string $pluralLabel = 'Precios';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        if ($ownerRecord instanceof Servicio) {
            return 'Histórico de Precios';
        }

        return 'Precios y Tarifas';
    }

    protected function getPriceableModelClass(): string
    {
        return Precio::class;
    }

    protected function getPriceableForeignKey(): string
    {
        return 'priceable_id';
    }

    protected function getPriceableForeignType(): ?string
    {
        return 'priceable_type';
    }

    protected function hasTipoPrecioField(): bool
    {
        $owner = $this->getOwnerRecord();
        if ($owner instanceof Servicio) {
            return false;
        }

        return true;
    }

    protected function getDefaultMonedaId(): ?int
    {
        $id = Moneda::query()
            ->where('codigo', 'NIO')
            ->value('id')
            ?? Moneda::query()
                ->where('es_predeterminada', true)
                ->value('id');

        return $id !== null && is_numeric($id) ? intval($id) : null;
    }

    protected function getUniquePrecioErrorMessage(): string
    {
        $owner = $this->getOwnerRecord();
        if ($owner instanceof Servicio) {
            return 'Ya existe un precio vigente activo para este servicio y esta moneda. Desactive el precio anterior antes de guardar.';
        }

        return 'Ya existe un precio o tarifa vigente activa para este registro, esta moneda y este concepto. Desactive el precio anterior antes de guardar.';
    }

    /**
     * @param  Builder<Precio>  $query
     * @return Builder<Precio>
     */
    protected function modifyQueryUsing(Builder $query): Builder
    {
        return $query->with(['moneda']);
    }
}
