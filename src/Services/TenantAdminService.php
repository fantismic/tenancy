<?php
namespace Fantismic\Tenancy\Services;

use Fantismic\Tenancy\Models\Tenant;
use Fantismic\Tenancy\Helpers\TenantManager;

class TenantAdminService
{
    protected $manager;

    public function __construct()
    {
        $this->manager = new TenantManager;
    }

    public function all()
    {
        return Tenant::all();
    }

    public function find($id)
    {
        return Tenant::find($id);
    }

    public function create($name, $connection, $options = [])
    {
        return $this->manager->createTenant($name, $connection, $options);
    }

    public function update($id, $data)
    {
        $tenant = Tenant::findOrFail($id);
        $tenant->update($data);
        return $tenant;
    }

    public function delete($id)
    {
        $tenant = Tenant::findOrFail($id);
        return $tenant->delete();
    }

    public function migrate($tenant)
    {
        $connection = json_decode($tenant->connection, true);
        $this->manager->runTenantMigrations($connection);
    }

    public function syncUsers($tenant)
    {
        $this->manager->syncAllUsersForTenant($tenant);
    }

    public function countUsers($tenant)
    {
        $connection = json_decode($tenant->connection, true);
        \Fantismic\Tenancy\Helpers\ConnectionHelper::setTenantConnection($connection);
        return \Fantismic\Tenancy\Models\UserTenant::count();
    }
}
