<?php

namespace Fantismic\Tenancy\Logging;

class TenancyLogger
{
    public static function info($message, array $context = [])
    {
        if (config('tenancy.log_enabled')) {
            \Log::info('[Tenancy] ' . $message, $context);
        }
    }

    public static function error($message, array $context = [])
    {
        if (config('tenancy.log_enabled')) {
            \Log::error('[Tenancy] ' . $message, $context);
        }
    }

    public static function debug($message, array $context = [])
    {
        if (config('tenancy.log_enabled')) {
            \Log::debug('[Tenancy] ' . $message, $context);
        }
    }

    // Podés agregar más niveles si querés
}
