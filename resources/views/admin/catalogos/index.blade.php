<x-app-layout>
    <x-slot name="header">Catálogos</x-slot>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-semibold text-slate-900">Catálogos del Sistema</h2>
        <p class="text-sm text-slate-500 mt-1">Gestiona las tipologías, parámetros y opciones del sistema.</p>
    </div>
    @can('catalogos.crear')
    <div>
        <a href="{{ route('admin.catalogos.create') }}" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nuevo catálogo
        </a>
    </div>
    @endcan
</div>

<!-- Filtros -->
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
    <form method="GET" action="{{ route('admin.catalogos.index') }}" class="flex flex-col sm:flex-row items-end gap-4">
        <div class="flex-1">
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Filtrar por Tipo</label>
            <select name="tipo" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                <option value="">Todos los tipos</option>
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo }}" {{ request('tipo') == $tipo ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $tipo)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                Filtrar
            </button>
            @if(request()->has('tipo') && request('tipo') != '')
            <a href="{{ route('admin.catalogos.index') }}" class="text-slate-500 hover:text-slate-700 p-2" title="Limpiar filtros">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </a>
            @endif
        </div>
    </form>
</div>

<!-- Tabla -->
<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-100 bg-slate-50">
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Descripción</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($catalogos as $catalogo)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3 text-slate-700 font-medium">
                        {{ ucfirst(str_replace('_', ' ', $catalogo->tipo)) }}
                    </td>
                    <td class="px-4 py-3 text-slate-800 font-semibold">
                        {{ $catalogo->nombre }}
                    </td>
                    <td class="px-4 py-3 text-slate-500 text-xs">
                        {{ $catalogo->descripcion ?? 'Sin descripción' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($catalogo->activo)
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div> Activo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-600">
                                <div class="w-1.5 h-1.5 rounded-full bg-slate-400"></div> Inactivo
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            
                            @can('catalogos.editar')
                            <a href="{{ route('admin.catalogos.edit', $catalogo->id) }}" class="text-slate-400 hover:text-sgd-green p-1 transition-colors" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            @endcan

                            @can('catalogos.eliminar')
                            <form method="POST" action="{{ route('admin.catalogos.destroy', $catalogo->id) }}" class="inline" onsubmit="return confirm('¿Estás seguro de que deseas eliminar este catálogo? Esta acción no se puede deshacer.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-slate-400 hover:text-red-500 p-1 transition-colors" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </form>
                            @endcan

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                        No se encontraron catálogos con los filtros actuales.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($catalogos->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $catalogos->links() }}
    </div>
    @endif
</div>
</x-app-layout>
