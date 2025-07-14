<?php

namespace Fantismic\Tenancy\Helpers;

use Exception;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Fantismic\Tenancy\Models\Tenant;
use Fantismic\Tenancy\Models\UserTenant;

/**
 * TenantManager handles tenant creation, database setup, and migrations.
 */

/*
use Fantismic\Tenancy\Facades\Tenancy;


$tenant = Tenancy::createTenant(
    'Demo Seed',
    [
        'driver'         => 'mysql',
        'host'           => 'localhost',
        'database'       => 'tenant_demo123',
        'admin_username' => 'root',      // Para crear la base/usuario
        'admin_password' => '',          // Lo ideal es root o un user con permisos de CREATE
    ],
    [
        'create_db' => true,
        'migrate' => true,
        'seed' => \Database\Seeders\NewTenantSeeder::class, // Opcional, puede ser null
    ]
);
*/

class TenantManager
{

    protected $defaultFields = [
        'name',
        'email',
        'avatar',
        // Agrega más campos según tu modelo User
    ];

    public function getCurrentTenant()
    {
        $tenantId = session('tenant_id');
        if (!$tenantId) {
            return null; // O lanzar excepción si preferís
        }

        return \Fantismic\Tenancy\Models\Tenant::find($tenantId);
    }

    public function initialize($tenant)
    {
        // Si pasan UUID, busca el modelo
        if (is_string($tenant)) {
            $tenant = \Fantismic\Tenancy\Models\Tenant::findOrFail($tenant);
        }
        // Setea la conexión dinámica (usa tu helper)
        $connection = json_decode($tenant->connection, true);
        \Fantismic\Tenancy\Helpers\ConnectionHelper::setTenantConnection($connection);

        // Guarda en sesión/contexto si querés
        session(['tenant_id' => $tenant->id]);
    }

    public function initializeForUser($user, $tenant_connection_data)
    {
        // Trae los tenants del usuario
        $tenants = $user->tenants ?? $user->tenants();
        if (is_callable($tenants)) $tenants = $tenants();

        $count = $tenants instanceof \Illuminate\Database\Eloquent\Relations\Relation
            ? $tenants->count()
            : (is_countable($tenants) ? count($tenants) : 0);

        if ($count === 1) {
            $tenant = $tenants->first();
            $this->initialize($tenant);
            return $tenant;
        } elseif ($count > 1) {
            // Opcional: elegí lógica de resolución
            throw new \Exception("El usuario pertenece a múltiples tenants. Selección manual requerida.");
        } else {
            $tenant = $this->createTenant($user->name . ' Tenant',$tenant_connection_data);
            $user->tenants()->attach($tenant->id);
            $this->syncUserToTenant($user, $tenant, ['name', 'email', 'avatar']);
            $this->initialize($tenant);
            return $tenant;
        }
    }

    public function syncUserToTenant(User $centralUser, Tenant $tenant, $fields = null)
    {
        $fields = $fields ?? $this->defaultFields;
        $tenantConnection = json_decode($tenant->connection, true);
        \Fantismic\Tenancy\Helpers\ConnectionHelper::setTenantConnection($tenantConnection);

        $data = [];
        foreach ($fields as $field) {
            if (isset($centralUser->$field)) {
                $data[$field] = $centralUser->$field;
            }
        }
        $data['updated_at'] = now();

        UserTenant::updateOrCreate(
            ['id' => $centralUser->id],
            $data
        );
    }

    /**
     * Sincroniza todos los usuarios de la base central a la tabla users del tenant.
     */
    public function syncAllUsersForTenant(Tenant $tenant, $fields = null)
    {
        $fields = $fields ?? $this->defaultFields;
        $users = User::all();
        foreach ($users as $user) {
            $this->syncUserToTenant($user, $tenant, $fields);
        }
    }

