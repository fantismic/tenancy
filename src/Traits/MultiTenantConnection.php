<?php

namespace Fantismic\Tenancy\Traits;

use Fantismic\Tenancy\Facades\Tenancy;

trait MultiTenantConnection
{
    /**
     * Usa la conexión del tenant actual detectada por Tenancy::getCurrentTenant().
     */
    public static function onTenant()
    {
        // Si hay tenant, usá su conexión, si no, default a 'tenant'
        $tenant = Tenancy::getCurrentTenant();
        $connection = 'tenant_temp'; // Ajustá el atributo si tu modelo lo tiene con otro nombre
        return (new static)->setConnection($connection)->newQuery();
    }

    /**
     * Usar una conexión tenant custom (por nombre)
     */
    public static function onTenantConnection($connectionName)
    {
        return (new static)->setConnection($connectionName)->newQuery();
    }

    /**
     * Central (igual que antes)
     */
    public static function onCentral()
    {
        return (new static)->setConnection('mysql')->newQuery(); // O 'central' si usás ese alias
    }

    /**
     * Cambia la conexión de la instancia actual.
     * Permite fluidez en relaciones cargadas.
     */
    public function withTenantConnection($connection = 'tenant_temp')
    {
        $this->setConnection($connection);
        return $this;
    }

    public function withCentralConnection()
    {
        $this->setConnection('mysql');
        return $this;
    }
}
