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

    <div x-data="mincienciasPage()">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Panel izquierdo: Tipologías --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Tipologías</h3>
                <button type="button" @click="modalNuevaTipologia = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#39A900] hover:bg-[#2d8500] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    + Nueva
                </button>
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
                        @php
                            $detalleT = ['nombre' => $typology->nombre, 'codigo' => $typology->codigo ?? '—', 'subcategorias' => $typology->subcategories_count ?? 0, 'descripccion' => $typology->descripccion ?? ''];
                            $editT = ['nombre' => $typology->nombre, 'codigo' => $typology->codigo ?? '', 'descripccion' => $typology->descripccion ?? ''];
                            $hasSub = ($typology->subcategories_count ?? 0) > 0;
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 font-medium text-slate-900">{{ $typology->nombre }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $typology->codigo }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $typology->subcategories_count ?? 0 }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 flex-wrap">
                                    <button type="button" @click="openDetalleT({{ json_encode($detalleT) }})" class="p-1.5 inline-flex text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Ver detalle">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <button type="button" @click="openEditarT({{ $typology->id }}, {{ json_encode($editT) }})" class="p-1.5 inline-flex text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                                    </button>
                                    <form id="form-delete-t-{{ $typology->id }}" method="POST" action="{{ route('admin.minciencias-typologies.destroy', $typology) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="intentEliminarT({{ $hasSub ? 'true' : 'false' }}, 'form-delete-t-{{ $typology->id }}', {{ json_encode($typology->nombre) }})" class="p-1.5 inline-flex text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </form>
                                </div>
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
                <button type="button" @click="modalNuevaSubcategoria = true"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-white bg-[#39A900] hover:bg-[#2d8500] transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    + Nueva
                </button>
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
                        @php
                            $detalleS = ['nombre' => $sub->nombre, 'tipologia' => $sub->mincienciasTypology?->nombre ?? '—', 'descripccion' => $sub->descripccion ?? ''];
                            $editS = ['nombre' => $sub->nombre, 'minciencias_typology_id' => (string) $sub->minciencias_typology_id, 'descripccion' => $sub->descripccion ?? ''];
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 font-medium text-slate-900">{{ $sub->nombre }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $sub->mincienciasTypology?->nombre ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="flex items-center justify-end gap-1 flex-wrap">
                                    <button type="button" @click="openDetalleS({{ json_encode($detalleS) }})" class="p-1.5 inline-flex text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg" title="Ver detalle">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </button>
                                    <button type="button" @click="openEditarS({{ $sub->id }}, {{ json_encode($editS) }})" class="p-1.5 inline-flex text-slate-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg" title="Editar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" /></svg>
                                    </button>
                                    <form id="form-delete-s-{{ $sub->id }}" method="POST" action="{{ route('admin.minciencias-subcategories.destroy', $sub) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" @click="intentEliminarS('form-delete-s-{{ $sub->id }}', {{ json_encode($sub->nombre) }})" class="p-1.5 inline-flex text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg" title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                        </button>
                                    </form>
                                </div>
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

    {{-- Modales Tipologías y Subcategorías --}}
    {{-- Modal Ver detalle Tipología --}}
    <div x-show="modalDetalleT" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalDetalleT" @click.self="modalDetalleT = false" class="fixed inset-0 bg-black/50" x-transition></div>
            <div x-show="modalDetalleT" class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Detalle de la tipología</h3>
                <template x-if="detalleT">
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-slate-500 font-medium">Nombre</dt><dd class="text-slate-900 mt-0.5" x-text="detalleT?.nombre"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Código</dt><dd class="text-slate-900 mt-0.5" x-text="detalleT?.codigo || '—'"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Subcategorías</dt><dd class="text-slate-900 mt-0.5" x-text="detalleT?.subcategorias ?? 0"></dd></div>
                        <div x-show="detalleT?.descripccion"><dt class="text-slate-500 font-medium">Descripción</dt><dd class="text-slate-900 mt-0.5 text-xs" x-text="detalleT?.descripccion"></dd></div>
                    </dl>
                </template>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="modalDetalleT = false" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Editar Tipología --}}
    <div x-show="modalEditarT" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditarT" @click.self="modalEditarT = false" class="fixed inset-0 bg-black/50" x-transition></div>
            <div x-show="modalEditarT" class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar tipología</h3>
                <form :action="editFormActionT" method="POST" id="form-editar-tipologia">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label for="edit_t_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="edit_t_nombre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label for="edit_t_codigo" class="block text-sm font-medium text-slate-700 mb-1">Código <span class="text-red-500">*</span></label>
                            <input type="text" name="codigo" id="edit_t_codigo" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label for="edit_t_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                            <textarea name="descripccion" id="edit_t_descripccion" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="modalEditarT = false" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Nueva Tipología --}}
    <div x-show="modalNuevaTipologia" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalNuevaTipologia" @click.self="modalNuevaTipologia = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalNuevaTipologia" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-1">Nueva tipología</h3>
                <p class="text-sm text-slate-500 mb-4">Crear tipología Minciencias.</p>
                <form method="POST" action="{{ route('admin.minciencias-typologies.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_form_type" value="typology">
                    <div>
                        <label for="modal_t_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="modal_t_nombre" value="{{ old('nombre') }}" required class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_t_codigo" class="block text-sm font-medium text-slate-700 mb-1">Código <span class="text-red-500">*</span></label>
                        <input type="text" name="codigo" id="modal_t_codigo" value="{{ old('codigo') }}" required class="w-full border @error('codigo') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('codigo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_t_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                        <textarea name="descripccion" id="modal_t_descripccion" rows="2" class="w-full border @error('descripccion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">{{ old('descripccion') }}</textarea>
                        @error('descripccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalNuevaTipologia = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal No se puede eliminar (Tipología) --}}
    <div x-show="deleteErrorT" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div @click.self="deleteErrorT = null" class="fixed inset-0 bg-black/50"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">No se puede eliminar</h3>
                        <p class="text-sm text-slate-600" x-text="deleteErrorT"></p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="deleteErrorT = null" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Confirmar eliminación Tipología --}}
    <div x-show="modalConfirmEliminarT" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div @click.self="modalConfirmEliminarT = false" class="fixed inset-0 bg-black/40"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-100">
                <div class="text-center">
                    <div class="mx-auto w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">Eliminar tipología</h3>
                    <p class="text-sm text-slate-500 mb-1" x-text="'«' + (confirmEliminarNombreT || '') + '»'"></p>
                    <p class="text-sm text-slate-600 mb-6">¿Está seguro? Esta acción no se puede deshacer.</p>
                    <div class="flex gap-3 justify-center">
                        <button type="button" @click="modalConfirmEliminarT = false; pendingDeleteFormIdT = null" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="button" @click="submitEliminarT()" class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Ver detalle Subcategoría --}}
    <div x-show="modalDetalleS" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalDetalleS" @click.self="modalDetalleS = false" class="fixed inset-0 bg-black/50" x-transition></div>
            <div x-show="modalDetalleS" class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Detalle de la subcategoría</h3>
                <template x-if="detalleS">
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-slate-500 font-medium">Nombre</dt><dd class="text-slate-900 mt-0.5" x-text="detalleS?.nombre"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Tipología</dt><dd class="text-slate-900 mt-0.5" x-text="detalleS?.tipologia || '—'"></dd></div>
                        <div x-show="detalleS?.descripccion"><dt class="text-slate-500 font-medium">Descripción</dt><dd class="text-slate-900 mt-0.5 text-xs" x-text="detalleS?.descripccion"></dd></div>
                    </dl>
                </template>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="modalDetalleS = false" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Editar Subcategoría --}}
    <div x-show="modalEditarS" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditarS" @click.self="modalEditarS = false" class="fixed inset-0 bg-black/50" x-transition></div>
            <div x-show="modalEditarS" class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar subcategoría</h3>
                <form :action="editFormActionS" method="POST" id="form-editar-subcategoria">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label for="edit_s_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="edit_s_nombre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label for="edit_s_minciencias_typology_id" class="block text-sm font-medium text-slate-700 mb-1">Tipología <span class="text-red-500">*</span></label>
                            <select name="minciencias_typology_id" id="edit_s_minciencias_typology_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                @foreach($typologies as $t)
                                    <option value="{{ $t->id }}">{{ $t->nombre }} ({{ $t->codigo }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="edit_s_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                            <textarea name="descripccion" id="edit_s_descripccion" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="modalEditarS = false" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Nueva Subcategoría --}}
    <div x-show="modalNuevaSubcategoria" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalNuevaSubcategoria" @click.self="modalNuevaSubcategoria = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalNuevaSubcategoria" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-1">Nueva subcategoría</h3>
                <p class="text-sm text-slate-500 mb-4">Vincular a una tipología Minciencias.</p>
                <form method="POST" action="{{ route('admin.minciencias-subcategories.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_form_type" value="subcategory">
                    <div>
                        <label for="modal_s_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="modal_s_nombre" value="{{ old('nombre') }}" required class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_s_minciencias_typology_id" class="block text-sm font-medium text-slate-700 mb-1">Tipología <span class="text-red-500">*</span></label>
                        <select name="minciencias_typology_id" id="modal_s_minciencias_typology_id" required class="w-full border @error('minciencias_typology_id') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            <option value="">Seleccionar...</option>
                            @foreach($typologies as $t)
                                <option value="{{ $t->id }}" {{ old('minciencias_typology_id') == $t->id ? 'selected' : '' }}>{{ $t->nombre }} ({{ $t->codigo }})</option>
                            @endforeach
                        </select>
                        @error('minciencias_typology_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_s_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                        <textarea name="descripccion" id="modal_s_descripccion" rows="2" class="w-full border @error('descripccion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">{{ old('descripccion') }}</textarea>
                        @error('descripccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalNuevaSubcategoria = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal No se puede eliminar (Subcategoría) --}}
    <div x-show="deleteErrorS" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div @click.self="deleteErrorS = null" class="fixed inset-0 bg-black/50"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">No se puede eliminar</h3>
                        <p class="text-sm text-slate-600" x-text="deleteErrorS"></p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="deleteErrorS = null" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Confirmar eliminación Subcategoría --}}
    <div x-show="modalConfirmEliminarS" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div @click.self="modalConfirmEliminarS = false" class="fixed inset-0 bg-black/40"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-100">
                <div class="text-center">
                    <div class="mx-auto w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">Eliminar subcategoría</h3>
                    <p class="text-sm text-slate-500 mb-1" x-text="'«' + (confirmEliminarNombreS || '') + '»'"></p>
                    <p class="text-sm text-slate-600 mb-6">¿Está seguro? Esta acción no se puede deshacer.</p>
                    <div class="flex gap-3 justify-center">
                        <button type="button" @click="modalConfirmEliminarS = false; pendingDeleteFormIdS = null" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="button" @click="submitEliminarS()" class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function mincienciasPage() {
            const baseT = '{{ url('admin/minciencias-typologies') }}';
            const baseS = '{{ url('admin/minciencias-subcategories') }}';
            return {
                // Tipologías
                detalleT: null,
                modalDetalleT: false,
                modalEditarT: false,
                editDataT: { nombre: '', codigo: '', descripccion: '' },
                editFormActionT: '',
                modalNuevaTipologia: @json($errors->any() && old('_form_type') === 'typology'),
                deleteErrorT: @json(session('delete_error_typology')),
                modalConfirmEliminarT: false,
                confirmEliminarNombreT: '',
                pendingDeleteFormIdT: null,
                openDetalleT(data) { this.detalleT = data; this.modalDetalleT = true; },
                openEditarT(id, data) {
                    this.editFormActionT = baseT + '/' + id;
                    this.editDataT = { nombre: data.nombre || '', codigo: data.codigo || '', descripccion: data.descripccion || '' };
                    this.modalEditarT = true;
                    this.$nextTick(() => {
                        document.getElementById('edit_t_nombre').value = this.editDataT.nombre;
                        document.getElementById('edit_t_codigo').value = this.editDataT.codigo;
                        const d = document.getElementById('edit_t_descripccion');
                        if (d) d.value = this.editDataT.descripccion;
                    });
                },
                intentEliminarT(hasSubcategories, formId, nombre) {
                    if (hasSubcategories) {
                        this.deleteErrorT = 'Esta tipología tiene subcategorías vinculadas. Elimine o reasigne las subcategorías primero.';
                        return;
                    }
                    this.confirmEliminarNombreT = nombre || '';
                    this.pendingDeleteFormIdT = formId;
                    this.modalConfirmEliminarT = true;
                },
                submitEliminarT() {
                    if (this.pendingDeleteFormIdT && document.getElementById(this.pendingDeleteFormIdT)) {
                        document.getElementById(this.pendingDeleteFormIdT).submit();
                    }
                    this.modalConfirmEliminarT = false;
                    this.pendingDeleteFormIdT = null;
                },
                // Subcategorías
                detalleS: null,
                modalDetalleS: false,
                modalEditarS: false,
                editDataS: { nombre: '', minciencias_typology_id: '', descripccion: '' },
                editFormActionS: '',
                modalNuevaSubcategoria: @json($errors->any() && old('_form_type') === 'subcategory'),
                deleteErrorS: @json(session('delete_error_subcategory')),
                modalConfirmEliminarS: false,
                confirmEliminarNombreS: '',
                pendingDeleteFormIdS: null,
                openDetalleS(data) { this.detalleS = data; this.modalDetalleS = true; },
                openEditarS(id, data) {
                    this.editFormActionS = baseS + '/' + id;
                    this.editDataS = { nombre: data.nombre || '', minciencias_typology_id: String(data.minciencias_typology_id || ''), descripccion: data.descripccion || '' };
                    this.modalEditarS = true;
                    this.$nextTick(() => {
                        document.getElementById('edit_s_nombre').value = this.editDataS.nombre;
                        document.getElementById('edit_s_minciencias_typology_id').value = this.editDataS.minciencias_typology_id;
                        const d = document.getElementById('edit_s_descripccion');
                        if (d) d.value = this.editDataS.descripccion;
                    });
                },
                intentEliminarS(formId, nombre) {
                    this.confirmEliminarNombreS = nombre || '';
                    this.pendingDeleteFormIdS = formId;
                    this.modalConfirmEliminarS = true;
                },
                submitEliminarS() {
                    if (this.pendingDeleteFormIdS && document.getElementById(this.pendingDeleteFormIdS)) {
                        document.getElementById(this.pendingDeleteFormIdS).submit();
                    }
                    this.modalConfirmEliminarS = false;
                    this.pendingDeleteFormIdS = null;
                }
            };
        }
    </script>
    </div>
</x-app-layout>
