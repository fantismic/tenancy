<?php

namespace Fantismic\Tenancy\Helpers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class ConnectionHelper
{
    public static function setTenantConnection(array $connectionData, $connectionName = 'tenant_temp')
    {
        Config::set("database.connections.{$connectionName}", [
            'driver'    => $connectionData['driver'] ?? 'mysql',
            'host'      => $connectionData['host'],
            'port'      => $connectionData['port'] ?? 3306,
            'database'  => $connectionData['database'],
            'username'  => $connectionData['username'],
            'password'  => $connectionData['password'],
            'charset'   => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
        ]);
        DB::purge($connectionName);
        DB::reconnect($connectionName);
    }
}
