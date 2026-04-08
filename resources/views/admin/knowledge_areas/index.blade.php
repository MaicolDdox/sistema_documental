<x-app-layout>
    <x-slot name="header">Áreas del Conocimiento</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Catálogos</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Áreas del Conocimiento</span>
    </nav>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Áreas del Conocimiento</h2>
        <p class="text-sm text-slate-500 mt-1">Tabla: areas_conocimientos → gran_areas_conocimientos</p>
    </div>

    <div x-data="knowledgeAreasPage()">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Panel izquierdo: Áreas --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Áreas</h3>
                <button type="button" @click="modalNuevaArea = true"
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
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Gran Área</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($areas as $area)
                        @php
                            $detalleA = ['nombre' => $area->nombre, 'gran_area' => $area->knowledgeGrandArea?->nombre ?? '—', 'descripccion' => $area->descripccion ?? ''];
                            $editA = ['nombre' => $area->nombre, 'knowledge_grand_area_id' => (string) $area->knowledge_grand_area_id, 'descripccion' => $area->descripccion ?? ''];
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 font-medium text-slate-900">{{ $area->nombre }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $area->knowledgeGrandArea?->nombre ?? '—' }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="relative inline-flex justify-end w-full" x-data="{ open: false }">
                                    <button type="button"
                                            @click.stop="open = !open"
                                            @keydown.escape.window="open = false"
                                            class="inline-flex items-center justify-center rounded-full p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                            aria-haspopup="true"
                                            :aria-expanded="open ? 'true' : 'false'">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                         x-cloak
                                         @click.away="open = false"
                                         class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                        <button type="button"
                                                @click="open = false; openDetalleA({{ json_encode($detalleA) }})"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span>Ver detalle</span>
                                        </button>

                                        <button type="button"
                                                @click="open = false; openEditarA({{ $area->id }}, {{ json_encode($editA) }})"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                            </svg>
                                            <span>Editar</span>
                                        </button>

                                        <form id="form-delete-a-{{ $area->id }}" method="POST" action="{{ route('admin.knowledge-areas.destroy', $area) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    @click="open = false; intentEliminarA('form-delete-a-{{ $area->id }}', {{ json_encode($area->nombre) }})"
                                                    class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                                </svg>
                                                <span>Eliminar</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-500 text-sm">No hay áreas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Panel derecho: Gran Áreas --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h3 class="text-sm font-semibold text-slate-900">Gran Áreas</h3>
                <button type="button" @click="modalNuevaGranArea = true"
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
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Áreas</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-slate-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($grandAreas as $grand)
                        @php
                            $detalleG = ['nombre' => $grand->nombre, 'areas' => $grand->knowledge_areas_count ?? 0, 'descripccion' => $grand->descripccion ?? ''];
                            $editG = ['nombre' => $grand->nombre, 'descripccion' => $grand->descripccion ?? ''];
                            $hasAreas = ($grand->knowledge_areas_count ?? 0) > 0;
                        @endphp
                        <tr class="hover:bg-slate-50/50">
                            <td class="px-4 py-2.5 font-medium text-slate-900">{{ $grand->nombre }}</td>
                            <td class="px-4 py-2.5 text-slate-600">{{ $grand->knowledge_areas_count ?? 0 }} {{ ($grand->knowledge_areas_count ?? 0) === 1 ? 'área' : 'áreas' }}</td>
                            <td class="px-4 py-2.5 text-right">
                                <div class="relative inline-flex justify-end w-full" x-data="{ open: false }">
                                    <button type="button"
                                            @click.stop="open = !open"
                                            @keydown.escape.window="open = false"
                                            class="inline-flex items-center justify-center rounded-full p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                            aria-haspopup="true"
                                            :aria-expanded="open ? 'true' : 'false'">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                         x-cloak
                                         @click.away="open = false"
                                         class="absolute right-0 mt-2 w-48 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                        <button type="button"
                                                @click="open = false; openDetalleG({{ json_encode($detalleG) }})"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            </svg>
                                            <span>Ver detalle</span>
                                        </button>

                                        <button type="button"
                                                @click="open = false; openEditarG({{ $grand->id }}, {{ json_encode($editG) }})"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                            <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                            </svg>
                                            <span>Editar</span>
                                        </button>

                                        <form id="form-delete-g-{{ $grand->id }}" method="POST" action="{{ route('admin.knowledge-grand-areas.destroy', $grand) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    @click="open = false; intentEliminarG({{ $hasAreas ? 'true' : 'false' }}, 'form-delete-g-{{ $grand->id }}', {{ json_encode($grand->nombre) }})"
                                                    class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                            <span>Eliminar</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-8 text-center text-slate-500 text-sm">No hay gran áreas.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Ver detalle Área --}}
    <div x-show="modalDetalleA" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalDetalleA" @click.self="modalDetalleA = false" class="fixed inset-0 bg-black/50" x-transition></div>
            <div x-show="modalDetalleA" class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Detalle del área</h3>
                <template x-if="detalleA">
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-slate-500 font-medium">Nombre</dt><dd class="text-slate-900 mt-0.5" x-text="detalleA?.nombre"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Gran Área</dt><dd class="text-slate-900 mt-0.5" x-text="detalleA?.gran_area || '—'"></dd></div>
                        <div x-show="detalleA?.descripccion"><dt class="text-slate-500 font-medium">Descripción</dt><dd class="text-slate-900 mt-0.5 text-xs" x-text="detalleA?.descripccion"></dd></div>
                    </dl>
                </template>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="modalDetalleA = false" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Editar Área --}}
    <div x-show="modalEditarA" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditarA" @click.self="modalEditarA = false" class="fixed inset-0 bg-black/50" x-transition></div>
            <div x-show="modalEditarA" class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar área</h3>
                <form :action="editFormActionA" method="POST" id="form-editar-area">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label for="edit_a_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="edit_a_nombre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label for="edit_a_knowledge_grand_area_id" class="block text-sm font-medium text-slate-700 mb-1">Gran Área <span class="text-red-500">*</span></label>
                            <select name="knowledge_grand_area_id" id="edit_a_knowledge_grand_area_id" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                @foreach($grandAreas as $g)
                                    <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="edit_a_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                            <textarea name="descripccion" id="edit_a_descripccion" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="modalEditarA = false" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Nueva Área --}}
    <div x-show="modalNuevaArea" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalNuevaArea" @click.self="modalNuevaArea = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalNuevaArea" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-1">Nueva área</h3>
                <p class="text-sm text-slate-500 mb-4">Vincular a una gran área.</p>
                <form method="POST" action="{{ route('admin.knowledge-areas.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_form_type" value="area">
                    <div>
                        <label for="modal_a_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="modal_a_nombre" value="{{ old('nombre') }}" required class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_a_knowledge_grand_area_id" class="block text-sm font-medium text-slate-700 mb-1">Gran Área <span class="text-red-500">*</span></label>
                        <select name="knowledge_grand_area_id" id="modal_a_knowledge_grand_area_id" required class="w-full border @error('knowledge_grand_area_id') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            <option value="">Seleccionar...</option>
                            @foreach($grandAreas as $g)
                                <option value="{{ $g->id }}" {{ old('knowledge_grand_area_id') == $g->id ? 'selected' : '' }}>{{ $g->nombre }}</option>
                            @endforeach
                        </select>
                        @error('knowledge_grand_area_id')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_a_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                        <textarea name="descripccion" id="modal_a_descripccion" rows="2" class="w-full border @error('descripccion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">{{ old('descripccion') }}</textarea>
                        @error('descripccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalNuevaArea = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal No se puede eliminar (Área) --}}
    <div x-show="deleteErrorA" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div @click.self="deleteErrorA = null" class="fixed inset-0 bg-black/50"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">No se puede eliminar</h3>
                        <p class="text-sm text-slate-600" x-text="deleteErrorA"></p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="deleteErrorA = null" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Confirmar eliminación Área --}}
    <div x-show="modalConfirmEliminarA" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div @click.self="modalConfirmEliminarA = false" class="fixed inset-0 bg-black/40"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-100">
                <div class="text-center">
                    <div class="mx-auto w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">Eliminar área</h3>
                    <p class="text-sm text-slate-500 mb-1" x-text="'«' + (confirmEliminarNombreA || '') + '»'"></p>
                    <p class="text-sm text-slate-600 mb-6">¿Está seguro? Esta acción no se puede deshacer.</p>
                    <div class="flex gap-3 justify-center">
                        <button type="button" @click="modalConfirmEliminarA = false; pendingDeleteFormIdA = null" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="button" @click="submitEliminarA()" class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Ver detalle Gran Área --}}
    <div x-show="modalDetalleG" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalDetalleG" @click.self="modalDetalleG = false" class="fixed inset-0 bg-black/50" x-transition></div>
            <div x-show="modalDetalleG" class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Detalle de la gran área</h3>
                <template x-if="detalleG">
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-slate-500 font-medium">Nombre</dt><dd class="text-slate-900 mt-0.5" x-text="detalleG?.nombre"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Áreas vinculadas</dt><dd class="text-slate-900 mt-0.5" x-text="(detalleG?.areas ?? 0) + ' ' + ((detalleG?.areas ?? 0) === 1 ? 'área' : 'áreas')"></dd></div>
                        <div x-show="detalleG?.descripccion"><dt class="text-slate-500 font-medium">Descripción</dt><dd class="text-slate-900 mt-0.5 text-xs" x-text="detalleG?.descripccion"></dd></div>
                    </dl>
                </template>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="modalDetalleG = false" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Editar Gran Área --}}
    <div x-show="modalEditarG" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditarG" @click.self="modalEditarG = false" class="fixed inset-0 bg-black/50" x-transition></div>
            <div x-show="modalEditarG" class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar gran área</h3>
                <form :action="editFormActionG" method="POST" id="form-editar-gran-area">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label for="edit_g_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="edit_g_nombre" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label for="edit_g_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                            <textarea name="descripccion" id="edit_g_descripccion" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="modalEditarG = false" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Nueva Gran Área --}}
    <div x-show="modalNuevaGranArea" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalNuevaGranArea" @click.self="modalNuevaGranArea = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalNuevaGranArea" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-1">Nueva gran área</h3>
                <p class="text-sm text-slate-500 mb-4">Crear gran área de conocimiento.</p>
                <form method="POST" action="{{ route('admin.knowledge-grand-areas.store') }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="_form_type" value="grand_area">
                    <div>
                        <label for="modal_g_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="modal_g_nombre" value="{{ old('nombre') }}" required class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_g_descripccion" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                        <textarea name="descripccion" id="modal_g_descripccion" rows="2" class="w-full border @error('descripccion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">{{ old('descripccion') }}</textarea>
                        @error('descripccion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalNuevaGranArea = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal No se puede eliminar (Gran Área) --}}
    <div x-show="deleteErrorG" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div @click.self="deleteErrorG = null" class="fixed inset-0 bg-black/50"></div>
            <div class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">No se puede eliminar</h3>
                        <p class="text-sm text-slate-600" x-text="deleteErrorG"></p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="deleteErrorG = null" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Confirmar eliminación Gran Área --}}
    <div x-show="modalConfirmEliminarG" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div @click.self="modalConfirmEliminarG = false" class="fixed inset-0 bg-black/40"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-100">
                <div class="text-center">
                    <div class="mx-auto w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">Eliminar gran área</h3>
                    <p class="text-sm text-slate-500 mb-1" x-text="'«' + (confirmEliminarNombreG || '') + '»'"></p>
                    <p class="text-sm text-slate-600 mb-6">¿Está seguro? Esta acción no se puede deshacer.</p>
                    <div class="flex gap-3 justify-center">
                        <button type="button" @click="modalConfirmEliminarG = false; pendingDeleteFormIdG = null" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="button" @click="submitEliminarG()" class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function knowledgeAreasPage() {
            const baseA = '{{ url('admin/knowledge-areas') }}';
            const baseG = '{{ url('admin/knowledge-grand-areas') }}';
            return {
                // Áreas
                detalleA: null,
                modalDetalleA: false,
                modalEditarA: false,
                editDataA: { nombre: '', knowledge_grand_area_id: '', descripccion: '' },
                editFormActionA: '',
                modalNuevaArea: @json($errors->any() && old('_form_type') === 'area'),
                deleteErrorA: @json(session('delete_error_area')),
                modalConfirmEliminarA: false,
                confirmEliminarNombreA: '',
                pendingDeleteFormIdA: null,
                openDetalleA(data) { this.detalleA = data; this.modalDetalleA = true; },
                openEditarA(id, data) {
                    this.editFormActionA = baseA + '/' + id;
                    this.editDataA = { nombre: data.nombre || '', knowledge_grand_area_id: String(data.knowledge_grand_area_id || ''), descripccion: data.descripccion || '' };
                    this.modalEditarA = true;
                    this.$nextTick(() => {
                        document.getElementById('edit_a_nombre').value = this.editDataA.nombre;
                        document.getElementById('edit_a_knowledge_grand_area_id').value = this.editDataA.knowledge_grand_area_id;
                        const d = document.getElementById('edit_a_descripccion');
                        if (d) d.value = this.editDataA.descripccion;
                    });
                },
                intentEliminarA(formId, nombre) {
                    this.confirmEliminarNombreA = nombre || '';
                    this.pendingDeleteFormIdA = formId;
                    this.modalConfirmEliminarA = true;
                },
                submitEliminarA() {
                    if (this.pendingDeleteFormIdA && document.getElementById(this.pendingDeleteFormIdA)) {
                        document.getElementById(this.pendingDeleteFormIdA).submit();
                    }
                    this.modalConfirmEliminarA = false;
                    this.pendingDeleteFormIdA = null;
                },
                // Gran Áreas
                detalleG: null,
                modalDetalleG: false,
                modalEditarG: false,
                editDataG: { nombre: '', descripccion: '' },
                editFormActionG: '',
                modalNuevaGranArea: @json($errors->any() && old('_form_type') === 'grand_area'),
                deleteErrorG: @json(session('delete_error_grand')),
                modalConfirmEliminarG: false,
                confirmEliminarNombreG: '',
                pendingDeleteFormIdG: null,
                openDetalleG(data) { this.detalleG = data; this.modalDetalleG = true; },
                openEditarG(id, data) {
                    this.editFormActionG = baseG + '/' + id;
                    this.editDataG = { nombre: data.nombre || '', descripccion: data.descripccion || '' };
                    this.modalEditarG = true;
                    this.$nextTick(() => {
                        document.getElementById('edit_g_nombre').value = this.editDataG.nombre;
                        const d = document.getElementById('edit_g_descripccion');
                        if (d) d.value = this.editDataG.descripccion;
                    });
                },
                intentEliminarG(hasAreas, formId, nombre) {
                    if (hasAreas) {
                        this.deleteErrorG = 'Esta gran área tiene áreas vinculadas. Elimine o reasigne las áreas primero.';
                        return;
                    }
                    this.confirmEliminarNombreG = nombre || '';
                    this.pendingDeleteFormIdG = formId;
                    this.modalConfirmEliminarG = true;
                },
                submitEliminarG() {
                    if (this.pendingDeleteFormIdG && document.getElementById(this.pendingDeleteFormIdG)) {
                        document.getElementById(this.pendingDeleteFormIdG).submit();
                    }
                    this.modalConfirmEliminarG = false;
                    this.pendingDeleteFormIdG = null;
                }
            };
        }
    </script>
    </div>
</x-app-layout>
