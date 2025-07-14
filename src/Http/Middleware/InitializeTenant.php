<?php

namespace Fantismic\Tenancy\Http\Middleware;

use Closure;
use Exception;
use Fantismic\Tenancy\Facades\Tenancy;
use Fantismic\Tenancy\Facades\TenancyLog;
use Fantismic\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class InitializeTenant
{
    public function handle($request, Closure $next)
    {

        $user = Auth::user();
        if ($user) {
            $tenantId = session('tenant_id');

            if ($tenantId) {
                if (! $user->tenants()->where('tenants.id', $tenantId)->exists()) {
                    Throw new \Exception('Acceso denegado: tenant no autorizado.');
                }
                $tenant = Tenant::find($tenantId);
                if ($tenant) {
                    TenancyLog::info( __METHOD__ .' - Inicializando tenant: ' . $tenant->id);
                    Tenancy::initialize($tenant);
                }
            }
        }

        return $next($request);
    }
}
