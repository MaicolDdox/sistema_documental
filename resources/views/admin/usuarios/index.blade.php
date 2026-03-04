<x-app-layout>
    <x-slot name="header">Gestión de Usuarios</x-slot>
<div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
    <div>
        <h2 class="text-xl font-semibold text-slate-900">Usuarios</h2>
        <p class="text-sm text-slate-500 mt-1">Gestiona los accesos y roles de los usuarios del sistema.</p>
    </div>
    @can('usuarios.crear')
    <div>
        <a href="{{ route('admin.usuarios.create') }}" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Crear usuario
        </a>
    </div>
    @endcan
</div>

<!-- Filtros -->
<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
    <form method="GET" action="{{ route('admin.usuarios.index') }}" class="grid grid-cols-1 sm:grid-cols-3 md:grid-cols-4 gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Buscar</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nombre, doc o email..."
                   class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Rol</label>
            <select name="rol" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                <option value="">Todos los roles</option>
                @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ request('rol') == $role->name ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Estado</label>
            <select name="estado" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                <option value="">Todos los estados</option>
                @foreach($estados as $estado)
                    <option value="{{ $estado->value }}" {{ request('estado') == $estado->value ? 'selected' : '' }}>
                        {{ ucfirst($estado->name) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button type="submit" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all w-full sm:w-auto">
                Filtrar
            </button>
            @if(request()->hasAny(['search', 'rol', 'estado']) && (request('search') || request('rol') || request('estado')))
            <a href="{{ route('admin.usuarios.index') }}" class="text-slate-500 hover:text-slate-700 p-2" title="Limpiar filtros">
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
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Usuario</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Documento</th>
                    <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Rol(es)</th>
                    <th class="text-center px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                    <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($usuarios as $user)
                <tr class="hover:bg-slate-50 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-sgd-blue text-white flex items-center justify-center font-bold text-xs shrink-0">
                                {{ $user->initials() }}
                            </div>
                            <div>
                                <p class="font-medium text-slate-900">{{ $user->person ? $user->person->nombre_completo : 'Sin Nombre' }}</p>
                                <p class="text-xs text-slate-500">{{ $user->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        {{ $user->tipo_documento?->value ?? '?' }} {{ $user->numero_documento }}
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        <div class="flex flex-wrap gap-1">
                            @forelse($user->roles as $role)
                                <span class="px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                </span>
                            @empty
                                <span class="text-slate-400 text-xs italic">Sin rol</span>
                            @endforelse
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($user->estado?->value === 'activo')
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
                        <div class="flex items-center justify-end gap-2" x-data="{ roleModal: false }">
                            
                            @can('usuarios.editar')
                            <a href="{{ route('admin.usuarios.edit', $user->id) }}" class="text-slate-400 hover:text-sgd-green p-1 transition-colors" title="Editar">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                                </svg>
                            </a>
                            @endcan

                            @can('usuarios.asignar_rol')
                            <button @click="roleModal = true" class="text-slate-400 hover:text-sgd-blue p-1 transition-colors" title="Gestionar Roles">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </button>
                            
                            <!-- Role Modal -->
                            <div x-show="roleModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                                    <div x-show="roleModal" x-transition.opacity class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                                    <div x-show="roleModal" x-transition @click.away="roleModal = false" class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md w-full border border-slate-200">
                                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                                            <h3 class="text-lg leading-6 font-semibold text-slate-900 mb-2" id="modal-title">
                                                Gestionar Roles para {{ $user->person ? $user->person->primer_nombre : $user->email }}
                                            </h3>
                                            
                                            <!-- Current Roles -->
                                            <div class="mb-4">
                                                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Roles Actuales</p>
                                                <div class="flex flex-wrap gap-2">
                                                    @forelse($user->roles as $role)
                                                    <form method="POST" action="{{ route('admin.usuarios.revocar_rol', $user->id) }}" class="inline">
                                                        @csrf
                                                        <input type="hidden" name="rol" value="{{ $role->name }}">
                                                        <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-medium bg-blue-50 text-blue-700 hover:bg-red-50 hover:text-red-700 transition-colors border border-blue-200" title="Revocar rol">
                                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                                        </button>
                                                    </form>
                                                    @empty
                                                        <p class="text-sm text-slate-500 italic">No tiene roles asignados</p>
                                                    @endforelse
                                                </div>
                                            </div>

                                            <hr class="border-slate-100 my-4">

                                            <!-- Assign Role -->
                                            <form method="POST" action="{{ route('admin.usuarios.asignar_rol', $user->id) }}">
                                                @csrf
                                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Asignar Nuevo Rol</label>
                                                <div class="flex gap-2">
                                                    <select name="rol" required class="flex-1 border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                                                        <option value="">Selecciona un rol...</option>
                                                        @foreach($roles as $role)
                                                            @if(!$user->hasRole($role->name))
                                                                <option value="{{ $role->name }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                                                            @endif
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                                                        Asignar
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-slate-100">
                                            <button type="button" @click="roleModal = false" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-200 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                                Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endcan

                            @can('usuarios.activar_desactivar')
                            @if(auth()->id() !== $user->id)
                            <form method="POST" action="{{ route('admin.usuarios.toggle_estado', $user->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-slate-400 hover:{{ $user->estado?->value === 'activo' ? 'text-red-500' : 'text-green-500' }} p-1 transition-colors" title="{{ $user->estado?->value === 'activo' ? 'Desactivar' : 'Activar' }}">
                                    @if($user->estado?->value === 'activo')
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    @endif
                                </button>
                            </form>
                            @endif
                            @endcan

                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-slate-500">
                        No se encontraron usuarios con los filtros actuales.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    @if($usuarios->hasPages())
    <div class="px-5 py-4 border-t border-slate-100">
        {{ $usuarios->links() }}
    </div>
    @endif
</x-app-layout>
