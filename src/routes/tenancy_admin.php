<?php

use Fantismic\Tenancy\Http\Controllers\TenantAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'is_admin'])->prefix('admin/tenants')->group(function() {
    Route::get('/', [TenantAdminController::class, 'index'])->name('tenancyadmin.index');
    Route::post('/{id}/migrate', [TenantAdminController::class, 'migrate'])->name('tenancyadmin.migrate');
    Route::post('/{id}/sync-users', [TenantAdminController::class, 'syncUsers'])->name('tenancyadmin.sync-users');
    Route::delete('/{id}', [TenantAdminController::class, 'destroy'])->name('tenancyadmin.destroy');
});
