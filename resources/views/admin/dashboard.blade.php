<x-app-layout>
    <x-slot name="header">Dashboard Admin</x-slot>
<div class="mb-6">
    <h2 class="text-xl font-semibold text-slate-900">Bienvenido al Panel de Administración</h2>
    <p class="text-sm text-slate-500 mt-1">
        Gestiona los usuarios, permisos y catálogos de tu centro de formación.
    </p>
</div>

<!-- Tarjetas de resumen -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    
    <!-- Total Usuarios -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-green-50 flex items-center justify-center text-sgd-green">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Usuarios</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ $totalUsuarios ?? \App\Models\User::where('training_center_id', auth()->user()->training_center_id)->count() }}</h3>
        </div>
    </div>

    <!-- Total Roles -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-blue-50 flex items-center justify-center text-sgd-blue">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Roles</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ $totalRoles ?? \Spatie\Permission\Models\Role::count() }}</h3>
        </div>
    </div>

    <!-- Total Catálogos -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-lg bg-orange-50 flex items-center justify-center text-orange-500">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Catálogos</p>
            <h3 class="text-2xl font-bold text-slate-900">{{ $totalCatalogos ?? \App\Models\Catalogo::count() }}</h3>
        </div>
    </div>

</div>

<!-- Accesos Rápidos -->
<div class="bg-white rounded-xl border border-slate-200">
    <div class="px-5 py-4 border-b border-slate-100">
        <h3 class="text-sm font-semibold text-slate-900">Accesos Rápidos</h3>
        <p class="text-xs text-slate-400 mt-0.5">Accede a las principales secciones del sistema</p>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        
        @can('usuarios.listar')
        <a href="{{ route('admin.usuarios.index') }}" class="group block border border-slate-200 rounded-lg p-5 hover:border-sgd-green hover:shadow-sm transition-all text-center">
            <div class="w-10 h-10 mx-auto rounded-full bg-slate-50 group-hover:bg-green-50 flex items-center justify-center text-slate-400 group-hover:text-sgd-green transition-colors mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                </svg>
            </div>
            <h4 class="text-sm font-medium text-slate-800">Gestionar Usuarios</h4>
            <p class="text-xs text-slate-500 mt-1 line-clamp-2">Crea, edita y asociales roles a los usuarios.</p>
        </a>
        @endcan

        @can('catalogos.leer')
        <a href="{{ route('admin.catalogos.index') }}" class="group block border border-slate-200 rounded-lg p-5 hover:border-sgd-green hover:shadow-sm transition-all text-center">
            <div class="w-10 h-10 mx-auto rounded-full bg-slate-50 group-hover:bg-green-50 flex items-center justify-center text-slate-400 group-hover:text-sgd-green transition-colors mb-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
            </div>
            <h4 class="text-sm font-medium text-slate-800">Gestionar Catálogos</h4>
            <p class="text-xs text-slate-500 mt-1 line-clamp-2">Administra las tipologías y parámetros del sistema.</p>
        </a>
        @endcan

    </div>
</div>
</x-app-layout>
