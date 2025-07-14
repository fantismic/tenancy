<?php

namespace Fantismic\Tenancy\Facades;

use Illuminate\Support\Facades\Facade;

class TenantAdmin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tenantadmin';
    }
}
