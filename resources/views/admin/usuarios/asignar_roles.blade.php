<x-app-layout>
    <x-slot name="header">Asignación de Roles</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Administración</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Roles y Permisos</span>
    </nav>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Asignación de Roles</h2>
        <p class="text-sm text-slate-500 mt-1">
            Gestiona los roles del sistema. Con varios roles, el <strong>rol principal</strong> mostrado es el que tiene prioridad al iniciar sesión (mismo criterio que la redirección del login). Los demás permiten acceder a otros módulos desde <a href="{{ route('admin.usuarios.usuarios_con_rol') }}" class="text-[#39A900] hover:underline font-medium">Usuarios con rol</a> o desde el menú lateral.
        </p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Formulario: Asignar rol a usuario --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 mb-4">Asignar Rol a Usuario</h3>
            <form method="POST" action="{{ route('admin.usuarios.asignar_rol_store') }}" class="space-y-5">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                    <div>
                        <label for="user_id" class="block text-xs font-medium text-slate-600 mb-1.5">Usuario</label>
                        <select name="user_id" id="user_id" required
                                class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20 transition-all">
                            <option value="">Seleccionar usuario...</option>
                            @foreach($usuarios as $u)
                                @php
                                    $nombre = $u->person?->nombre_completo ?: $u->email;
                                @endphp
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->numero_documento }} ({{ $nombre }})
                                </option>
                            @endforeach
                        </select>
                        @error('user_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="flex items-center gap-2 sm:gap-3">
                        <span class="hidden sm:inline text-slate-400" aria-hidden="true">→</span>
                        <div class="flex-1">
                            <label for="rol" class="block text-xs font-medium text-slate-600 mb-1.5">Rol a asignar</label>
                            <select name="rol" id="rol" required
                                    class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20 transition-all">
                                <option value="">Seleccionar rol...</option>
                                @foreach($roles as $role)
                                    @if(in_array($role->name, $roleNamesAssignable ?? []))
                                        <option value="{{ $role->name }}" {{ old('rol') == $role->name ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('rol')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3 pt-2">
                    <a href="{{ route('admin.usuarios.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50 transition-colors">
                        Cancelar
                    </a>
                    <button type="submit" class="px-4 py-2.5 rounded-lg bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold transition-colors shadow-sm">
                        Guardar asignación
                    </button>
                </div>
            </form>
        </div>

        {{-- Listado: Roles en el sistema — chulito verde si el usuario puede asignar ese rol --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 mb-4">Roles en el sistema</h3>
            <ul class="space-y-2.5">
                @foreach($roles as $role)
                    @php $puedeAsignar = in_array($role->name, $roleNamesAssignable ?? []); @endphp
                    <li class="flex flex-wrap items-center gap-2">
                        @if($puedeAsignar)
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-[#39A900]/15 text-[#39A900]" title="Puede asignar este rol">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            </span>
                        @else
                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-slate-100 text-slate-400" title="No puede asignar este rol">
                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                            </span>
                        @endif
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                        </span>
                        <span class="text-xs text-slate-500">
                            can {{ $role->permissions->first()?->name ?? '—' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-app-layout>
