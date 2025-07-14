<?php

namespace Fantismic\Tenancy\Console;

use Illuminate\Console\Command;
use Fantismic\Tenancy\Helpers\ConnectionHelper;
use Fantismic\Tenancy\Models\Tenant;
use Illuminate\Support\Facades\Artisan;

class MigrateTenantCommand extends Command
{
    protected $signature = 'tenants:migrate {tenant_id}';

    public function handle()
    {
        $tenant = Tenant::findOrFail($this->argument('tenant_id'));
        $connectionData = json_decode($tenant->connection, true);

        ConnectionHelper::setTenantConnection($connectionData);

        $this->info("Migrando tenant: {$tenant->name}");
        Artisan::call('migrate', [
            '--path' => 'packages/Tenancy/database/migrations/tenant',
            '--database' => 'tenant_temp',
            '--force' => true,
        ]);
        $this->info(Artisan::output());
    }
}
