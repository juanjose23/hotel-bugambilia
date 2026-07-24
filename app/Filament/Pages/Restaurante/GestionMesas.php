<?php

declare(strict_types=1);

namespace App\Filament\Pages\Restaurante;

use App\Enums\HabitacionesEspacios\EstadoEspacio;
use App\Interactors\Restaurante\CambiarEstadoMesa;
use App\Interactors\Restaurante\SepararMesas;
use App\Interactors\Restaurante\UnirMesas;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Reservas\Reserva;
use App\Repository\Models\User;
use App\Repository\Queries\Restaurante\ObtenerMapaMesasQuery;
use BackedEnum;
use Exception;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use UnitEnum;

final class GestionMesas extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-table-cells';

    protected static string|UnitEnum|null $navigationGroup = 'Restaurante';

    protected static ?string $navigationLabel = 'Gestión de Mesas';

    protected static ?string $title = 'Mapa de Mesas e Interfaz de Servicio';

    protected static ?string $slug = 'restaurante/mesas';

    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.gestion-mesas';

    /** @var Collection<int, Espacio> */
    public Collection $ambientes;

    /** @var Collection<int, Espacio> */
    public Collection $mesas;

    public ?int $mesaSeleccionadaId = null;

    /** @var int[] */
    public array $mesasParaUnir = [];

    public string $motivoUnion = 'uso_inmediato';

    public ?int $reservaIdParaUnion = null;

    /** @var Collection<int, Reserva> */
    public Collection $reservasRestaurante;

    private ObtenerMapaMesasQuery $mapaMesas;

    private CambiarEstadoMesa $cambiarEstado;

    private UnirMesas $unirMesasInteractor;

    private SepararMesas $separarMesasInteractor;

    public function boot(
        ObtenerMapaMesasQuery $mapaMesas,
        CambiarEstadoMesa $cambiarEstado,
        UnirMesas $unirMesasInteractor,
        SepararMesas $separarMesasInteractor
    ): void {
        $this->mapaMesas = $mapaMesas;
        $this->cambiarEstado = $cambiarEstado;
        $this->unirMesasInteractor = $unirMesasInteractor;
        $this->separarMesasInteractor = $separarMesasInteractor;
    }

    public function mount(): void
    {
        $this->recargarMesas();
        $this->reservasRestaurante = Reserva::query()
            ->whereNotNull('espacio_id')
            ->latest('id')
            ->limit(20)
            ->get();
    }

    public function recargarMesas(): void
    {
        $mapa = $this->mapaMesas->ejecutar();
        $this->ambientes = $mapa['ambientes'];
        $this->mesas = $mapa['mesas'];
    }

    public function cambiarEstadoMesa(int $mesaId, string $nuevoEstado): void
    {
        $estado = EstadoEspacio::tryFrom((int) $nuevoEstado);

        if ($estado === null) {
            Notification::make()->title('Estado de mesa inválido')->danger()->send();

            return;
        }

        $mesa = $this->cambiarEstado->ejecutar($mesaId, $estado);
        $this->recargarMesas();

        Notification::make()
            ->title("Mesa {$mesa->nombre} cambiada a {$estado->getLabel()}")
            ->success()
            ->send();
    }

    public function unirMesas(): void
    {
        if ($this->mesaSeleccionadaId === null || empty($this->mesasParaUnir)) {
            Notification::make()
                ->title('Selección incompleta')
                ->body('Debe seleccionar una mesa principal y al menos una mesa secundaria para unir.')
                ->warning()
                ->send();

            return;
        }

        try {
            $this->unirMesasInteractor->ejecutar(
                mesaPrincipalId: $this->mesaSeleccionadaId,
                mesasSecundariasIds: $this->mesasParaUnir,
                reservaId: $this->reservaIdParaUnion,
                motivo: $this->motivoUnion
            );

            $this->mesaSeleccionadaId = null;
            $this->mesasParaUnir = [];
            $this->reservaIdParaUnion = null;
            $this->recargarMesas();

            Notification::make()
                ->title('Mesas unidas exitosamente')
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al unir mesas')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function separarMesas(int $mesaId): void
    {
        try {
            $this->separarMesasInteractor->ejecutar($mesaId);
            $this->recargarMesas();

            Notification::make()
                ->title('Mesa desvinculada y liberada')
                ->success()
                ->send();
        } catch (Exception $e) {
            Notification::make()
                ->title('Error al separar mesa')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * @return array{colorBadge: string, bgDot: string, borderCircle: string, borderCard: string, badgeLabel: string}
     */
    public function obtenerConfiguracionEstiloMesa(int $estadoValor): array
    {
        return match ($estadoValor) {
            1 => [
                'colorBadge' => 'success',
                'bgDot' => 'bg-emerald-500',
                'borderCircle' => 'border-emerald-500 dark:border-emerald-400',
                'borderCard' => 'border-emerald-500/40 dark:border-emerald-500/30 bg-emerald-500/5 dark:bg-emerald-950/20',
                'badgeLabel' => 'Disponible',
            ],
            3, 6 => [
                'colorBadge' => 'warning',
                'bgDot' => 'bg-amber-500',
                'borderCircle' => 'border-amber-500 dark:border-amber-400',
                'borderCard' => 'border-amber-500/40 dark:border-amber-500/30 bg-amber-500/5 dark:bg-amber-950/20',
                'badgeLabel' => 'En Atención',
            ],
            4 => [
                'colorBadge' => 'info',
                'bgDot' => 'bg-sky-500',
                'borderCircle' => 'border-sky-500 dark:border-sky-400',
                'borderCard' => 'border-sky-500/40 dark:border-sky-500/30 bg-sky-500/5 dark:bg-sky-950/20',
                'badgeLabel' => 'Reservada',
            ],
            5 => [
                'colorBadge' => 'danger',
                'bgDot' => 'bg-rose-500',
                'borderCircle' => 'border-rose-500 dark:border-rose-400',
                'borderCard' => 'border-rose-500/40 dark:border-rose-500/30 bg-rose-500/5 dark:bg-rose-950/20',
                'badgeLabel' => 'Ocupada',
            ],
            default => [
                'colorBadge' => 'gray',
                'bgDot' => 'bg-slate-500',
                'borderCircle' => 'border-slate-500 dark:border-slate-400',
                'borderCard' => 'border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50',
                'badgeLabel' => 'Inactiva',
            ],
        };
    }

    public static function canAccess(): bool
    {
        /** @var User|null $user */
        $user = auth()->user();

        return $user?->can('page_GestionMesas') ?? false;
    }
}
