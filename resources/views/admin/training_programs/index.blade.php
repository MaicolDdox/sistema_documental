<x-app-layout>
    <x-slot name="header">Programas de Formación</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Catálogos</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Programas de Formación</span>
    </nav>

    <div x-data="trainingProgramsIndex()">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Programas de Formación</h2>
            <p class="text-sm text-slate-500 mt-1">Tabla: programas_formaciones → tipos_programas_formaciones</p>
        </div>
        <button type="button" @click="modalNuevoPrograma = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-[#39A900] hover:bg-[#2d8500] transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            + Nuevo Programa
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <form method="GET" action="{{ route('admin.training-programs.index') }}" class="flex gap-3 items-center">
            <div class="flex-1 flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-2.5 bg-slate-50/50 focus-within:bg-white focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900] transition-all">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar programa..."
                       class="flex-1 bg-transparent border-0 text-sm text-slate-800 placeholder-slate-400 focus:ring-0 p-0">
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium">
                Buscar
            </button>
        </form>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Modalidad</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($programs as $program)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $program->nombre }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $program->trainingProgramType?->nombre ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $program->modalidad ? ucfirst($program->modalidad->value) : '—' }}</td>
                        <td class="px-4 py-3">
                            @if($program->estado?->value === 'activo')
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                                    <span class="w-2 h-2 rounded-full bg-green-500"></span> Activo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600">
                                    <span class="w-2 h-2 rounded-full bg-red-500"></span> Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @php
                                $detallePrograma = [
                                    'nombre' => $program->nombre,
                                    'tipo' => $program->trainingProgramType?->nombre ?? '—',
                                    'modalidad' => $program->modalidad ? ucfirst($program->modalidad->value) : '—',
                                    'estado' => $program->estado ? ucfirst($program->estado->value) : '—',
                                    'descripcion' => $program->descripcion ?? '',
                                ];
                                $editPrograma = [
                                    'nombre' => $program->nombre,
                                    'tipo' => $program->trainingProgramType?->nombre ?? '',
                                    'modalidad' => $program->modalidad?->value ?? 'presencial',
                                    'estado' => $program->estado?->value ?? 'activo',
                                    'descripcion' => $program->descripcion ?? '',
                                ];
                                $estadoActivo = $program->estado?->value === 'activo';
                            @endphp
                            <div class="relative flex items-center justify-end" x-data="{ open: false }">
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
                                    {{-- Ver detalle --}}
                                    <button type="button"
                                            @click="open = false; openDetalle({{ json_encode($detallePrograma) }})"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>Ver detalle</span>
                                    </button>

                                    {{-- Editar --}}
                                    <button type="button"
                                            @click="open = false; openEditar({{ $program->id }}, {{ json_encode($editPrograma) }})"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                        </svg>
                                        <span>Editar</span>
                                    </button>

                                    {{-- Activar / Desactivar --}}
                                    <form method="POST" action="{{ route('admin.training-programs.toggle', $program) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                @click="open = false"
                                                class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                            @if($estadoActivo)
                                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                                </svg>
                                                <span>Desactivar</span>
                                            @else
                                                <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                <span>Activar</span>
                                            @endif
                                        </button>
                                    </form>

                                    {{-- Eliminar --}}
                                    <form id="form-delete-program-{{ $program->id }}" method="POST" action="{{ route('admin.training-programs.destroy', $program) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                @click="open = false; intentEliminar({{ $estadoActivo ? 'true' : 'false' }}, 'form-delete-program-{{ $program->id }}', {{ json_encode($program->nombre) }})"
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
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                            No hay programas de formación registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($programs->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $programs->links() }}
        </div>
        @endif
    </div>

    {{-- Modal Ver detalle --}}
    <div x-show="modalDetalle" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalDetalle" @click.self="modalDetalle = false" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50"></div>
            <div x-show="modalDetalle" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Detalle del programa</h3>
                <template x-if="detalle">
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-slate-500 font-medium">Nombre</dt><dd class="text-slate-900 mt-0.5" x-text="detalle?.nombre"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Tipo</dt><dd class="text-slate-900 mt-0.5" x-text="detalle?.tipo || '—'"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Modalidad</dt><dd class="text-slate-900 mt-0.5" x-text="detalle?.modalidad || '—'"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Estado</dt><dd class="mt-0.5"><span :class="(detalle?.estado || '').toLowerCase() === 'activo' ? 'bg-green-100 text-green-800' : 'bg-slate-100 text-slate-700'" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" x-text="detalle?.estado || '—'"></span></dd></div>
                        <div x-show="detalle?.descripcion"><dt class="text-slate-500 font-medium">Descripción</dt><dd class="text-slate-900 mt-0.5 text-xs" x-text="detalle?.descripcion"></dd></div>
                    </dl>
                </template>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="modalDetalle = false" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Editar --}}
    <div x-show="modalEditar" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalEditar" @click.self="modalEditar = false" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50"></div>
            <div x-show="modalEditar" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar programa de formación</h3>
                <form :action="editFormAction" method="POST" id="form-editar-programa">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label for="edit_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre" id="edit_nombre" :value="editData.nombre" required
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label for="edit_tipo" class="block text-sm font-medium text-slate-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                            <input type="text" name="tipo" id="edit_tipo" :value="editData.tipo" required placeholder="Ej: Técnico"
                                   class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label for="edit_modalidad" class="block text-sm font-medium text-slate-700 mb-1">Modalidad <span class="text-red-500">*</span></label>
                            <select name="modalidad" id="edit_modalidad" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                <option value="presencial">Presencial</option>
                                <option value="virtual">Virtual</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit_estado" class="block text-sm font-medium text-slate-700 mb-1">Estado <span class="text-red-500">*</span></label>
                            <select name="estado" id="edit_estado" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                <option value="activo">Activo</option>
                                <option value="inactivo">Inactivo</option>
                            </select>
                        </div>
                        <div>
                            <label for="edit_descripcion" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                            <textarea name="descripcion" id="edit_descripcion" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10"></textarea>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end gap-2">
                        <button type="button" @click="modalEditar = false" class="px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: No se puede eliminar --}}
    <div x-show="deleteError" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="deleteError" @click.self="deleteError = null" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/50"></div>
            <div x-show="deleteError" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-slate-900 mb-2">No se puede eliminar</h3>
                        <p class="text-sm text-slate-600" x-text="deleteError"></p>
                    </div>
                </div>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="deleteError = null" class="px-4 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium">Entendido</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Confirmar eliminación --}}
    <div x-show="modalConfirmEliminar" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalConfirmEliminar" @click.self="modalConfirmEliminar = false" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/40"></div>
            <div x-show="modalConfirmEliminar" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-100">
                <div class="text-center">
                    <div class="mx-auto w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">Eliminar programa de formación</h3>
                    <p class="text-sm text-slate-500 mb-1" x-text="'«' + (confirmEliminarNombre || '') + '»'"></p>
                    <p class="text-sm text-slate-600 mb-6">¿Está seguro? Esta acción no se puede deshacer.</p>
                    <div class="flex gap-3 justify-center">
                        <button type="button" @click="modalConfirmEliminar = false; pendingDeleteFormId = null"
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition-colors">Cancelar</button>
                        <button type="button" @click="submitEliminar()"
                                class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold transition-colors shadow-sm">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal: Nuevo Programa de Formación --}}
    <div x-show="modalNuevoPrograma" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalNuevoPrograma" @click.self="modalNuevoPrograma = false" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/40"></div>
            <div x-show="modalNuevoPrograma" x-transition:enter="ease-out duration-200" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                 class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 border border-slate-100 max-h-[90vh] overflow-y-auto">
                <h3 class="text-lg font-semibold text-slate-900 mb-1">Nuevo programa de formación</h3>
                <p class="text-sm text-slate-500 mb-4">Registre el tipo de programa.</p>
                <form method="POST" action="{{ route('admin.training-programs.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="modal_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre" id="modal_nombre" value="{{ old('nombre') }}" required
                               class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_tipo" class="block text-sm font-medium text-slate-700 mb-1">Tipo <span class="text-red-500">*</span></label>
                        <input type="text" name="tipo" id="modal_tipo" value="{{ old('tipo') }}" required placeholder="Ej: Técnico"
                               class="w-full border @error('tipo') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('tipo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_modalidad" class="block text-sm font-medium text-slate-700 mb-1">Modalidad <span class="text-red-500">*</span></label>
                        <select name="modalidad" id="modal_modalidad" required class="w-full border @error('modalidad') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            <option value="presencial" {{ old('modalidad') == 'presencial' ? 'selected' : '' }}>Presencial</option>
                            <option value="virtual" {{ old('modalidad') == 'virtual' ? 'selected' : '' }}>Virtual</option>
                        </select>
                        @error('modalidad')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_estado" class="block text-sm font-medium text-slate-700 mb-1">Estado <span class="text-red-500">*</span></label>
                        <select name="estado" id="modal_estado" required class="w-full border @error('estado') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                            <option value="activo" {{ old('estado', 'activo') == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ old('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                        @error('estado')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_descripcion" class="block text-sm font-medium text-slate-700 mb-1">Descripción</label>
                        <textarea name="descripcion" id="modal_descripcion" rows="2" class="w-full border @error('descripcion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">{{ old('descripcion') }}</textarea>
                        @error('descripcion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalNuevoPrograma = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition-colors">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold transition-colors">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function trainingProgramsIndex() {
            const baseUrl = '{{ url('admin/training-programs') }}';
            return {
                modalDetalle: false,
                modalEditar: false,
                modalNuevoPrograma: @json($errors->any()),
                deleteError: @json(session('delete_error')),
                modalConfirmEliminar: false,
                confirmEliminarNombre: '',
                pendingDeleteFormId: null,
                detalle: null,
                editData: { nombre: '', tipo: '', modalidad: 'presencial', estado: 'activo', descripcion: '' },
                editFormAction: '',
                openDetalle(data) {
                    this.detalle = data;
                    this.modalDetalle = true;
                },
                openEditar(id, data) {
                    this.editFormAction = baseUrl + '/' + id;
                    this.editData = {
                        nombre: data.nombre || '',
                        training_program_type_id: String(data.training_program_type_id || ''),
                        modalidad: data.modalidad || 'presencial',
                        estado: data.estado || 'activo',
                        descripcion: data.descripcion || ''
                    };
                    this.modalEditar = true;
                    this.$nextTick(() => {
                        document.getElementById('edit_nombre').value = this.editData.nombre;
                        document.getElementById('edit_tipo').value = this.editData.tipo || '';
                        document.getElementById('edit_modalidad').value = this.editData.modalidad;
                        document.getElementById('edit_estado').value = this.editData.estado;
                        const desc = document.getElementById('edit_descripcion');
                        if (desc) desc.value = this.editData.descripcion || '';
                    });
                },
                intentEliminar(estadoActivo, formId, nombre) {
                    if (estadoActivo) {
                        this.deleteError = 'Este programa está activo. Desactívelo desde el botón de acciones en la fila y vuelva a intentar eliminarlo.';
                        return;
                    }
                    this.confirmEliminarNombre = nombre || '';
                    this.pendingDeleteFormId = formId;
                    this.modalConfirmEliminar = true;
                },
                submitEliminar() {
                    if (this.pendingDeleteFormId && document.getElementById(this.pendingDeleteFormId)) {
                        document.getElementById(this.pendingDeleteFormId).submit();
                    }
                    this.modalConfirmEliminar = false;
                    this.pendingDeleteFormId = null;
                }
            };
        }
    </script>
    </div>
</x-app-layout>
