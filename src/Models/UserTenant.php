<?php

namespace Fantismic\Tenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Fantismic\Tenancy\Traits\UsesTenantConnection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class UserTenant extends Model
{
    use UsesTenantConnection; // Si tus IDs son uuid

    protected $table = 'users';          // La tabla del tenant

    protected $guarded = [];

    public $timestamps = true;
}
