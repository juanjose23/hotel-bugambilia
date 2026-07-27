<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\BusinessLogic\Restaurante\Auditoria\RegistrarAuditoriaRestaurante;
use App\BusinessLogic\Restaurante\Mesas\VerificarRestauranteActivo;
use App\Enums\Restaurante\AccionAuditoriaRestaurante;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\User;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use BackedEnum;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use UnitEnum;

final class ConfiguracionRestaurante extends Page
{
    use HasPageShield;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Configuración POS';

    protected static ?string $title = 'Configuración General del Restaurante & POS';

    protected static ?string $slug = 'restaurante/configuracion';

    protected static ?int $navigationSort = 15;

    protected string $view = 'filament.resources.restaurante.configuracion-restaurante';

    public float $propinaSugerida = 10.0;

    public float $impuestoPorcentaje = 15.0;

    public string $impresoraCocina = 'Termica_Cocina';

    public string $impresoraBar = 'Termica_Bar';

    public string $impresoraPostres = 'Termica_Postres';

    public string $impresoraParrilla = 'Termica_Parrilla';

    public bool $impresionAutomatica = true;

    public int $copiasTicket = 1;

    private RestauranteRepositorioInterface $repositorio;

    public function boot(RestauranteRepositorioInterface $repositorio): void
    {
        $this->repositorio = $repositorio;
    }

    public function mount(): void
    {
        $restaurante = $this->repositorio->obtenerRestaurantePrincipal();
        if ($restaurante instanceof Espacio && is_array($restaurante->meta_datos)) {
            $meta = $restaurante->meta_datos;
            $this->propinaSugerida = (float) ($meta['propina_sugerida'] ?? 10.0);
            $this->impuestoPorcentaje = (float) ($meta['impuesto_porcentaje'] ?? 15.0);
            $this->impresoraCocina = (string) ($meta['impresora_cocina'] ?? 'Termica_Cocina');
            $this->impresoraBar = (string) ($meta['impresora_bar'] ?? 'Termica_Bar');
            $this->impresoraPostres = (string) ($meta['impresora_postres'] ?? 'Termica_Postres');
            $this->impresoraParrilla = (string) ($meta['impresora_parrilla'] ?? 'Termica_Parrilla');
            $this->impresionAutomatica = (bool) ($meta['impresion_automatica'] ?? true);
            $this->copiasTicket = (int) ($meta['copias_ticket'] ?? 1);
        }
    }

    public function guardar(RegistrarAuditoriaRestaurante $auditoria): void
    {
        $restaurante = $this->repositorio->obtenerRestaurantePrincipal();

        if (! $restaurante instanceof Espacio) {
            Notification::make()
                ->title('Restaurante no encontrado')
                ->danger()
                ->send();

            return;
        }

        $meta = is_array($restaurante->meta_datos) ? $restaurante->meta_datos : [];
        $meta['propina_sugerida'] = $this->propinaSugerida;
        $meta['impuesto_porcentaje'] = $this->impuestoPorcentaje;
        $meta['impresora_cocina'] = $this->impresoraCocina;
        $meta['impresora_bar'] = $this->impresoraBar;
        $meta['impresora_postres'] = $this->impresoraPostres;
        $meta['impresora_parrilla'] = $this->impresoraParrilla;
        $meta['impresion_automatica'] = $this->impresionAutomatica;
        $meta['copias_ticket'] = $this->copiasTicket;

        $restaurante->meta_datos = $meta;
        $this->repositorio->guardarMesa($restaurante);

        $auditoria->registrar(
            accion: AccionAuditoriaRestaurante::GuardarConfiguracion,
            detalles: $meta,
            userId: auth()->id() !== null ? (int) auth()->id() : null,
            ipAddress: request()->ip(),
        );

        Notification::make()
            ->title('Configuración del restaurante guardada exitosamente')
            ->success()
            ->send();
    }

    public static function canAccess(): bool
    {
        if (! app(VerificarRestauranteActivo::class)->estaActivo()) {
            return false;
        }

        /** @var User|null $user */
        $user = auth()->user();

        return $user?->can('page_ConfiguracionRestaurante') ?? true;
    }
}
