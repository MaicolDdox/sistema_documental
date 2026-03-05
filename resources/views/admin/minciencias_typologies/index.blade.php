<x-app-layout>
    <x-slot name="header">Tipologías Minciencias</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Catálogos</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Tipologías Minciencias</span>
    </nav>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Tipologías Minciencias</h2>
        <p class="text-sm text-slate-500 mt-1">Tabla: tipologias_minciencias + subcategorias_minciencias</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Panel izquierdo: Tipologías --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Tipologías</h3>
                <a href="{{ route('admin.minciencias-typologies.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#39A900] hover:bg-[#2d8500] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    + Nueva
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Nombre</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Código</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Subcategorías</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($typologies as $typology)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 font-medium text-slate-900">{{ $typology->nombre }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $typology->codigo }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $typology->subcategories_count ?? 0 }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <a href="{{ route('admin.minciencias-typologies.edit', $typology) }}" class="p-1.5 inline-flex text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.minciencias-typologies.destroy', $typology) }}" class="inline" onsubmit="return confirm('¿Eliminar esta tipología?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 inline-flex text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-slate-500 text-sm">No hay tipologías.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Panel derecho: Subcategorías --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Subcategorías</h3>
                <a href="{{ route('admin.minciencias-subcategories.create') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#39A900] hover:bg-[#2d8500] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    + Nueva
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50">
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Nombre</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Tipología</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($subcategories as $sub)
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 font-medium text-slate-900">{{ $sub->nombre }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $sub->mincienciasTypology?->nombre ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <a href="{{ route('admin.minciencias-subcategories.edit', $sub) }}" class="p-1.5 inline-flex text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                                </a>
                                <form method="POST" action="{{ route('admin.minciencias-subcategories.destroy', $sub) }}" class="inline" onsubmit="return confirm('¿Eliminar esta subcategoría?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 inline-flex text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-500 text-sm">No hay subcategorías.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
