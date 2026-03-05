<x-app-layout>
    <x-slot name="header">Gestión de Usuarios</x-slot>

    {{-- Breadcrumbs --}}
    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Administración</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Usuarios</span>
    </nav>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Gestión de Usuarios</h2>
            <p class="text-sm text-slate-500 mt-1">Tabla: users ‣ personas — CRUD completo</p>
        </div>
        @can('usuarios.crear')
        <a href="{{ route('admin.usuarios.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white bg-[#39A900] hover:bg-[#2d8500] transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            + Crear Usuario
        </a>
        @endcan
    </div>

    {{-- Pestañas: Todos | Activos | Inactivos --}}
    <div class="flex gap-1 mb-4 border-b border-slate-200">
        <a href="{{ route('admin.usuarios.index', request()->only('search', 'rol')) }}"
           class="px-4 py-2.5 text-sm font-medium rounded-t-lg transition-colors {{ !request('estado') ? 'bg-white border border-b-0 border-slate-200 text-slate-900 -mb-px' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
            Todos
        </a>
        <a href="{{ route('admin.usuarios.index', array_merge(request()->only('search', 'rol'), ['estado' => 'activo'])) }}"
           class="px-4 py-2.5 text-sm font-medium rounded-t-lg transition-colors {{ request('estado') === 'activo' ? 'bg-white border border-b-0 border-slate-200 text-slate-900 -mb-px' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
            Activos
        </a>
        <a href="{{ route('admin.usuarios.index', array_merge(request()->only('search', 'rol'), ['estado' => 'inactivo'])) }}"
           class="px-4 py-2.5 text-sm font-medium rounded-t-lg transition-colors {{ request('estado') === 'inactivo' ? 'bg-white border border-b-0 border-slate-200 text-slate-900 -mb-px' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
            Inactivos
        </a>
    </div>

    {{-- Buscar + filtro rol --}}
    <div class="bg-white rounded-xl border border-slate-200 p-4 mb-6">
        <form method="GET" action="{{ route('admin.usuarios.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
            <input type="hidden" name="estado" value="{{ request('estado') }}">
            <div class="flex-1 flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-2.5 bg-slate-50/50 focus-within:bg-white focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900] transition-all">
                <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, documento..."
                       class="flex-1 bg-transparent border-0 text-sm text-slate-800 placeholder-slate-400 focus:ring-0 p-0">
            </div>
            <div class="w-full sm:w-48">
                <label class="block text-xs font-medium text-slate-500 mb-1">Rol</label>
                <select name="rol" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                    <option value="">Todos los roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role->name }}" {{ request('rol') == $role->name ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-sm font-medium">
                Filtrar
            </button>
        </form>
    </div>

    {{-- Tabla --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre completo</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Documento</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Email institucional</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Cargo</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($usuarios as $user)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-[#0a1628] text-white flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ $user->initials() }}
                                </div>
                                <div>
                                    <p class="font-medium text-slate-900">{{ $user->person ? $user->person->nombre_completo : 'Sin nombre' }}</p>
                                    <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-700">
                            {{ $user->tipo_documento?->value ?? '—' }} {{ $user->numero_documento }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $user->person?->email_institucional ?? $user->email ?? '—' }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $user->person?->entityPosition?->nombre ?? $user->roles->first()?->name ? ucfirst(str_replace('_', ' ', $user->roles->first()->name)) : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($user->estado?->value === 'activo')
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
                            <div class="flex items-center justify-end gap-1" x-data="{ roleModal: false }">
                                @can('usuarios.editar')
                                <a href="{{ route('admin.usuarios.edit', $user->id) }}" class="p-2 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition-colors" title="Ver / Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('admin.usuarios.edit', $user->id) }}" class="p-2 rounded-lg text-slate-400 hover:text-[#39A900] hover:bg-green-50 transition-colors" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                    </svg>
                                </a>
                                @endcan
                                @can('usuarios.asignar_rol')
                                <button type="button" @click="roleModal = true" class="p-2 rounded-lg text-slate-400 hover:text-[#39A900] hover:bg-green-50 transition-colors" title="Gestionar roles">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                </button>
                                {{-- Modal asignar/revocar roles (mismo contenido que antes) --}}
                                <div x-show="roleModal" x-cloak style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                        <div x-show="roleModal" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm" aria-hidden="true"></div>
                                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                        <div x-show="roleModal" x-transition @click.away="roleModal = false" class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl sm:my-8 sm:align-middle sm:max-w-md w-full border border-slate-200">
                                            <div class="px-4 pt-5 pb-4 sm:p-6">
                                                <h3 class="text-lg font-semibold text-slate-900 mb-2" id="modal-title">Gestionar roles — {{ $user->person ? $user->person->primer_nombre : $user->email }}</h3>
                                                <div class="mb-4">
                                                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Roles actuales</p>
                                                    <div class="flex flex-wrap gap-2">
                                                        @forelse($user->roles as $role)
                                                            <form method="POST" action="{{ route('admin.usuarios.revocar_rol', $user->id) }}" class="inline">
                                                                @csrf
                                                                <input type="hidden" name="rol" value="{{ $role->name }}">
                                                                <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium bg-green-50 text-[#39A900] hover:bg-red-50 hover:text-red-700 border border-green-200 transition-colors">
                                                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
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
                                            </div>
                                            <div class="bg-slate-50 px-4 py-3 border-t border-slate-100">
                                                <button type="button" @click="roleModal = false" class="w-full sm:w-auto px-4 py-2 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">
                                                    Cerrar
                                                </button>
                                            </div>
                                        </div>
                                        </div>
                                    </div>
                                </div>
                                @endcan
                                @can('usuarios.activar_desactivar')
                                @if(auth()->id() !== $user->id)
                                <form method="POST" action="{{ route('admin.usuarios.toggle_estado', $user->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition-colors" title="{{ $user->estado?->value === 'activo' ? 'Desactivar' : 'Activar' }}">
                                        @if($user->estado?->value === 'activo')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" /></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                        @endif
                                    </button>
                                </form>
                                @endif
                                @endcan
                                @can('usuarios.editar')
                                @if(auth()->id() !== $user->id)
                                <form method="POST" action="{{ route('admin.usuarios.destroy', $user->id) }}" class="inline" onsubmit="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                    </button>
                                </form>
                                @endif
                                @endcan
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-slate-500">
                            No se encontraron usuarios con los filtros actuales.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($usuarios->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $usuarios->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
