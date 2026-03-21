<x-app-layout>
    <x-slot name="header">Usuarios con rol</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Administración</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.usuarios.index') }}" class="hover:text-slate-700">Usuarios</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Usuarios con rol</span>
    </nav>

    @if(session('success'))
    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-sm">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-sm">
        {{ session('error') }}
    </div>
    @endif

    <div x-data="usuariosConRol()" class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Usuarios con rol asignado</h2>
                <p class="text-sm text-slate-500 mt-1">Listado de usuarios que ya tienen al menos un rol. El <span class="font-medium text-slate-700">rol principal</span> es el guardado para el usuario o, si no hay uno definido, la prioridad del sistema. Al <span class="font-medium">agregar otro rol</span>, el principal no cambia solo por la prioridad automática.</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50">
                <form method="GET" action="{{ route('admin.usuarios.usuarios_con_rol') }}" class="flex gap-3 items-center">
                    <div class="flex-1 flex items-center gap-2 border border-slate-200 rounded-lg px-3 py-2.5 bg-white focus-within:ring-2 focus-within:ring-[#39A900]/20 focus-within:border-[#39A900] transition-all">
                        <svg class="w-4 h-4 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por cédula o nombre..."
                               class="flex-1 bg-transparent border-0 text-sm text-slate-800 placeholder-slate-400 focus:ring-0 p-0">
                    </div>
                    <button type="submit" class="px-4 py-2.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-white text-sm font-medium transition-colors">
                        Buscar
                    </button>
                </form>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50">
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Usuario</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Rol principal</th>
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Roles asignados</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($usuarios as $u)
                        @php
                            $nombre = $u->person?->nombre_completo ?: $u->email;
                            $rolesParaQuitar = $u->roles->map(function ($r) {
                                return [
                                    'name' => $r->name,
                                    'label' => ucfirst(str_replace('_', ' ', $r->name)),
                                ];
                            })->values();
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $u->numero_documento }}</div>
                                <div class="text-slate-500 text-xs mt-0.5">{{ $nombre }}</div>
                            </td>
                            <td class="px-4 py-3 align-top">
                                @php
                                    $pName = $primaryRoleNamesByUserId[$u->id] ?? null;
                                @endphp
                                @if($pName)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-800 text-white border border-slate-700">
                                        {{ ucfirst(str_replace('_', ' ', $pName)) }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 align-top">
                                <div class="flex flex-col gap-1.5">
                                    @foreach($u->roles as $r)
                                        @php
                                            $isPrimary = $pName && $r->name === $pName;
                                        @endphp
                                        <div>
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $isPrimary ? 'bg-[#39A900]/15 text-[#1f6b00] border border-[#39A900]/30' : 'bg-[#39A900]/10 text-[#2d8500] border border-[#39A900]/20' }}">
                                                {{ ucfirst(str_replace('_', ' ', $r->name)) }}
                                                @if($isPrimary)
                                                    <span class="text-[10px] font-semibold opacity-80">(principal)</span>
                                                @endif
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="inline-flex items-center justify-end gap-1" role="group" aria-label="Acciones de roles">
                                    @can('usuarios.asignar_rol')
                                    <button type="button"
                                            title="Agregar rol"
                                            aria-label="Agregar rol a este usuario"
                                            @click="openAgregarRol({{ $u->id }}, @js($nombre))"
                                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-[#39A900] bg-[#39A900]/10 hover:bg-[#39A900]/20 border border-[#39A900]/25 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-[#39A900]/40">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                    </button>
                                    @endcan
                                    @can('usuarios.revocar_rol')
                                    <button type="button"
                                            title="Quitar rol"
                                            aria-label="Quitar un rol a este usuario"
                                            @click="openQuitarRol({{ $u->id }}, @js($nombre), @js($rolesParaQuitar->values()->all()), @js(route('admin.usuarios.revocar_rol', $u->id)))"
                                            class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-red-700 bg-red-50 hover:bg-red-100 border border-red-200 transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-red-300">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.75" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center gap-2 text-slate-500">
                                    <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    <p>No hay usuarios con rol asignado.</p>
                                    <a href="{{ route('admin.usuarios.asignar_roles') }}" class="text-sm text-[#39A900] hover:underline font-medium">Asignar primer rol a un usuario</a>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($usuarios->hasPages())
            <div class="px-4 py-3 border-t border-slate-100 bg-slate-50/30">
                {{ $usuarios->withQueryString()->links() }}
            </div>
            @endif
        </div>

        @can('usuarios.asignar_rol')
        {{-- Modal Agregar rol --}}
        <div x-show="modalAgregar" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalAgregar" @click.self="modalAgregar = false" class="fixed inset-0 bg-black/50 transition-opacity" x-transition></div>
                <div x-show="modalAgregar"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-[#39A900]/10 flex items-center justify-center">
                            <svg class="w-5 h-5 text-[#39A900]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Agregar otro rol</h3>
                            <p class="text-sm text-slate-500" x-text="agregarRolNombre ? agregarRolNombre : 'Seleccione un usuario'"></p>
                        </div>
                    </div>
                    <form action="{{ route('admin.usuarios.asignar_rol_store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="user_id" :value="agregarRolUserId">
                        <input type="hidden" name="_from" value="usuarios_con_rol">
                        <div class="mb-4">
                            <label for="modal_rol" class="block text-sm font-medium text-slate-700 mb-1.5">Rol a agregar</label>
                            <select name="rol" id="modal_rol" required
                                    class="w-full border border-slate-200 rounded-xl px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20 transition-all">
                                <option value="">Seleccionar rol...</option>
                                @foreach($roles as $role)
                                    @if(in_array($role->name, $roleNamesAssignable ?? []))
                                <option value="{{ $role->name }}">{{ ucfirst(str_replace('_', ' ', $role->name)) }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button type="button" @click="modalAgregar = false"
                                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2.5 rounded-xl bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold transition-colors shadow-sm">
                                Agregar rol
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcan

        @can('usuarios.revocar_rol')
        {{-- Modal Quitar rol --}}
        <div x-show="modalQuitar" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-modal="true">
            <div class="flex min-h-full items-center justify-center p-4">
                <div x-show="modalQuitar" @click.self="modalQuitar = false" class="fixed inset-0 bg-black/50 transition-opacity" x-transition></div>
                <div x-show="modalQuitar"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-100">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center border border-red-100">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900">Quitar rol</h3>
                            <p class="text-sm text-slate-500" x-text="quitarRolNombre ? quitarRolNombre : 'Seleccione un usuario'"></p>
                        </div>
                    </div>
                    <form :action="quitarRolFormAction" method="POST">
                        @csrf
                        <div class="mb-3 rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">
                            Si quita el único rol, el usuario volverá a la lista de usuarios sin rol hasta que se le asigne otro.
                        </div>
                        <div class="mb-4">
                            <span class="block text-sm font-medium text-slate-700 mb-2">Rol a quitar</span>
                            <div class="max-h-56 overflow-y-auto rounded-xl border border-slate-200 bg-white divide-y divide-slate-100">
                                <template x-for="(opt, idx) in quitarRolesOpciones" :key="opt.name">
                                    <label class="flex items-center gap-3 px-3.5 py-2.5 cursor-pointer hover:bg-red-50/50 text-sm text-slate-800">
                                        <input type="radio" name="rol" class="text-red-600 border-slate-300 focus:ring-red-500"
                                               :value="opt.name" x-model="quitarRolSeleccionado"
                                               :required="idx === 0 && quitarRolesOpciones.length > 0">
                                        <span x-text="opt.label"></span>
                                    </label>
                                </template>
                            </div>
                            <p x-show="modalQuitar && quitarRolesOpciones.length === 0" class="mt-2 text-sm text-amber-700">
                                No hay roles para mostrar. Cierre e intente de nuevo.
                            </p>
                        </div>
                        <div class="flex gap-3 justify-end">
                            <button type="button" @click="modalQuitar = false"
                                    class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50 transition-colors">
                                Cancelar
                            </button>
                            <button type="submit"
                                    class="px-4 py-2.5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-semibold transition-colors shadow-sm">
                                Quitar rol
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endcan
    </div>

    <script>
        window.usuariosConRol = function usuariosConRol() {
            return {
                modalAgregar: false,
                agregarRolUserId: null,
                agregarRolNombre: '',
                modalQuitar: false,
                quitarRolUserId: null,
                quitarRolNombre: '',
                quitarRolFormAction: '',
                quitarRolesOpciones: [],
                quitarRolSeleccionado: '',
                openAgregarRol(userId, nombre) {
                    this.agregarRolUserId = userId;
                    this.agregarRolNombre = nombre;
                    this.modalAgregar = true;
                },
                openQuitarRol(userId, nombre, rolesOpciones, actionUrl) {
                    this.quitarRolUserId = userId;
                    this.quitarRolNombre = nombre;
                    this.quitarRolFormAction = actionUrl;
                    this.quitarRolesOpciones = Array.isArray(rolesOpciones) ? rolesOpciones : [];
                    this.quitarRolSeleccionado = '';
                    this.modalQuitar = true;
                }
            };
        };
    </script>
</x-app-layout>
