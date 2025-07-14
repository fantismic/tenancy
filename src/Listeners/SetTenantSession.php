<?php

namespace Fantismic\Tenancy\Listeners;

use Illuminate\Auth\Events\Login;
use Fantismic\Tenancy\Facades\Tenancy;
use Illuminate\Support\Facades\Cache;

class SetTenantSession
{
    public function handle(Login $event)
    {
        $user = $event->user;
        $tenant = $user->tenants()->first();
        // Obtené el tenant (podés usar initializeForUser para lógica propia)
        #$tenant = Tenancy::initializeForUser($user);
        
        if ($tenant) {
            session(['tenant_id' => $tenant->id]);
        }
        
    }
}
