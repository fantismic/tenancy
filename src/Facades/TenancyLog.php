<?php

namespace Fantismic\Tenancy\Facades;

use Illuminate\Support\Facades\Facade;

class TenancyLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'tenancylog';
    }
}
