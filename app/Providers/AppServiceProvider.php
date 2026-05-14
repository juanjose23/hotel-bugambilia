<?php

namespace App\Providers;

use App\Models\Compras\OrdenCompra;
use App\Models\Compras\RecepcionCompra;
use App\Models\Compras\Solicitud;
use App\Observers\Compras\RecepcionObserver;
use App\Policies\Compras\OrdenCompraPolicy;
use App\Policies\Compras\RecepcionCompraPolicy;
use App\Policies\Compras\SolicitudPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

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
        Gate::policy(Solicitud::class, SolicitudPolicy::class);
        Gate::policy(OrdenCompra::class, OrdenCompraPolicy::class);
        Gate::policy(RecepcionCompra::class, RecepcionCompraPolicy::class);
    }
}
