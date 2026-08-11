<?php

namespace App\Providers;

use App\Notifications\Reservas\Contracts\UrlNotificadorInterface as UrlNotificadorReservasInterface;
use App\Notifications\Reservas\UrlNotificador as UrlNotificadorReservas;
use App\Repository\Models\Activos\ActivoMantenimiento;
use App\Repository\Models\Compras\OrdenCompra;
use App\Repository\Models\Compras\RecepcionCompra;
use App\Repository\Models\Espacios\Espacio;
use App\Repository\Models\Habitaciones\Habitacion;
use App\Repository\Models\Limpieza\LimpiezaEjecucion;
use App\Repository\Models\Limpieza\SolicitudLimpieza;
use App\Repository\Models\User;
use App\Repository\Observers\Activos\ActivoMantenimientoObserver;
use App\Repository\Observers\Compras\OrdenCompraObserver;
use App\Repository\Observers\Compras\RecepcionObserver;
use App\Repository\Observers\Espacios\EspacioHistorialObserver;
use App\Repository\Observers\Habitaciones\HabitacionHistorialObserver;
use App\Repository\Observers\Inventario\RecepcionInventoryObserver;
use App\Repository\Observers\Limpieza\LimpiezaEjecucionObserver;
use App\Repository\Observers\Limpieza\SolicitudLimpiezaObserver;
use App\Repository\Persistencia\Activos\ActivoAsignacionRepositorio;
use App\Repository\Persistencia\Activos\ActivoAsignacionRepositorioInterface;
use App\Repository\Persistencia\Activos\ActivoBajaRepositorio;
use App\Repository\Persistencia\Activos\ActivoBajaRepositorioInterface;
use App\Repository\Persistencia\Activos\ActivoMantenimientoRepositorio;
use App\Repository\Persistencia\Activos\ActivoMantenimientoRepositorioInterface;
use App\Repository\Persistencia\Activos\ActivoRepositorio;
use App\Repository\Persistencia\Activos\ActivoRepositorioInterface;
use App\Repository\Persistencia\Activos\ActPlanMantenimientoRepositorio;
use App\Repository\Persistencia\Activos\ActPlanMantenimientoRepositorioInterface;
use App\Repository\Persistencia\Activos\PrefijoCodigoRepositorio;
use App\Repository\Persistencia\Activos\PrefijoCodigoRepositorioInterface;
use App\Repository\Persistencia\Activos\RegistroIndividualizacionRepositorio;
use App\Repository\Persistencia\Activos\RegistroIndividualizacionRepositorioInterface;
use App\Repository\Persistencia\Compras\DevolucionRepositorio;
use App\Repository\Persistencia\Compras\DevolucionRepositorioInterface;
use App\Repository\Persistencia\Compras\OrdenCompraRepositorio;
use App\Repository\Persistencia\Compras\OrdenCompraRepositorioInterface;
use App\Repository\Persistencia\Compras\ProveedorRepositorio;
use App\Repository\Persistencia\Compras\ProveedorRepositorioInterface;
use App\Repository\Persistencia\Compras\RecepcionRepositorio;
use App\Repository\Persistencia\Compras\RecepcionRepositorioInterface;
use App\Repository\Persistencia\Compras\SolicitudRepositorio;
use App\Repository\Persistencia\Compras\SolicitudRepositorioInterface;
use App\Repository\Persistencia\Cuentas\CuentaRepositorio;
use App\Repository\Persistencia\Cuentas\CuentaRepositorioInterface;
use App\Repository\Persistencia\Habitaciones\HabitacionRepositorio;
use App\Repository\Persistencia\Habitaciones\HabitacionRepositorioInterface;
use App\Repository\Persistencia\Inventario\InventarioFisicoRepositorio;
use App\Repository\Persistencia\Inventario\InventarioFisicoRepositorioInterface;
use App\Repository\Persistencia\Inventario\LoteRepositorio;
use App\Repository\Persistencia\Inventario\LoteRepositorioInterface;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorio;
use App\Repository\Persistencia\Inventario\MovimientoStockRepositorioInterface;
use App\Repository\Persistencia\Inventario\StockRepositorio;
use App\Repository\Persistencia\Inventario\StockRepositorioInterface;
use App\Repository\Persistencia\Reservas\ReservaRepositorio;
use App\Repository\Persistencia\Reservas\ReservaRepositorioInterface;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorio;
use App\Repository\Persistencia\Restaurante\RestauranteRepositorioInterface;
use App\Repository\Persistencia\Servicios\ServicioRepositorio;
use App\Repository\Persistencia\Servicios\ServicioRepositorioInterface;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            HabitacionRepositorioInterface::class,
            HabitacionRepositorio::class
        );

        $this->app->bind(
            ServicioRepositorioInterface::class,
            ServicioRepositorio::class
        );

        $this->app->bind(
            ProveedorRepositorioInterface::class,
            ProveedorRepositorio::class
        );

        $this->app->bind(
            LoteRepositorioInterface::class,
            LoteRepositorio::class
        );

        $this->app->bind(
            StockRepositorioInterface::class,
            StockRepositorio::class
        );

        $this->app->bind(
            MovimientoStockRepositorioInterface::class,
            MovimientoStockRepositorio::class
        );

        $this->app->bind(
            InventarioFisicoRepositorioInterface::class,
            InventarioFisicoRepositorio::class
        );

        $this->app->bind(
            DevolucionRepositorioInterface::class,
            DevolucionRepositorio::class
        );

        $this->app->bind(
            ActivoRepositorioInterface::class,
            ActivoRepositorio::class
        );

        $this->app->bind(
            ActivoAsignacionRepositorioInterface::class,
            ActivoAsignacionRepositorio::class
        );

        $this->app->bind(
            ActivoMantenimientoRepositorioInterface::class,
            ActivoMantenimientoRepositorio::class
        );

        $this->app->bind(
            ActivoBajaRepositorioInterface::class,
            ActivoBajaRepositorio::class
        );

        $this->app->bind(
            ActPlanMantenimientoRepositorioInterface::class,
            ActPlanMantenimientoRepositorio::class
        );

        $this->app->bind(
            PrefijoCodigoRepositorioInterface::class,
            PrefijoCodigoRepositorio::class
        );

        $this->app->bind(
            RegistroIndividualizacionRepositorioInterface::class,
            RegistroIndividualizacionRepositorio::class
        );

        $this->app->bind(
            OrdenCompraRepositorioInterface::class,
            OrdenCompraRepositorio::class
        );

        $this->app->bind(
            RecepcionRepositorioInterface::class,
            RecepcionRepositorio::class
        );

        $this->app->bind(
            SolicitudRepositorioInterface::class,
            SolicitudRepositorio::class
        );

        $this->app->bind(
            ReservaRepositorioInterface::class,
            ReservaRepositorio::class
        );

        $this->app->bind(
            CuentaRepositorioInterface::class,
            CuentaRepositorio::class
        );

        $this->app->bind(
            RestauranteRepositorioInterface::class,
            RestauranteRepositorio::class
        );

        $this->app->bind(
            UrlNotificadorReservasInterface::class,
            UrlNotificadorReservas::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // @phpstan-ignore argument.type (Larastan factory namespace resolution)
        Factory::guessFactoryNamesUsing(function (string $modelClass): string {
            $prefix = 'App\\Repository\\Models\\';

            if (str_starts_with($modelClass, $prefix)) {
                $relative = substr($modelClass, strlen($prefix));
                $parts = explode('\\', $relative);
                $className = array_pop($parts);
                $factoryClass = 'Database\\Factories\\'.implode('\\', $parts).'\\'.$className.'Factory';
                if (class_exists($factoryClass)) {
                    return $factoryClass;
                }
            }

            // Standard Laravel convention: Database\\Factories\\<ClassName>Factory
            $baseName = class_basename($modelClass);

            return 'Database\\Factories\\'.$baseName.'Factory';
        });

        $this->configureDefaults();

        View::composer('reports.*', function ($view) {
            $view->with('usuario', auth()->user() ? auth()->user()->name : 'Sistema');
        });

        OrdenCompra::observe(OrdenCompraObserver::class);
        RecepcionCompra::observe(RecepcionObserver::class);
        RecepcionCompra::observe(RecepcionInventoryObserver::class);
        ActivoMantenimiento::observe(ActivoMantenimientoObserver::class);
        SolicitudLimpieza::observe(SolicitudLimpiezaObserver::class);
        Habitacion::observe(HabitacionHistorialObserver::class);
        LimpiezaEjecucion::observe(LimpiezaEjecucionObserver::class);
        Espacio::observe(EspacioHistorialObserver::class);

        Gate::before(function ($user, $ability) {
            return $user instanceof User && $user->hasRole('super_admin') ? true : null;
        });

        // Registrar migraciones en subcarpetas de módulos
        if ($this->app->runningInConsole()) {
            $migrationsPath = database_path('migrations');
            $paths = glob($migrationsPath.DIRECTORY_SEPARATOR.'*', GLOB_ONLYDIR);
            $this->loadMigrationsFrom(array_merge([$migrationsPath], is_array($paths) ? $paths : []));
        }

        // Resolver nombres de políticas modularizadas en Repository/Policies
        Gate::guessPolicyNamesUsing(function (string $modelClass) {
            if (str_starts_with($modelClass, 'App\\Repository\\Models\\')) {
                return str_replace('App\\Repository\\Models\\', 'App\\Repository\\Policies\\', $modelClass).'Policy';
            }

            return 'App\\Policies\\'.class_basename($modelClass).'Policy';
        });

        RateLimiter::for('auth', function (Request $request): Limit {
            $rawEmail = $request->input('email');
            $email = is_string($rawEmail) ? $rawEmail : '';

            return Limit::perMinute(5)->by(strtolower($email).'|'.$request->ip());
        });

        RateLimiter::for('logout', fn (Request $request): Limit => Limit::perMinute(30)->by($request->ip()));
    }

    protected function configureDefaults(): void
    {
        date_default_timezone_set('America/Managua');
        config(['app.timezone' => 'America/Managua']);

        Date::use(CarbonImmutable::class);

        Model::preventLazyLoading(
            ! $this->app->isProduction(),
        );

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
        //
    }
    //
}
