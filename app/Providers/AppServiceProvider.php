<?php

namespace App\Providers;

use App\Models\Activos\ActivoMantenimiento;
use App\Models\Audits\AuditoriaReporte;
use App\Models\Compras\DevolucionCompra;
use App\Models\Compras\OrdenCompra;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\Solicitud;
use App\Models\Habitaciones\Habitacion;
use App\Models\User;
use App\Observers\Activos\ActivoMantenimientoObserver;
use App\Observers\Compras\OrdenCompraObserver;
use App\Observers\Compras\RecepcionObserver;
use App\Observers\Habitaciones\HabitacionHistorialObserver;
use App\Observers\Inventario\RecepcionInventoryObserver;
use App\Policies\AuditPolicy;
use App\Policies\Audits\AuditoriaReportePolicy;
use App\Policies\Compras\DevolucionCompraPolicy;
use App\Policies\Compras\OrdenCompraPolicy;
use App\Policies\Compras\RecepcionCompraPolicy;
use App\Policies\Compras\SolicitudPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use OwenIt\Auditing\Models\Audit;
use Spatie\Permission\Models\Role;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RecepcionCompra::observe(RecepcionObserver::class);
        RecepcionCompra::observe(RecepcionInventoryObserver::class);
        OrdenCompra::observe(OrdenCompraObserver::class);
        ActivoMantenimiento::observe(ActivoMantenimientoObserver::class);
        Habitacion::observe(HabitacionHistorialObserver::class);
        Gate::policy(Solicitud::class, SolicitudPolicy::class);
        Gate::policy(OrdenCompra::class, OrdenCompraPolicy::class);
        Gate::policy(RecepcionCompra::class, RecepcionCompraPolicy::class);
        Gate::policy(DevolucionCompra::class, DevolucionCompraPolicy::class);

        // Registrar políticas para modelos externos o en rutas no estándar
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Audit::class, AuditPolicy::class);
        Gate::policy(Role::class, RolePolicy::class);
        Gate::policy(AuditoriaReporte::class, AuditoriaReportePolicy::class);

        // Prevenir carga diferida (lazy loading) en desarrollo y testing para atrapar consultas N+1
        Model::preventLazyLoading(! $this->app->isProduction());
    }
}
