**Fantismic/Tenancy**

A Laravel package to build multi-tenant applications with separate databases per tenant on a single domain.

## Features

* **Tenant Provisioning**: Create tenant-specific databases and database users automatically.
* **Multi-Database Support**: Each tenant gets its own MySQL database connection.
* **Migrations & Seeders**: Run migrations and seeders per tenant.
* **Tenant Isolation**: Use the `UsesTenantConnection` trait to isolate Eloquent models to the tenant database.
* **Middleware**: Automatically initialize tenant context based on authenticated user.
* **Artisan Commands**: Manage tenant migrations via console command.
* **Admin Panel**: Out-of-the-box routes, controllers, and views for tenant administration (list, migrate, sync users, delete).

## Requirements

* PHP >= 8.1
* Laravel >= 10.0
* MySQL

## Installation

```bash
composer require fantismic/tenancy
```

## Service Provider & Facades

This package uses auto-discovery. The following service providers and facades are registered:

* **Service Providers**:

  * `Fantismic\Tenancy\TenancyServiceProvider`

* **Facades**:

  * `Tenancy` (alias for `Fantismic\Tenancy\Helpers\TenantManager`)
  * `TenantAdmin` (alias for `Fantismic\Tenancy\Services\TenantAdminService`)

## Database Migrations

Publish and run the central migrations:

```bash
php artisan vendor:publish --provider="Fantismic\Tenancy\TenancyServiceProvider" --tag="migrations"
php artisan migrate
```

This will create:

* `tenants` table
* `tenant_user` pivot table
* Tenant-specific migration stub directory at `database/migrations/tenant`

## Configuration

No configuration file is published. Tenant connection settings are stored in the `connection` JSON column of the `tenants` table. You can customize connection parameters when creating a tenant.

## Usage

### Creating a Tenant

Use the `Tenancy` facade to create a new tenant:

```php
use Fantismic\Tenancy\Facades\Tenancy;

$tenant = Tenancy::createTenant(
    'Tenant Name',            // Tenant display name
    [                         // Connection details for admin user
        'driver'         => 'mysql',
        'host'           => '127.0.0.1',
        'database'       => 'admin_db',
        'username'       => 'root',
        'password'       => 'secret',
    ],
    [                         // Options
        'create_db' => true,  // Create database & user (default: true)
        'migrate'   => true,  // Run tenant migrations (default: true)
        'seed'      => null,  // Seeder class to run (optional)
    ]
);
```

This will:

1. Generate a UUID for the tenant.
2. Create a database named `tenant_{uuid}` and a user `u_{uuid}` with random password.
3. Store the connection JSON in the `tenants` table.
4. Run tenant migrations in `database/migrations/tenant`.
5. Return the `Tenant` model instance.

### Artisan Command

Run migrations for an existing tenant:

```bash
php artisan tenants:migrate {tenant_id}
```

### Eloquent Models

Use the `UsesTenantConnection` trait on any model that should use the tenant database:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Fantismic\Tenancy\Traits\UsesTenantConnection;

class Invoice extends Model
{
    use UsesTenantConnection;
    // ...
}
```

### Middleware

Protect tenant routes and initialize tenant context:

```php
use Fantismic\Tenancy\Http\Middleware\InitializeTenant;

Route::middleware([
    'auth',
    InitializeTenant::class,
])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index']);
});
```

### Admin Panel

The package provides a simple administrative UI. Publish views:

```bash
php artisan vendor:publish --provider="Fantismic\Tenancy\TenancyServiceProvider" --tag="views"
```

Then visit `/admin/tenants` to manage tenants.

Supported actions:

* List tenants
* Migrate tenant database
* Sync users from central to tenant pivot
* Delete tenant and drop its database

## Facades & Services

* **`Tenancy`** (`TenantManager`): Tenant creation, migrations, and seeding.
* **`TenantAdmin`** (`TenantAdminService`): Fetch, migrate, sync users, and delete tenants.

## Events & Listeners

The package listens to the `Login` event to set the current tenant in session:

* `Fantismic\Tenancy\Listeners\SetTenantSession`

## Contributing

Feel free to submit issues or pull requests. Follow PSR-12 coding standards and include tests for new features.

## License

MIT License. See [LICENSE](LICENSE) for details.
