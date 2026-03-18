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

    <div x-data="catalogosSimples()">
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        {{-- Cargos en Entidad --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Cargos en Entidad</h3>
                @can('catalogos.crear')
                <button type="button" @click="modalCargo = true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </button>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $entityPositions->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($entityPositions as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0" data-id="{{ $item->id }}" data-nombre="{{ e($item->nombre) }}" data-descripccion="{{ e($item->descripccion ?? '') }}">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="relative shrink-0" x-data="{ open: false }">
                            <button type="button"
                                    @click.stop="open = !open"
                                    @keydown.escape.window="open = false"
                                    class="inline-flex items-center justify-center rounded-full p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                    aria-haspopup="true"
                                    :aria-expanded="open ? 'true' : 'false'">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </button>
                            <div x-show="open"
                                 x-cloak
                                 @click.away="open = false"
                                 class="absolute right-0 mt-1 w-44 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                <button type="button"
                                        @click="open = false; openDetalle($event.target.closest('li'), 'Cargo en entidad')"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Ver detalle</span>
                                </button>
                                @can('catalogos.editar')
                                <button type="button"
                                        @click="open = false; openEditCargoFromEl($event.target.closest('li'))"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                                    <span>Editar</span>
                                </button>
                                @endcan
                                @can('catalogos.eliminar')
                                <form method="POST" action="{{ route('admin.entity-positions.destroy', $item) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            @click="open = false; if (window.confirm('¿Eliminar este registro?')) { $el.closest('form').submit(); }"
                                            class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        <span>Eliminar</span>
                                    </button>
                                </form>
                                @endcan
                            </div>
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
                <button type="button" @click="modalVinculacion = true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </button>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $linkageTypes->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($linkageTypes as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0" data-id="{{ $item->id }}" data-nombre="{{ e($item->nombre) }}" data-descripccion="{{ e($item->descripccion ?? '') }}">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="relative shrink-0" x-data="{ open: false }">
                            <button type="button"
                                    @click.stop="open = !open"
                                    @keydown.escape.window="open = false"
                                    class="inline-flex items-center justify-center rounded-full p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                    aria-haspopup="true"
                                    :aria-expanded="open ? 'true' : 'false'">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </button>
                            <div x-show="open"
                                 x-cloak
                                 @click.away="open = false"
                                 class="absolute right-0 mt-1 w-44 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                <button type="button"
                                        @click="open = false; openDetalle($event.target.closest('li'), 'Tipo de vinculación')"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Ver detalle</span>
                                </button>
                                @can('catalogos.editar')
                                <button type="button"
                                        @click="open = false; openEditVinculacionFromEl($event.target.closest('li'))"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                                    <span>Editar</span>
                                </button>
                                @endcan
                                @can('catalogos.eliminar')
                                <form method="POST" action="{{ route('admin.linkage-types.destroy', $item) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            @click="open = false; if (window.confirm('¿Eliminar este registro?')) { $el.closest('form').submit(); }"
                                            class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        <span>Eliminar</span>
                                    </button>
                                </form>
                                @endcan
                            </div>
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
                <button type="button" @click="modalModalidad = true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </button>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $projectModalities->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($projectModalities as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0" data-id="{{ $item->id }}" data-nombre="{{ e($item->nombre) }}" data-descripccion="{{ e($item->descripccion ?? '') }}">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="relative shrink-0" x-data="{ open: false }">
                            <button type="button"
                                    @click.stop="open = !open"
                                    @keydown.escape.window="open = false"
                                    class="inline-flex items-center justify-center rounded-full p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                    aria-haspopup="true"
                                    :aria-expanded="open ? 'true' : 'false'">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </button>
                            <div x-show="open"
                                 x-cloak
                                 @click.away="open = false"
                                 class="absolute right-0 mt-1 w-44 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                <button type="button"
                                        @click="open = false; openDetalle($event.target.closest('li'), 'Modalidad de proyecto')"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Ver detalle</span>
                                </button>
                                @can('catalogos.editar')
                                <button type="button"
                                        @click="open = false; openEditModalidadFromEl($event.target.closest('li'))"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                                    <span>Editar</span>
                                </button>
                                @endcan
                                @can('catalogos.eliminar')
                                <form method="POST" action="{{ route('admin.project-modalities.destroy', $item) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            @click="open = false; if (window.confirm('¿Eliminar este registro?')) { $el.closest('form').submit(); }"
                                            class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        <span>Eliminar</span>
                                    </button>
                                </form>
                                @endcan
                            </div>
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
                <button type="button" @click="modalInvestigacion = true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </button>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $investigationTypes->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($investigationTypes as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0" data-id="{{ $item->id }}" data-nombre="{{ e($item->nombre) }}" data-descripccion="{{ e($item->descripccion ?? '') }}">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="relative shrink-0" x-data="{ open: false }">
                            <button type="button"
                                    @click.stop="open = !open"
                                    @keydown.escape.window="open = false"
                                    class="inline-flex items-center justify-center rounded-full p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                    aria-haspopup="true"
                                    :aria-expanded="open ? 'true' : 'false'">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </button>
                            <div x-show="open"
                                 x-cloak
                                 @click.away="open = false"
                                 class="absolute right-0 mt-1 w-44 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                <button type="button"
                                        @click="open = false; openDetalle($event.target.closest('li'), 'Tipo de investigación')"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Ver detalle</span>
                                </button>
                                @can('catalogos.editar')
                                <button type="button"
                                        @click="open = false; openEditInvestigacionFromEl($event.target.closest('li'))"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                                    <span>Editar</span>
                                </button>
                                @endcan
                                @can('catalogos.eliminar')
                                <form method="POST" action="{{ route('admin.investigation-types.destroy', $item) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            @click="open = false; if (window.confirm('¿Eliminar este registro?')) { $el.closest('form').submit(); }"
                                            class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        <span>Eliminar</span>
                                    </button>
                                </form>
                                @endcan
                            </div>
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
                <button type="button" @click="modalLinea = true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </button>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $technologicalLines->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($technologicalLines as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0" data-id="{{ $item->id }}" data-nombre="{{ e($item->nombre) }}" data-descripccion="{{ e($item->descripccion ?? '') }}">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="relative shrink-0" x-data="{ open: false }">
                            <button type="button"
                                    @click.stop="open = !open"
                                    @keydown.escape.window="open = false"
                                    class="inline-flex items-center justify-center rounded-full p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                    aria-haspopup="true"
                                    :aria-expanded="open ? 'true' : 'false'">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </button>
                            <div x-show="open"
                                 x-cloak
                                 @click.away="open = false"
                                 class="absolute right-0 mt-1 w-44 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                <button type="button"
                                        @click="open = false; openDetalle($event.target.closest('li'), 'Línea tecnológica')"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Ver detalle</span>
                                </button>
                                @can('catalogos.editar')
                                <button type="button"
                                        @click="open = false; openEditLineaFromEl($event.target.closest('li'))"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                                    <span>Editar</span>
                                </button>
                                @endcan
                                @can('catalogos.eliminar')
                                <form method="POST" action="{{ route('admin.technological-lines.destroy', $item) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            @click="open = false; if (window.confirm('¿Eliminar este registro?')) { $el.closest('form').submit(); }"
                                            class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        <span>Eliminar</span>
                                    </button>
                                </form>
                                @endcan
                            </div>
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
                <button type="button" @click="modalTematica = true" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-white bg-[#39A900] hover:bg-[#2d8500] transition-all" title="Nuevo">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </button>
                @endcan
            </div>
            <div class="px-4 py-3">
                <p class="text-xs text-slate-500 mb-3">{{ $thematicAreas->count() }} registros</p>
                <ul class="space-y-2">
                    @forelse($thematicAreas as $item)
                    <li class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0" data-id="{{ $item->id }}" data-nombre="{{ e($item->nombre) }}" data-descripccion="{{ e($item->descripccion ?? '') }}">
                        <span class="text-sm text-slate-800">{{ $item->nombre }}</span>
                        <div class="relative shrink-0" x-data="{ open: false }">
                            <button type="button"
                                    @click.stop="open = !open"
                                    @keydown.escape.window="open = false"
                                    class="inline-flex items-center justify-center rounded-full p-1.5 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                    aria-haspopup="true"
                                    :aria-expanded="open ? 'true' : 'false'">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </button>
                            <div x-show="open"
                                 x-cloak
                                 @click.away="open = false"
                                 class="absolute right-0 mt-1 w-44 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                <button type="button"
                                        @click="open = false; openDetalle($event.target.closest('li'), 'Área temática')"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Ver detalle</span>
                                </button>
                                @can('catalogos.editar')
                                <button type="button"
                                        @click="open = false; openEditTematicaFromEl($event.target.closest('li'))"
                                        class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487z" /></svg>
                                    <span>Editar</span>
                                </button>
                                @endcan
                                @can('catalogos.eliminar')
                                <form method="POST" action="{{ route('admin.thematic-areas.destroy', $item) }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            @click="open = false; if (window.confirm('¿Eliminar este registro?')) { $el.closest('form').submit(); }"
                                            class="w-full flex items-center gap-2 px-3 py-1.5 text-left text-xs text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        <span>Eliminar</span>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </li>
                    @empty
                    <li class="text-sm text-slate-500 py-2">Sin registros</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>

    {{-- Modales de registro (solo crear) — dentro del mismo x-data para que Alpine los controle --}}
    @can('catalogos.crear')
    {{-- Modal Cargos en Entidad --}}
    <div x-show="modalCargo" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalCargo" @click.self="modalCargo = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalCargo" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Nuevo cargo en entidad</h3>
                <form method="POST" action="{{ route('admin.entity-positions.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_from_simples" value="1">
                    <input type="hidden" name="_form_type" value="entity_position">
                    <div>
                        <label for="cargo_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="cargo_nombre" value="{{ old('nombre') }}" required class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="cargo_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="cargo_descripccion" value="{{ old('descripccion') }}" required class="w-full border @error('descripccion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('descripccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalCargo = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Tipos de Vinculación --}}
    <div x-show="modalVinculacion" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalVinculacion" @click.self="modalVinculacion = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalVinculacion" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Nuevo tipo de vinculación</h3>
                <form method="POST" action="{{ route('admin.linkage-types.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_from_simples" value="1">
                    <input type="hidden" name="_form_type" value="linkage_type">
                    <div>
                        <label for="vinculacion_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="vinculacion_nombre" value="{{ old('nombre') }}" required class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="vinculacion_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="vinculacion_descripccion" value="{{ old('descripccion') }}" required class="w-full border @error('descripccion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('descripccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalVinculacion = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Modalidades Proyectos --}}
    <div x-show="modalModalidad" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalModalidad" @click.self="modalModalidad = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalModalidad" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Nueva modalidad de proyecto</h3>
                <form method="POST" action="{{ route('admin.project-modalities.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_from_simples" value="1">
                    <input type="hidden" name="_form_type" value="project_modality">
                    <div>
                        <label for="modalidad_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="modalidad_nombre" value="{{ old('nombre') }}" required class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modalidad_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="modalidad_descripccion" value="{{ old('descripccion') }}" required class="w-full border @error('descripccion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('descripccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalModalidad = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Tipos de Investigación --}}
    <div x-show="modalInvestigacion" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalInvestigacion" @click.self="modalInvestigacion = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalInvestigacion" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Nuevo tipo de investigación</h3>
                <form method="POST" action="{{ route('admin.investigation-types.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_from_simples" value="1">
                    <input type="hidden" name="_form_type" value="investigation_type">
                    <div>
                        <label for="investigacion_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="investigacion_nombre" value="{{ old('nombre') }}" required class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="investigacion_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="investigacion_descripccion" value="{{ old('descripccion') }}" required class="w-full border @error('descripccion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('descripccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalInvestigacion = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Líneas Tecnológicas --}}
    <div x-show="modalLinea" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalLinea" @click.self="modalLinea = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalLinea" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Nueva línea tecnológica</h3>
                <form method="POST" action="{{ route('admin.technological-lines.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_from_simples" value="1">
                    <input type="hidden" name="_form_type" value="technological_line">
                    <div>
                        <label for="linea_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="linea_nombre" value="{{ old('nombre') }}" required class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="linea_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="linea_descripccion" value="{{ old('descripccion') }}" required class="w-full border @error('descripccion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('descripccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalLinea = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Áreas Temáticas --}}
    <div x-show="modalTematica" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalTematica" @click.self="modalTematica = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalTematica" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Nueva área temática</h3>
                <form method="POST" action="{{ route('admin.thematic-areas.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_from_simples" value="1">
                    <input type="hidden" name="_form_type" value="thematic_area">
                    <div>
                        <label for="tematica_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="tematica_nombre" value="{{ old('nombre') }}" required class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="tematica_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="tematica_descripccion" value="{{ old('descripccion') }}" required class="w-full border @error('descripccion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('descripccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalTematica = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    {{-- Modal Ver detalle (genérico para todos los catálogos) --}}
    <div x-show="modalDetalle" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalDetalle" @click.self="modalDetalle = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalDetalle" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4" x-text="detalleItem.tipo || 'Detalle'"></h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-slate-500 font-medium">Nombre</dt>
                        <dd class="text-slate-900 mt-0.5" x-text="detalleItem.nombre || '—'"></dd>
                    </div>
                    <div>
                        <dt class="text-slate-500 font-medium">Descripción</dt>
                        <dd class="text-slate-900 mt-0.5" x-text="detalleItem.descripccion || '—'"></dd>
                    </div>
                </dl>
                <div class="mt-6 pt-4 border-t border-slate-100">
                    <button type="button" @click="modalDetalle = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modales de edición --}}
    @can('catalogos.editar')
    {{-- Modal Editar Cargo --}}
    <div x-show="modalEditCargo" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditCargo" @click.self="modalEditCargo = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalEditCargo" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar cargo en entidad</h3>
                <form :action="'{{ url('admin/entity-positions') }}/' + editCargo.id" method="POST" class="space-y-4" x-show="editCargo.id">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_from_simples" value="1">
                    <div>
                        <label for="edit_cargo_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="edit_cargo_nombre" x-model="editCargo.nombre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div>
                        <label for="edit_cargo_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="edit_cargo_descripccion" x-model="editCargo.descripccion" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalEditCargo = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Editar Vinculación --}}
    <div x-show="modalEditVinculacion" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditVinculacion" @click.self="modalEditVinculacion = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalEditVinculacion" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar tipo de vinculación</h3>
                <form :action="'{{ url('admin/linkage-types') }}/' + editVinculacion.id" method="POST" class="space-y-4" x-show="editVinculacion.id">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_from_simples" value="1">
                    <div>
                        <label for="edit_vinculacion_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="edit_vinculacion_nombre" x-model="editVinculacion.nombre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div>
                        <label for="edit_vinculacion_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="edit_vinculacion_descripccion" x-model="editVinculacion.descripccion" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalEditVinculacion = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Editar Modalidad --}}
    <div x-show="modalEditModalidad" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditModalidad" @click.self="modalEditModalidad = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalEditModalidad" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar modalidad de proyecto</h3>
                <form :action="'{{ url('admin/project-modalities') }}/' + editModalidad.id" method="POST" class="space-y-4" x-show="editModalidad.id">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_from_simples" value="1">
                    <div>
                        <label for="edit_modalidad_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="edit_modalidad_nombre" x-model="editModalidad.nombre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div>
                        <label for="edit_modalidad_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="edit_modalidad_descripccion" x-model="editModalidad.descripccion" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalEditModalidad = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Editar Investigación --}}
    <div x-show="modalEditInvestigacion" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditInvestigacion" @click.self="modalEditInvestigacion = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalEditInvestigacion" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar tipo de investigación</h3>
                <form :action="'{{ url('admin/investigation-types') }}/' + editInvestigacion.id" method="POST" class="space-y-4" x-show="editInvestigacion.id">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_from_simples" value="1">
                    <div>
                        <label for="edit_investigacion_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="edit_investigacion_nombre" x-model="editInvestigacion.nombre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div>
                        <label for="edit_investigacion_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="edit_investigacion_descripccion" x-model="editInvestigacion.descripccion" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalEditInvestigacion = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Editar Línea --}}
    <div x-show="modalEditLinea" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditLinea" @click.self="modalEditLinea = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalEditLinea" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar línea tecnológica</h3>
                <form :action="'{{ url('admin/technological-lines') }}/' + editLinea.id" method="POST" class="space-y-4" x-show="editLinea.id">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_from_simples" value="1">
                    <div>
                        <label for="edit_linea_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="edit_linea_nombre" x-model="editLinea.nombre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div>
                        <label for="edit_linea_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="edit_linea_descripccion" x-model="editLinea.descripccion" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalEditLinea = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Modal Editar Temática --}}
    <div x-show="modalEditTematica" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditTematica" @click.self="modalEditTematica = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalEditTematica" class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar área temática</h3>
                <form :action="'{{ url('admin/thematic-areas') }}/' + editTematica.id" method="POST" class="space-y-4" x-show="editTematica.id">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="_from_simples" value="1">
                    <div>
                        <label for="edit_tematica_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="edit_tematica_nombre" x-model="editTematica.nombre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div>
                        <label for="edit_tematica_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción <span class="text-red-500">*</span></label>
                        <input type="text" name="descripccion" id="edit_tematica_descripccion" x-model="editTematica.descripccion" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalEditTematica = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endcan

    <script>
        function catalogosSimples() {
            return {
                modalCargo: @json($errors->any() && old('_form_type') === 'entity_position'),
                modalVinculacion: @json($errors->any() && old('_form_type') === 'linkage_type'),
                modalModalidad: @json($errors->any() && old('_form_type') === 'project_modality'),
                modalInvestigacion: @json($errors->any() && old('_form_type') === 'investigation_type'),
                modalLinea: @json($errors->any() && old('_form_type') === 'technological_line'),
                modalTematica: @json($errors->any() && old('_form_type') === 'thematic_area'),
                modalDetalle: false,
                detalleItem: { tipo: '', nombre: '', descripccion: '' },
                modalEditCargo: false,
                modalEditVinculacion: false,
                modalEditModalidad: false,
                modalEditInvestigacion: false,
                modalEditLinea: false,
                modalEditTematica: false,
                editCargo: { id: null, nombre: '', descripccion: '' },
                editVinculacion: { id: null, nombre: '', descripccion: '' },
                editModalidad: { id: null, nombre: '', descripccion: '' },
                editInvestigacion: { id: null, nombre: '', descripccion: '' },
                editLinea: { id: null, nombre: '', descripccion: '' },
                editTematica: { id: null, nombre: '', descripccion: '' },
                openDetalle(li, tipo) {
                    if (!li || !li.dataset) return;
                    this.detalleItem = {
                        tipo: tipo || '',
                        nombre: (li.dataset.nombre || '').replace(/&quot;/g, '"'),
                        descripccion: (li.dataset.descripccion || '').replace(/&quot;/g, '"')
                    };
                    this.modalDetalle = true;
                },
                openEditCargo(id, nombre, descripccion) {
                    this.editCargo = { id: id ? Number(id) : null, nombre: nombre || '', descripccion: descripccion || '' };
                    this.modalEditCargo = true;
                },
                openEditCargoFromEl(li) {
                    if (!li || !li.dataset) return;
                    const nombre = (li.dataset.nombre || '').replace(/&quot;/g, '"');
                    const descripccion = (li.dataset.descripccion || '').replace(/&quot;/g, '"');
                    this.openEditCargo(li.dataset.id, nombre, descripccion);
                },
                openEditVinculacion(id, nombre, descripccion) {
                    this.editVinculacion = { id, nombre: nombre || '', descripccion: descripccion || '' };
                    this.modalEditVinculacion = true;
                },
                openEditVinculacionFromEl(li) {
                    if (!li || !li.dataset) return;
                    this.openEditVinculacion(li.dataset.id, (li.dataset.nombre || '').replace(/&quot;/g, '"'), (li.dataset.descripccion || '').replace(/&quot;/g, '"'));
                },
                openEditModalidad(id, nombre, descripccion) {
                    this.editModalidad = { id, nombre: nombre || '', descripccion: descripccion || '' };
                    this.modalEditModalidad = true;
                },
                openEditModalidadFromEl(li) {
                    if (!li || !li.dataset) return;
                    this.openEditModalidad(li.dataset.id, (li.dataset.nombre || '').replace(/&quot;/g, '"'), (li.dataset.descripccion || '').replace(/&quot;/g, '"'));
                },
                openEditInvestigacion(id, nombre, descripccion) {
                    this.editInvestigacion = { id, nombre: nombre || '', descripccion: descripccion || '' };
                    this.modalEditInvestigacion = true;
                },
                openEditInvestigacionFromEl(li) {
                    if (!li || !li.dataset) return;
                    this.openEditInvestigacion(li.dataset.id, (li.dataset.nombre || '').replace(/&quot;/g, '"'), (li.dataset.descripccion || '').replace(/&quot;/g, '"'));
                },
                openEditLinea(id, nombre, descripccion) {
                    this.editLinea = { id, nombre: nombre || '', descripccion: descripccion || '' };
                    this.modalEditLinea = true;
                },
                openEditLineaFromEl(li) {
                    if (!li || !li.dataset) return;
                    this.openEditLinea(li.dataset.id, (li.dataset.nombre || '').replace(/&quot;/g, '"'), (li.dataset.descripccion || '').replace(/&quot;/g, '"'));
                },
                openEditTematica(id, nombre, descripccion) {
                    this.editTematica = { id, nombre: nombre || '', descripccion: descripccion || '' };
                    this.modalEditTematica = true;
                },
                openEditTematicaFromEl(li) {
                    if (!li || !li.dataset) return;
                    this.openEditTematica(li.dataset.id, (li.dataset.nombre || '').replace(/&quot;/g, '"'), (li.dataset.descripccion || '').replace(/&quot;/g, '"'));
                }
            };
        }
    </script>
    </div>
</x-app-layout>
