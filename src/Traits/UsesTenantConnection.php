<?php

namespace Fantismic\Tenancy\Traits;

trait UsesTenantConnection
{
    public function getConnectionName()
    {
        return 'tenant_temp';
    }
}
