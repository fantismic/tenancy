@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-gray-100">Administrar Tenants</h1>
    @if(session('status'))
        <div class="mb-4 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-100 px-4 py-2 rounded">
            {{ session('status') }}
        </div>
    @endif
    <div class="overflow-x-auto bg-white dark:bg-gray-900 rounded-xl shadow-md">
        <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
            <thead class="bg-gray-100 dark:bg-gray-800">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Nombre</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">UUID</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Base de Datos</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Usuarios</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Creado</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-800">
            @foreach($tenants as $tenant)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ $tenant->name }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">{{ $tenant->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                        {{ json_decode($tenant->connection, true)['database'] ?? '' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                        {{ Fantismic\Tenancy\Facades\TenantAdmin::countUsers($tenant) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                        {{ $tenant->created_at->format('Y-m-d H:i') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium flex gap-2 justify-end">
                        <form method="POST" action="{{ route('tenancyadmin.migrate', $tenant->id) }}">
                            @csrf
                            <button type="submit" class="inline-block px-3 py-1 rounded bg-indigo-600 text-white text-xs hover:bg-indigo-700 shadow">Migrar</button>
                        </form>
                        <form method="POST" action="{{ route('tenancyadmin.sync-users', $tenant->id) }}">
                            @csrf
                            <button type="submit" class="inline-block px-3 py-1 rounded bg-blue-600 text-white text-xs hover:bg-blue-700 shadow">Sync Usuarios</button>
                        </form>
                        <form method="POST" action="{{ route('tenancyadmin.destroy', $tenant->id) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-block px-3 py-1 rounded bg-red-600 text-white text-xs hover:bg-red-700 shadow" onclick="return confirm('¿Eliminar tenant?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
