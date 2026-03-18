<x-app-layout>
    <x-slot name="header">Asesores Externos</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Administración</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Asesores Externos</span>
    </nav>

    @if(session('success'))
    <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div x-data="externalAdvisorsIndex()">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Asesores Externos</h2>
            <p class="text-sm text-slate-500 mt-1">Tabla: asesores_externos — pueden tener o no cuenta en el sistema</p>
        </div>
        <button type="button" @click="modalNuevo = true"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-[#39A900] hover:bg-[#2d8500] transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            + Nuevo Asesor
        </button>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <form method="GET" action="{{ route('admin.external-advisors.index') }}" class="flex gap-3 items-center">
            <div class="flex-1 flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-2.5 bg-slate-50/50 focus-within:bg-white focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900] transition-all">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar asesor..."
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
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Email</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Institución</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Cuenta SGD</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($advisors as $advisor)
                    @php
                        $detalle = [
                            'nombre_completo' => $advisor->nombre_completo,
                            'email' => $advisor->email ?? '—',
                            'telefono' => $advisor->telefono ?? '—',
                            'institucion' => $advisor->institucion ?? '—',
                            'tiene_cuenta' => (bool) $advisor->user_id,
                            'user_email' => $advisor->user?->email ?? '',
                        ];
                        $editData = [
                            'nombre_completo' => $advisor->nombre_completo,
                            'email' => $advisor->email ?? '',
                            'telefono' => $advisor->telefono ?? '',
                            'institucion' => $advisor->institucion ?? '',
                        ];
                    @endphp
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $advisor->nombre_completo }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $advisor->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $advisor->institucion ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($advisor->user_id)
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                                    <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                                    Tiene cuenta
                                </span>
                            @else
                                <span class="text-xs text-slate-500">Sin cuenta</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
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
                                     class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                    <button type="button"
                                            @click="open = false; openDetalle({{ json_encode($detalle) }})"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span>Ver detalle</span>
                                    </button>

                                    <button type="button"
                                            @click="open = false; openEditar({{ $advisor->id }}, {{ json_encode($editData) }})"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                        </svg>
                                        <span>Editar</span>
                                    </button>

                                    <form id="form-delete-advisor-{{ $advisor->id }}" method="POST" action="{{ route('admin.external-advisors.destroy', $advisor) }}" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button"
                                                @click="open = false; intentEliminar('form-delete-advisor-{{ $advisor->id }}', {{ json_encode($advisor->nombre_completo) }})"
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
                            No hay asesores externos registrados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($advisors->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $advisors->links() }}
        </div>
        @endif
    </div>

    {{-- Modal Ver detalle --}}
    <div x-show="modalDetalle" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalDetalle" @click.self="modalDetalle = false" class="fixed inset-0 bg-black/50" x-transition></div>
            <div x-show="modalDetalle" class="relative bg-white rounded-xl shadow-xl max-w-md w-full p-6" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Detalle del asesor</h3>
                <template x-if="detalle">
                    <dl class="space-y-3 text-sm">
                        <div><dt class="text-slate-500 font-medium">Nombre completo</dt><dd class="text-slate-900 mt-0.5" x-text="detalle?.nombre_completo"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Email</dt><dd class="text-slate-900 mt-0.5" x-text="detalle?.email || '—'"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Teléfono</dt><dd class="text-slate-900 mt-0.5" x-text="detalle?.telefono || '—'"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Institución</dt><dd class="text-slate-900 mt-0.5" x-text="detalle?.institucion || '—'"></dd></div>
                        <div><dt class="text-slate-500 font-medium">Cuenta SGD</dt><dd class="text-slate-900 mt-0.5"><span x-show="detalle?.tiene_cuenta" class="text-green-600 font-medium">Tiene cuenta</span><span x-show="detalle?.tiene_cuenta && detalle?.user_email" x-text="' — ' + (detalle?.user_email || '')"></span><span x-show="!detalle?.tiene_cuenta" class="text-slate-500">Sin cuenta</span></dd></div>
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
            <div x-show="modalEditar" @click.self="modalEditar = false" class="fixed inset-0 bg-black/50" x-transition></div>
            <div x-show="modalEditar" class="relative bg-white rounded-xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar asesor externo</h3>
                <form :action="editFormAction" method="POST" id="form-editar-asesor">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label for="edit_nombre_completo" class="block text-sm font-medium text-slate-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                            <input type="text" name="nombre_completo" id="edit_nombre_completo" required class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label for="edit_email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                            <input type="email" name="email" id="edit_email" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label for="edit_telefono" class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                            <input type="text" name="telefono" id="edit_telefono" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        </div>
                        <div>
                            <label for="edit_institucion" class="block text-sm font-medium text-slate-700 mb-1">Institución</label>
                            <input type="text" name="institucion" id="edit_institucion" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
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

    {{-- Modal Nuevo asesor --}}
    <div x-show="modalNuevo" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalNuevo" @click.self="modalNuevo = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalNuevo" class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto" x-transition>
                <h3 class="text-lg font-semibold text-slate-900 mb-1">Nuevo asesor externo</h3>
                <p class="text-sm text-slate-500 mb-4">Registra un asesor externo. Puede o no tener cuenta en el sistema.</p>
                <form method="POST" action="{{ route('admin.external-advisors.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="modal_nombre_completo" class="block text-sm font-medium text-slate-700 mb-1">Nombre completo <span class="text-red-500">*</span></label>
                        <input type="text" name="nombre_completo" id="modal_nombre_completo" value="{{ old('nombre_completo') }}" required class="w-full border @error('nombre_completo') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('nombre_completo')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_email" class="block text-sm font-medium text-slate-700 mb-1">Email</label>
                        <input type="email" name="email" id="modal_email" value="{{ old('email') }}" class="w-full border @error('email') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_telefono" class="block text-sm font-medium text-slate-700 mb-1">Teléfono</label>
                        <input type="text" name="telefono" id="modal_telefono" value="{{ old('telefono') }}" class="w-full border @error('telefono') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('telefono')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="modal_institucion" class="block text-sm font-medium text-slate-700 mb-1">Institución</label>
                        <input type="text" name="institucion" id="modal_institucion" value="{{ old('institucion') }}" class="w-full border @error('institucion') border-red-500 @else border-slate-200 @enderror rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        @error('institucion')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div class="flex gap-3 justify-end pt-2">
                        <button type="button" @click="modalNuevo = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Confirmar eliminación --}}
    <div x-show="modalConfirmEliminar" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div x-show="modalConfirmEliminar" @click.self="modalConfirmEliminar = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div x-show="modalConfirmEliminar" class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-100" x-transition>
                <div class="text-center">
                    <div class="mx-auto w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79"/>
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">Eliminar asesor externo</h3>
                    <p class="text-sm text-slate-500 mb-1" x-text="'«' + (confirmEliminarNombre || '') + '»'"></p>
                    <p class="text-sm text-slate-600 mb-6">¿Está seguro? Esta acción no se puede deshacer.</p>
                    <div class="flex gap-3 justify-center">
                        <button type="button" @click="modalConfirmEliminar = false; pendingDeleteFormId = null" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                        <button type="button" @click="submitEliminar()" class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function externalAdvisorsIndex() {
            const baseUrl = '{{ url('admin/external-advisors') }}';
            return {
                modalDetalle: false,
                modalEditar: false,
                modalNuevo: @json($errors->any()),
                modalConfirmEliminar: false,
                confirmEliminarNombre: '',
                pendingDeleteFormId: null,
                detalle: null,
                editData: { nombre_completo: '', email: '', telefono: '', institucion: '' },
                editFormAction: '',
                openDetalle(data) {
                    this.detalle = data;
                    this.modalDetalle = true;
                },
                openEditar(id, data) {
                    this.editFormAction = baseUrl + '/' + id;
                    this.editData = {
                        nombre_completo: data.nombre_completo || '',
                        email: data.email || '',
                        telefono: data.telefono || '',
                        institucion: data.institucion || ''
                    };
                    this.modalEditar = true;
                    this.$nextTick(() => {
                        document.getElementById('edit_nombre_completo').value = this.editData.nombre_completo;
                        document.getElementById('edit_email').value = this.editData.email;
                        document.getElementById('edit_telefono').value = this.editData.telefono;
                        document.getElementById('edit_institucion').value = this.editData.institucion;
                    });
                },
                intentEliminar(formId, nombre) {
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

    {{-- Modal error al eliminar --}}
    @if(session('delete_error'))
    <div x-data="{ open: true }" x-show="open" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
        <div class="flex min-h-full items-center justify-center p-4">
            <div @click="open = false" class="fixed inset-0 bg-black/40" x-transition></div>
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-slate-100">
                <div class="text-center">
                    <div class="mx-auto w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center mb-4">
                        <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-900 mb-2">No se puede eliminar</h3>
                    <p class="text-sm text-slate-600 mb-6">{{ session('delete_error') }}</p>
                    <button type="button" @click="open = false" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium">Entendido</button>
                </div>
            </div>
        </div>
    </div>
    @endif
    </div>
</x-app-layout>
