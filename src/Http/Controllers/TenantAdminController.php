<?php
namespace Fantismic\Tenancy\Http\Controllers;

use Illuminate\Http\Request;
use Fantismic\Tenancy\Facades\TenantAdmin;

class TenantAdminController extends \Illuminate\Routing\Controller
{
    public function index()
    {
        $tenants = TenantAdmin::all();
        return view('tenancyadmin::admin.index', compact('tenants'));
    }

    public function migrate($id)
    {
        $tenant = TenantAdmin::find($id);
        TenantAdmin::migrate($tenant);
        return redirect()->back()->with('status', 'Migración ejecutada');
    }

    public function syncUsers($id)
    {
        $tenant = TenantAdmin::find($id);
        TenantAdmin::syncUsers($tenant);
        return redirect()->back()->with('status', 'Usuarios sincronizados');
    }

    public function destroy($id)
    {
        TenantAdmin::delete($id);
        return redirect()->back()->with('status', 'Tenant eliminado');
    }
}
