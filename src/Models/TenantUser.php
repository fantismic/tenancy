<?php

namespace Fantismic\Tenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class TenantUser extends Model
{
    use HasUuids;
    
    protected $table = 'tenant_user';
    protected $fillable = [
        'tenant_id', 'user_id', 'role'
    ];
}
