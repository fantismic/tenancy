<?php

namespace Fantismic\Tenancy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Tenant extends Model
{
    use HasUuids;
    
    protected $table = 'tenants';

    protected $fillable = [
        'name',
        'connection'
    ];
}
