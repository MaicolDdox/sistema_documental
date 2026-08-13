<x-app-layout>
    <x-slot name="header">Usuarios del sistema</x-slot>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Usuarios del sistema</h2>
            <p class="text-sm text-slate-500 mt-1">Todos los usuarios registrados excepto administradores del sistema.</p>
        </div>
        <a href="{{ route('super-admin.usuarios-sistema.create') }}"
           class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nuevo usuario
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
            {{ session('error') }}
        </div>
    @endif

    {{-- Filtros --}}
    <form method="GET" class="mb-4 flex flex-wrap gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Buscar por nombre, documento o correo..."
               class="flex-1 min-w-48 border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">

        <select name="rol"
                class="border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
            <option value="">Todos los roles</option>
            @foreach($roles as $rol)
                <option value="{{ $rol->name }}" {{ request('rol') === $rol->name ? 'selected' : '' }}>
                    {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                </option>
            @endforeach
        </select>

        <select name="estado"
                class="border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
            <option value="">Todos los estados</option>
            @foreach($estados as $estado)
                <option value="{{ $estado->value }}" {{ request('estado') === $estado->value ? 'selected' : '' }}>
                    {{ ucfirst($estado->value) }}
                </option>
            @endforeach
        </select>

        <button type="submit"
                class="sgd-btn-primary px-4 py-2.5 rounded-lg text-sm font-semibold">
            Buscar
        </button>
        @if(request('search') || request('rol') || request('estado'))
            <a href="{{ route('super-admin.usuarios-sistema.index') }}"
               class="border border-slate-200 bg-white px-4 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 transition-all">
                Limpiar
            </a>
        @endif
    </form>

    <div class="sgd-table-card bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="sgd-table text-sm">
                <thead>
                    <tr>
                        <th class="text-left">Usuario</th>
                        <th class="text-left">Documento</th>
                        <th class="text-left">Correo</th>
                        <th class="text-left">Rol</th>
                        <th class="text-left">Centro de formación</th>
                        <th class="text-left">Estado</th>
                        <th class="text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-100 text-xs font-semibold text-green-800">
                                    {{ $usuario->initials() }}
                                </span>
                                <span class="font-medium text-slate-900">
                                    {{ $usuario->person?->nombre_completo ?? $usuario->email }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $usuario->numero_documento }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $usuario->email }}</td>
                        <td class="px-4 py-3">
                            @foreach($usuario->roles as $rol)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-slate-100 text-slate-700">
                                    {{ ucfirst(str_replace('_', ' ', $rol->name)) }}
                                </span>
                            @endforeach
                            @if($usuario->roles->isEmpty())
                                <span class="text-slate-400 text-xs">Sin rol</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $usuario->trainingCenter?->nombre ?? '—' }}
                        </td>
                        <td class="px-4 py-3">
                            @if($usuario->estado?->value === 'activo')
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <form method="POST" action="{{ route('super-admin.usuarios-sistema.toggle_estado', $usuario->id) }}">
                                    @csrf
                                    <button type="submit"
                                            class="text-xs font-medium px-3 py-1.5 rounded-lg border transition-all
                                                   {{ $usuario->estado?->value === 'activo'
                                                      ? 'border-red-200 text-red-700 hover:bg-red-50'
                                                      : 'border-green-200 text-green-700 hover:bg-green-50' }}">
                                        {{ $usuario->estado?->value === 'activo' ? 'Desactivar' : 'Activar' }}
                                    </button>
                                </form>
                                @if(auth()->id() !== $usuario->id)
                                <form method="POST" action="{{ route('super-admin.usuarios-sistema.destroy', $usuario->id) }}"
                                      onsubmit="return confirm('¿Eliminar a {{ addslashes($usuario->person?->nombre_completo ?? $usuario->email) }}? Esta acción no se puede deshacer.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-xs font-medium px-3 py-1.5 rounded-lg border border-slate-200 text-slate-500 hover:bg-red-50 hover:text-red-700 hover:border-red-200 transition-all">
                                        Eliminar
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-slate-500 text-sm">
                            No hay usuarios registrados con los filtros aplicados.
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

    <div class="mt-4">
        <a href="{{ route('super-admin.dashboard') }}"
           class="text-sm text-slate-500 hover:text-slate-800 transition-colors">
            ← Volver al dashboard
        </a>
    </div>
</x-app-layout>
