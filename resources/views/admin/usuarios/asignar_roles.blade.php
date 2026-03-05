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
            Gestiona los roles del sistema con control @@can por opción.
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
                                <option value="{{ $u->id }}" {{ old('user_id') == $u->id ? 'selected' : '' }}>
                                    {{ $u->person ? $u->person->nombre_completo : $u->email }} ({{ $u->email }})
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
                            <label for="rol" class="block text-xs font-medium text-slate-600 mb-1.5">Rol a asignar (@@can)</label>
                            <select name="rol" id="rol" required
                                    class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/20 transition-all">
                                <option value="">Seleccionar rol...</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ old('rol') == $role->name ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </option>
                                @endforeach
                            </select>
                            @error('rol')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
                <p class="text-xs text-slate-500">
                    @@can: Cada opción corresponde a un permiso concreto en el backend. Selecciona un rol para ver el permiso requerido.
                </p>
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

        {{-- Listado: Roles en el sistema --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm">
            <h3 class="text-sm font-semibold text-slate-800 mb-4">Roles en el sistema</h3>
            <ul class="space-y-2.5">
                @foreach($roles as $role)
                    <li class="flex flex-wrap items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-800 border border-slate-200">
                            {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                        </span>
                        <span class="text-xs text-slate-500">
                            can {{ $role->permissions->first()?->name ?? 'usuarios.asignar_rol' }}
                        </span>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</x-app-layout>
