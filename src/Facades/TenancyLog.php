<?php

namespace Fantismic\Tenancy\Facades;

use Illuminate\Support\Facades\Facade;
use Fantismic\Tenancy\Logging\TenancyLogger;

class TenancyLog extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TenancyLogger::class;
    }
}
