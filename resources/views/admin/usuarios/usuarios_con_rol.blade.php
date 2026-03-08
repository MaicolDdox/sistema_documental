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

    <div x-data="usuariosConRol()" class="space-y-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-semibold text-slate-900">Usuarios con rol asignado</h2>
                <p class="text-sm text-slate-500 mt-1">Listado de usuarios que ya tienen al menos un rol. Puedes agregarles otro rol desde aquí.</p>
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
                            <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Roles actuales</th>
                            <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($usuarios as $u)
                        @php
                            $nombre = $u->person?->nombre_completo ?: $u->email;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="px-4 py-3">
                                <div class="font-medium text-slate-900">{{ $u->numero_documento }}</div>
                                <div class="text-slate-500 text-xs mt-0.5">{{ $nombre }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($u->roles as $r)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-[#39A900]/10 text-[#2d8500] border border-[#39A900]/20">
                                        {{ ucfirst(str_replace('_', ' ', $r->name)) }}
                                    </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <button type="button"
                                        @click="openAgregarRol({{ $u->id }}, '{{ addslashes($nombre) }}')"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium text-[#39A900] bg-[#39A900]/10 hover:bg-[#39A900]/20 border border-[#39A900]/20 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    Agregar rol
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-12 text-center">
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
    </div>

    <script>
        function usuariosConRol() {
            return {
                modalAgregar: false,
                agregarRolUserId: null,
                agregarRolNombre: '',
                openAgregarRol(userId, nombre) {
                    this.agregarRolUserId = userId;
                    this.agregarRolNombre = nombre;
                    this.modalAgregar = true;
                }
            };
        }
    </script>
</x-app-layout>