    public function createTenant($name, $connection, $options = [])
    {
        $createDb    = $options['create_db'] ?? true;
        $runMigrate  = $options['migrate'] ?? true;
        $seederClass = $options['seed'] ?? null;

        // 1. Genera el UUID antes de crear el tenant
        $uuid = (string) Str::uuid();

        // 2. Define los nombres cumpliendo los límites MySQL
        $dbName = 'tenant_' . $uuid;
        // User: "u_" + primeros 16 caracteres del uuid sin guiones, ej: u_b25c3fa08a7d42c7 (18 chars)
        $userShort = substr(str_replace('-', '', $uuid), 0, 16);
        $tenantDbUser = 'u_' . $userShort;
        $tenantDbPass = Str::random(20);

        // 3. Crea la base y usuario, si corresponde
        if ($createDb) {
            $this->createDatabaseAndUser(
                $connection,
                $dbName,
                $tenantDbUser,
                $tenantDbPass
            );
        }

        // 4. Arma la conexión final que guardará el tenant
        $finalConnection = $connection;
        $finalConnection['database'] = $dbName;
        $finalConnection['username'] = $tenantDbUser;
        $finalConnection['password'] = $tenantDbPass;

        // 5. Crea el registro central (usando el UUID que ya tenés)
/*         $tenant = new Tenant([
            'id' => $uuid,
            'name' => $name,
            'connection' => json_encode($finalConnection)
        ]);
        $tenant->save(); */

        $tenant = new Tenant;
        $tenant->id = $uuid;
        $tenant->name = $name;
        $tenant->connection = json_encode($finalConnection);
        $tenant->save();

        // 6. Migraciones y seed, usando el connection final
        if ($runMigrate) {
            $this->runTenantMigrations($finalConnection);
        }
        if ($seederClass) {
            $this->runTenantSeeder($finalConnection, $seederClass);
        }

        return $tenant;
    }

    protected function createDatabaseAndUser($adminConn, $dbName, $tenantDbUser, $tenantDbPass)
    {
        $adminConnection = [
            'driver'   => $adminConn['driver'] ?? 'mysql',
            'host'     => $adminConn['host'],
            'port'     => $adminConn['port'] ?? 3306,
            'username' => $adminConn['admin_username'] ?? 'root',
            'password' => $adminConn['admin_password'] ?? '',
            'database' => null,
            'charset'  => 'utf8mb4',
            'collation'=> 'utf8mb4_unicode_ci',
        ];
        config(['database.connections.tenant_temp_admin' => $adminConnection]);
        DB::purge('tenant_temp_admin');

        try {
            // Crea la base de datos
            $sqlDb = "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
            DB::connection('tenant_temp_admin')->statement($sqlDb);

            // Crea el usuario
            $sqlUser = "CREATE USER IF NOT EXISTS '{$tenantDbUser}'@'%' IDENTIFIED BY '{$tenantDbPass}'";
            DB::connection('tenant_temp_admin')->statement($sqlUser);

            // Da privilegios solo sobre la base creada
            $sqlGrant = "GRANT ALL PRIVILEGES ON `{$dbName}`.* TO '{$tenantDbUser}'@'%'";
            DB::connection('tenant_temp_admin')->statement($sqlGrant);
        } catch (Exception $e) {
            throw new Exception("No se pudo crear la base de datos o el usuario del tenant: " . $e->getMessage());
        }
    }

    protected function runTenantMigrations($connection)
    {
        \Fantismic\Tenancy\Helpers\ConnectionHelper::setTenantConnection($connection);

        // Ejecutar todas las migraciones tenant del proyecto
        \Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--database' => 'tenant_temp',
            '--force' => true,
        ]);
    }

    protected function runTenantSeeder($connection, $seederClass)
    {
        \Fantismic\Tenancy\Helpers\ConnectionHelper::setTenantConnection($connection);
        \Artisan::call('db:seed', [
            '--class' => $seederClass,
            '--database' => 'tenant_temp',
            '--force' => true,
        ]);
    }

}