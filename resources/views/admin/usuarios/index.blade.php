<x-app-layout>
    <x-slot name="header">Gestión de Usuarios</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Administración</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Usuarios</span>
    </nav>

    @if(session('success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">{{ session('error') }}</div>
    @endif

    <div x-data="usuariosIndex()" class="space-y-6">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Gestión de Usuarios</h2>
                <p class="text-sm text-slate-500 mt-1">Tabla: users ‣ personas — CRUD completo</p>
            </div>
            @can('usuarios.crear')
            <button type="button" @click="modalNuevo = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white bg-[#39A900] hover:bg-[#2d8500] transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                + Crear Usuario
            </button>
            @endcan
        </div>

        <div class="flex gap-1 mb-4 border-b border-slate-200">
            <a href="{{ route('admin.usuarios.index', request()->only('search', 'rol')) }}"
               class="px-4 py-2.5 text-sm font-medium rounded-t-lg transition-colors {{ !request('estado') ? 'bg-white border border-b-0 border-slate-200 text-slate-900 -mb-px' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Todos</a>
            <a href="{{ route('admin.usuarios.index', array_merge(request()->only('search', 'rol'), ['estado' => 'activo'])) }}"
               class="px-4 py-2.5 text-sm font-medium rounded-t-lg transition-colors {{ request('estado') === 'activo' ? 'bg-white border border-b-0 border-slate-200 text-slate-900 -mb-px' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Activos</a>
            <a href="{{ route('admin.usuarios.index', array_merge(request()->only('search', 'rol'), ['estado' => 'inactivo'])) }}"
               class="px-4 py-2.5 text-sm font-medium rounded-t-lg transition-colors {{ request('estado') === 'inactivo' ? 'bg-white border border-b-0 border-slate-200 text-slate-900 -mb-px' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">Inactivos</a>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
            <form method="GET" action="{{ route('admin.usuarios.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
                <input type="hidden" name="estado" value="{{ request('estado') }}">
                <div class="flex-1 flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-2.5 bg-slate-50/50 focus-within:bg-white focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900] transition-all">
                    <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, documento..."
                           class="flex-1 bg-transparent border-0 text-sm text-slate-800 placeholder-slate-400 focus:ring-0 p-0">
                </div>
                <div class="w-full sm:w-48">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Rol</label>
                    <select name="rol" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                        <option value="">Todos los roles</option>
                        @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('rol') == $role->name ? 'selected' : '' }}>{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium">Filtrar</button>
            </form>
        </div>

        {{-- Lista en formato registro cuadrado (tarjetas) --}}
        <div class="space-y-4">
            @forelse($usuarios as $user)
            @php
                $nombreCompleto = $user->person ? $user->person->nombre_completo : 'Sin nombre';
                $docLabel = $user->tipo_documento?->value ?? '—';
                $cargoLabel = $user->person?->entityPosition?->nombre ?? ($user->roles->first() ? ucfirst(str_replace('_', ' ', $user->roles->first()->name)) : '—');
                $estadoActivo = $user->estado?->value === 'activo';
            @endphp
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden hover:border-slate-300 transition-colors" x-data="{ roleModal: false }">
                <div class="p-4 sm:p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                    <div class="flex items-center gap-4 min-w-0 flex-1">
                        <div class="w-12 h-12 rounded-xl bg-[#0a1628] text-white flex items-center justify-center font-bold text-sm shrink-0">{{ $user->initials() }}</div>
                        <div class="min-w-0 flex-1 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-6 gap-y-1">
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Nombre completo</p>
                                <p class="font-medium text-slate-900 truncate">{{ $nombreCompleto }}</p>
                                <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Documento</p>
                                <p class="text-sm text-slate-700">{{ $docLabel }} {{ $user->numero_documento }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Email institucional</p>
                                <p class="text-sm text-slate-600 truncate">{{ $user->person?->email_institucional ?? $user->email ?? '—' }}</p>
                            </div>
                            <div>
                                <p class="text-xs font-medium text-slate-500 uppercase tracking-wide">Cargo / Estado</p>
                                <p class="text-sm text-slate-700">{{ $cargoLabel }}</p>
                                @if($estadoActivo)
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700 mt-0.5"><span class="w-2 h-2 rounded-full bg-green-500"></span> Activo</span>
                                @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 mt-0.5"><span class="w-2 h-2 rounded-full bg-red-500"></span> Inactivo</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-1 flex-shrink-0 border-t sm:border-t-0 sm:border-l border-slate-100 pt-4 sm:pt-0 sm:pl-4">
                        <button type="button" @click="openDetalle({{ json_encode([
                            'nombre_completo' => $nombreCompleto,
                            'documento' => $docLabel . ' ' . $user->numero_documento,
                            'email' => $user->email,
                            'email_institucional' => $user->person?->email_institucional ?? $user->email ?? '—',
                            'cargo' => $cargoLabel,
                            'roles' => $user->roles->pluck('name')->map(fn($n) => ucfirst(str_replace('_', ' ', $n)))->toArray(),
                            'estado' => $estadoActivo ? 'Activo' : 'Inactivo',
                        ]) }})" class="p-2.5 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Ver detalle">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </button>
                        @can('usuarios.editar')
                        <button type="button" @click="openEditar({{ $user->id }}, {{ json_encode([
                            'nombre' => $user->person?->primer_nombre ?? '',
                            'apellido' => $user->person?->primer_apellido ?? '',
                            'numero_documento' => $user->numero_documento,
                            'email' => $user->email,
                            'rol' => $user->roles->first()?->name ?? '',
                        ]) }})" class="p-2.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="Editar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"/></svg>
                        </button>
                        @endcan
                        @can('usuarios.asignar_rol')
                        <button type="button" @click="roleModal = true" class="p-2.5 rounded-lg text-slate-400 hover:text-[#39A900] hover:bg-green-50 transition-colors" title="Gestionar roles">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        </button>
                        @endcan
                        @can('usuarios.activar_desactivar')
                        @if(auth()->id() !== $user->id)
                        <button type="button" @click="openToggle({{ $user->id }}, '{{ addslashes($nombreCompleto) }}', {{ $estadoActivo ? 'true' : 'false' }})" class="p-2.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="{{ $estadoActivo ? 'Desactivar' : 'Activar' }}">
                            @if($estadoActivo)
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            @else
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </button>
                        @endif
                        @endcan
                        @can('usuarios.editar')
                        @if(auth()->id() !== $user->id)
                        <button type="button" @click="openEliminar({{ $user->id }}, '{{ addslashes($nombreCompleto) }}')" class="p-2.5 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Eliminar">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/></svg>
                        </button>
                        @endif
                        @endcan
                    </div>
                </div>

                {{-- Modal Gestionar roles (por usuario) --}}
                <div x-show="roleModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
                    <div class="flex min-h-full items-center justify-center p-4">
                        <div x-show="roleModal" @click.self="roleModal = false" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" x-transition></div>
                        <div x-show="roleModal" x-transition class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-200">
                            <h3 class="text-lg font-semibold text-slate-900 mb-2">Gestionar roles — {{ $user->person ? $user->person->primer_nombre : $user->email }}</h3>
                            <div class="mb-4">
                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Roles actuales</p>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($user->roles as $role)
                                    <form method="POST" action="{{ route('admin.usuarios.revocar_rol', $user->id) }}" class="inline">
                                        @csrf
                                        <input type="hidden" name="rol" value="{{ $role->name }}">
                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-green-50 text-[#39A900] hover:bg-red-50 hover:text-red-700 border border-green-200 transition-colors">
                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                    @empty
                                    <p class="text-sm text-slate-500 italic">Sin roles</p>
                                    @endforelse
                                </div>
                            </div>
                            <hr class="border-slate-100 my-4">
                            <form method="POST" action="{{ route('admin.usuarios.asignar_rol', $user->id) }}">
                                @csrf
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Asignar rol</label>
                                <div class="flex gap-2">
                                    <select name="rol" required class="flex-1 border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                                        <option value="">Seleccione...</option>
                                        @foreach($roles as $role)
                                        @if(!$user->hasRole($role->name))
                                        <option value="{{ $role->name }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                                        @endif
                                        @endforeach
                                    </select>
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Asignar</button>
                                </div>
                            </form>
                            <div class="mt-4 pt-4 border-t border-slate-100">
                                <button type="button" @click="roleModal = false" class="w-full px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-slate-200 p-12 text-center">
                <p class="text-slate-500">No se encontraron usuarios con los filtros actuales.</p>
            </div>
            @endforelse
        </div>

        @if($usuarios->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $usuarios->links() }}
        </div>
        @endif

        {{-- Modal Ver detalle --}}
        <div x-show="modalDetalle" x-cloak class="fixed inset-0 z-[60] overflow-y-auto" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalDetalle" @click.self="modalDetalle = false" class="fixed inset-0 bg-black/50" x-transition></div>
                <div x-show="modalDetalle" x-transition class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 border border-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Detalle del usuario</h3>
                    <template x-if="detalle">
                        <dl class="grid grid-cols-1 gap-4 text-sm">
                            <div><dt class="text-slate-500 font-medium">Nombre completo</dt><dd class="text-slate-900 mt-0.5" x-text="detalle.nombre_completo"></dd></div>
                            <div><dt class="text-slate-500 font-medium">Documento</dt><dd class="text-slate-900 mt-0.5" x-text="detalle.documento"></dd></div>
                            <div><dt class="text-slate-500 font-medium">Correo electrónico</dt><dd class="text-slate-900 mt-0.5 break-all" x-text="detalle.email"></dd></div>
                            <div><dt class="text-slate-500 font-medium">Email institucional</dt><dd class="text-slate-900 mt-0.5 break-all" x-text="detalle.email_institucional || '—'"></dd></div>
                            <div><dt class="text-slate-500 font-medium">Cargo / Rol</dt><dd class="text-slate-900 mt-0.5" x-text="detalle.cargo"></dd></div>
                            <div x-show="detalle.roles && detalle.roles.length">
                                <dt class="text-slate-500 font-medium">Roles asignados</dt>
                                <dd class="mt-1 flex flex-wrap gap-1.5">
                                    <template x-for="r in detalle.roles" :key="r">
                                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#39A900]/10 text-[#2d8500]" x-text="r"></span>
                                    </template>
                                </dd>
                            </div>
                            <div><dt class="text-slate-500 font-medium">Estado</dt><dd class="mt-0.5"><span class="font-medium" x-text="detalle.estado" :class="detalle.estado === 'Activo' ? 'text-green-600' : 'text-red-600'"></span></dd></div>
                        </dl>
                    </template>
                    <div class="mt-6 flex justify-end">
                        <button type="button" @click="modalDetalle = false" class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Editar --}}
        <div x-show="modalEditar" x-cloak class="fixed inset-0 z-[60] overflow-y-auto" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalEditar" @click.self="modalEditar = false" class="fixed inset-0 bg-black/50" x-transition></div>
                <div x-show="modalEditar" x-transition class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto border border-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900 mb-4">Editar usuario</h3>
                    <form :action="editFormAction" method="POST" id="form-editar-usuario">
                        @csrf
                        @method('PUT')
                        <div class="space-y-4">
                            <div>
                                <label for="edit_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombres <span class="text-red-500">*</span></label>
                                <input type="text" name="nombre" id="edit_nombre" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20">
                            </div>
                            <div>
                                <label for="edit_apellido" class="block text-sm font-medium text-slate-700 mb-1">Apellidos <span class="text-red-500">*</span></label>
                                <input type="text" name="apellido" id="edit_apellido" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20">
                            </div>
                            <div>
                                <label for="edit_numero_documento" class="block text-sm font-medium text-slate-700 mb-1">Número de documento <span class="text-red-500">*</span></label>
                                <input type="text" name="numero_documento" id="edit_numero_documento" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20">
                            </div>
                            <div>
                                <label for="edit_email" class="block text-sm font-medium text-slate-700 mb-1">Correo electrónico <span class="text-red-500">*</span></label>
                                <input type="email" name="email" id="edit_email" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20">
                            </div>
                            <div>
                                <label for="edit_rol" class="block text-sm font-medium text-slate-700 mb-1">Rol principal <span class="text-red-500">*</span></label>
                                <select name="rol" id="edit_rol" required class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20">
                                    <option value="">Selecciona un rol...</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end gap-2">
                            <button type="button" @click="modalEditar = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                            <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Actualizar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Confirmar eliminar --}}
        <div x-show="modalEliminar" x-cloak class="fixed inset-0 z-[60] overflow-y-auto" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalEliminar" @click.self="modalEliminar = false" class="fixed inset-0 bg-black/50" x-transition></div>
                <div x-show="modalEliminar" x-transition class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 border border-slate-100">
                    <div class="text-center">
                        <div class="mx-auto w-14 h-14 rounded-full bg-red-50 flex items-center justify-center mb-4">
                            <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79"/></svg>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-1">Eliminar usuario</h3>
                        <p class="text-sm text-slate-600 mb-2" x-text="'«' + (eliminarNombre || '') + '»'"></p>
                        <p class="text-sm text-slate-500 mb-6">Esta acción no se puede deshacer.</p>
                        <div class="flex gap-3 justify-center">
                            <button type="button" @click="modalEliminar = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                            <form :action="eliminarFormAction" method="POST" class="inline" id="form-confirm-eliminar">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Crear usuario --}}
        @can('usuarios.crear')
        <div x-show="modalNuevo" x-cloak class="fixed inset-0 z-[60] overflow-y-auto" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalNuevo" @click.self="modalNuevo = false" class="fixed inset-0 bg-black/50" x-transition></div>
                <div x-show="modalNuevo" x-transition class="relative bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto border border-slate-100">
                    <h3 class="text-lg font-semibold text-slate-900 mb-1">Nuevo usuario</h3>
                    <p class="text-sm text-slate-500 mb-4">Ingresa los datos para registrar un nuevo usuario en el sistema.</p>
                    <form method="POST" action="{{ route('admin.usuarios.store') }}" class="space-y-4">
                        @csrf
                        <input type="hidden" name="_form_type" value="create">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="crear_nombre" class="block text-sm font-medium text-slate-700 mb-1">Nombres <span class="text-red-500">*</span></label>
                                <input type="text" name="nombre" id="crear_nombre" value="{{ old('nombre') }}" required class="w-full border @error('nombre') border-red-500 @else border-slate-200 @enderror rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20">
                                @error('nombre')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label for="crear_apellido" class="block text-sm font-medium text-slate-700 mb-1">Apellidos <span class="text-red-500">*</span></label>
                                <input type="text" name="apellido" id="crear_apellido" value="{{ old('apellido') }}" required class="w-full border @error('apellido') border-red-500 @else border-slate-200 @enderror rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20">
                                @error('apellido')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                            </div>
                        </div>
                        <div>
                            <label for="crear_numero_documento" class="block text-sm font-medium text-slate-700 mb-1">Número de documento <span class="text-red-500">*</span></label>
                            <input type="text" name="numero_documento" id="crear_numero_documento" value="{{ old('numero_documento') }}" required class="w-full border @error('numero_documento') border-red-500 @else border-slate-200 @enderror rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20">
                            @error('numero_documento')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="crear_email" class="block text-sm font-medium text-slate-700 mb-1">Correo electrónico <span class="text-red-500">*</span></label>
                            <input type="email" name="email" id="crear_email" value="{{ old('email') }}" required class="w-full border @error('email') border-red-500 @else border-slate-200 @enderror rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20">
                            @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="crear_password" class="block text-sm font-medium text-slate-700 mb-1">Contraseña temporal <span class="text-red-500">*</span></label>
                            <input type="password" name="password" id="crear_password" required class="w-full border @error('password') border-red-500 @else border-slate-200 @enderror rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20">
                            <p class="text-xs text-slate-500 mt-1">Mínimo 8 caracteres.</p>
                            @error('password')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                        </div>
                        <div class="flex gap-3 justify-end pt-2">
                            <button type="button" @click="modalNuevo = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                            <button type="submit" class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold">Guardar usuario</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcan

        {{-- Modal Confirmar activar/desactivar --}}
        <div x-show="modalToggle" x-cloak class="fixed inset-0 z-[60] overflow-y-auto" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalToggle" @click.self="modalToggle = false" class="fixed inset-0 bg-black/50" x-transition></div>
                <div x-show="modalToggle" x-transition class="relative bg-white rounded-2xl shadow-xl max-w-sm w-full p-6 border border-slate-100">
                    <div class="text-center">
                        <div class="mx-auto w-14 h-14 rounded-full flex items-center justify-center mb-4" :class="toggleActivo ? 'bg-amber-50' : 'bg-green-50'">
                            <template x-if="toggleActivo">
                                <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                            </template>
                            <template x-if="!toggleActivo">
                                <svg class="w-7 h-7 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </template>
                        </div>
                        <h3 class="text-lg font-semibold text-slate-900 mb-1" x-text="toggleActivo ? 'Desactivar usuario' : 'Activar usuario'"></h3>
                        <p class="text-sm text-slate-600 mb-2" x-text="'«' + (toggleNombre || '') + '»'"></p>
                        <p class="text-sm text-slate-500 mb-6" x-text="toggleActivo ? 'El usuario no podrá iniciar sesión.' : 'El usuario podrá acceder al sistema.'"></p>
                        <div class="flex gap-3 justify-center">
                            <button type="button" @click="modalToggle = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                            <form :action="toggleFormAction" method="POST" class="inline" id="form-confirm-toggle">
                                @csrf
                                <button type="submit" class="px-4 py-2.5 rounded-xl text-white text-sm font-semibold" :class="toggleActivo ? 'bg-amber-500 hover:bg-amber-600' : 'bg-[#39A900] hover:bg-[#2d8500]'" x-text="toggleActivo ? 'Desactivar' : 'Activar'"></button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function usuariosIndex() {
            const baseUrl = '{{ url('admin/usuarios') }}';
            return {
                modalDetalle: false,
                modalEditar: false,
                modalEliminar: false,
                modalToggle: false,
                modalNuevo: @json(old('_form_type') === 'create'),
                detalle: null,
                editFormAction: '',
                editData: {},
                eliminarFormAction: '',
                eliminarNombre: '',
                toggleFormAction: '',
                toggleNombre: '',
                toggleActivo: true,
                openDetalle(data) {
                    this.detalle = data;
                    this.modalDetalle = true;
                },
                openEditar(id, data) {
                    this.editFormAction = baseUrl + '/' + id;
                    this.editData = data || {};
                    this.modalEditar = true;
                    this.$nextTick(() => {
                        const n = document.getElementById('edit_nombre');
                        const a = document.getElementById('edit_apellido');
                        const d = document.getElementById('edit_numero_documento');
                        const e = document.getElementById('edit_email');
                        const r = document.getElementById('edit_rol');
                        if (n) n.value = this.editData.nombre || '';
                        if (a) a.value = this.editData.apellido || '';
                        if (d) d.value = this.editData.numero_documento || '';
                        if (e) e.value = this.editData.email || '';
                        if (r) r.value = this.editData.rol || '';
                    });
                },
                openEliminar(id, nombre) {
                    this.eliminarFormAction = baseUrl + '/' + id;
                    this.eliminarNombre = nombre || '';
                    this.modalEliminar = true;
                },
                openToggle(id, nombre, estaActivo) {
                    this.toggleFormAction = baseUrl + '/' + id + '/toggle-estado';
                    this.toggleNombre = nombre || '';
                    this.toggleActivo = !!estaActivo;
                    this.modalToggle = true;
                }
            };
        }
    </script>
</x-app-layout>
