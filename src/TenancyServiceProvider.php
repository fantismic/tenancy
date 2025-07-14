<?php

namespace Fantismic\Tenancy;

use Illuminate\Auth\Events\Login;
use Illuminate\Support\ServiceProvider;
use Fantismic\Tenancy\Http\Middleware\InitializeTenant;
use Livewire\Livewire;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Event;
use Fantismic\Tenancy\Listeners\SetTenantSession;


class TenancyServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        if ($this->app->runningInConsole()) {
            $this->commands([
                \Fantismic\Tenancy\Console\MigrateTenantCommand::class,
            ]);
        }
        $this->loadRoutesFrom(__DIR__.'/routes/tenancy_admin.php');
        $this->loadViewsFrom(__DIR__.'/resources/views', 'tenancyadmin');

        #php artisan vendor:publish --tag=tenancy-tenant-migrations
        $this->publishes([
            __DIR__.'/../database/migrations/tenant' => database_path('migrations/tenant'),
        ], 'tenancy-tenant-migrations');

        $this->publishes([
            __DIR__ . '/config/tenancy.php' => config_path('tenancy.php'),
        ], 'config');

        Livewire::setUpdateRoute(function ($handle) {
            return Route::post('/livewire/update', $handle)
                ->middleware([
                    'web',
                    InitializeTenant::class,
                ]);
        });

        Event::listen(Login::class, SetTenantSession::class);

        Validator::extend('unique_tenant', function ($attribute, $value, $parameters, $validator) {
            // Parámetros esperados: tabla, columna (opcional)
            $table = $parameters[0] ?? null;
            $column = $parameters[1] ?? $attribute;

            if (!$table) {
                throw new \InvalidArgumentException("La regla unique_tenant requiere el nombre de la tabla.");
            }

            $connection = 'tenant_temp';

            // Obtenemos la query builder para la tabla y conexión tenant
            $query = \DB::connection($connection)->table($table)->where($column, $value);

            // Si se pasa un tercer parámetro como ID para ignorar (ej. para update)
            if (!empty($parameters[2])) {
                $idToIgnore = $parameters[2];
                $idColumn = $parameters[3] ?? 'id';
                $query->where($idColumn, '!=', $idToIgnore);
            }

            return !$query->exists();
        });

        Validator::extend('exists_tenant', function ($attribute, $value, $parameters, $validator) {
            // Parámetros esperados: tabla, columna (opcional)
            $table = $parameters[0] ?? null;
            $column = $parameters[1] ?? $attribute;

            if (!$table) {
                throw new \InvalidArgumentException("La regla exists_tenant requiere el nombre de la tabla.");
            }

            $connection = 'tenant_temp';

            $query = \DB::connection($connection)->table($table)->where($column, $value);

            return $query->exists();
        });

    }

    public function register()
    {
        $this->app->singleton('tenancy', function () {
            return new \Fantismic\Tenancy\Helpers\TenantManager();
        });

        $this->app->singleton('tenantadmin', function () {
            return new \Fantismic\Tenancy\Services\TenantAdminService();
        });

        $this->app->singleton('tenancylog', function () {
            return new \Fantismic\Tenancy\Logging\TenancyLog();
        });
    }
}
