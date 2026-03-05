<x-app-layout>
    <x-slot name="header">Catálogos</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-700">Catálogos</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Catálogos Simples</span>
    </nav>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Catálogos del Sistema</h2>
        <p class="text-sm text-slate-500 mt-1">Tablas de soporte: modalidades, tipos, cargos, vinculaciones.....</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        {{-- Cargos en Entidad --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Cargos en Entidad</h3>
                @can('catalogos.crear')
                <a href="{{ route('admin.entity-positions.create') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </a>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $entityPositions->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($entityPositions as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="flex items-center gap-0.5 shrink-0">
                            @can('catalogos.editar')
                            <a href="{{ route('admin.entity-positions.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                            </a>
                            @endcan
                            @can('catalogos.eliminar')
                            <form method="POST" action="{{ route('admin.entity-positions.destroy', $item) }}" class="inline" onsubmit="return confirm('¿Eliminar este registro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </li>
                    @empty
                    <li class="text-sm text-slate-500 py-2">Sin registros</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Tipos de Vinculación --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Tipos de Vinculación</h3>
                @can('catalogos.crear')
                <a href="{{ route('admin.linkage-types.create') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </a>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $linkageTypes->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($linkageTypes as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="flex items-center gap-0.5 shrink-0">
                            @can('catalogos.editar')
                            <a href="{{ route('admin.linkage-types.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                            </a>
                            @endcan
                            @can('catalogos.eliminar')
                            <form method="POST" action="{{ route('admin.linkage-types.destroy', $item) }}" class="inline" onsubmit="return confirm('¿Eliminar este registro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </li>
                    @empty
                    <li class="text-sm text-slate-500 py-2">Sin registros</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Modalidades Proyectos --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Modalidades Proyectos</h3>
                @can('catalogos.crear')
                <a href="{{ route('admin.project-modalities.create') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </a>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $projectModalities->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($projectModalities as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="flex items-center gap-0.5 shrink-0">
                            @can('catalogos.editar')
                            <a href="{{ route('admin.project-modalities.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                            </a>
                            @endcan
                            @can('catalogos.eliminar')
                            <form method="POST" action="{{ route('admin.project-modalities.destroy', $item) }}" class="inline" onsubmit="return confirm('¿Eliminar este registro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </li>
                    @empty
                    <li class="text-sm text-slate-500 py-2">Sin registros</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Tipos de Investigación --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Tipos de Investigación</h3>
                @can('catalogos.crear')
                <a href="{{ route('admin.investigation-types.create') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </a>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $investigationTypes->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($investigationTypes as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="flex items-center gap-0.5 shrink-0">
                            @can('catalogos.editar')
                            <a href="{{ route('admin.investigation-types.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                            </a>
                            @endcan
                            @can('catalogos.eliminar')
                            <form method="POST" action="{{ route('admin.investigation-types.destroy', $item) }}" class="inline" onsubmit="return confirm('¿Eliminar este registro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </li>
                    @empty
                    <li class="text-sm text-slate-500 py-2">Sin registros</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Líneas Tecnológicas --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Líneas Tecnológicas</h3>
                @can('catalogos.crear')
                <a href="{{ route('admin.technological-lines.create') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </a>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $technologicalLines->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($technologicalLines as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="flex items-center gap-0.5 shrink-0">
                            @can('catalogos.editar')
                            <a href="{{ route('admin.technological-lines.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                            </a>
                            @endcan
                            @can('catalogos.eliminar')
                            <form method="POST" action="{{ route('admin.technological-lines.destroy', $item) }}" class="inline" onsubmit="return confirm('¿Eliminar este registro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </li>
                    @empty
                    <li class="text-sm text-slate-500 py-2">Sin registros</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Áreas Temáticas --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Áreas Temáticas</h3>
                @can('catalogos.crear')
                <a href="{{ route('admin.thematic-areas.create') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </a>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $thematicAreas->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($thematicAreas as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="flex items-center gap-0.5 shrink-0">
                            @can('catalogos.editar')
                            <a href="{{ route('admin.thematic-areas.edit', $item) }}" class="p-1.5 text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                            </a>
                            @endcan
                            @can('catalogos.eliminar')
                            <form method="POST" action="{{ route('admin.thematic-areas.destroy', $item) }}" class="inline" onsubmit="return confirm('¿Eliminar este registro?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded" title="Eliminar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </li>
                    @empty
                    <li class="text-sm text-slate-500 py-2">Sin registros</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>
