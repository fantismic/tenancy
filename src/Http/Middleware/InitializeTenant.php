<?php

namespace Fantismic\Tenancy\Http\Middleware;

use Closure;
use Fantismic\Tenancy\Facades\Tenancy;
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

            // Si no hay tenant en sesión, o no pertenece al usuario, abortar
            if (!$tenantId || ! $user->tenants()->where('tenants.id', $tenantId)->exists()) {
                abort(403, 'Acceso denegado: tenant no autorizado.');
            }
            
            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant) {
                    Tenancy::initialize($tenant);
                }
            }
        }

        return $next($request);
    }
}
