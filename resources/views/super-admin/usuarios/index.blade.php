<x-app-layout>
    <x-slot name="header">Administradores del sistema</x-slot>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">Administradores del sistema</h2>
            <p class="text-sm text-slate-500 mt-1">Solo el super administrador puede crear y gestionar este rol.</p>
        </div>
        <a href="{{ route('super-admin.administradores.create') }}"
           class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nuevo administrador
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
    <form method="GET" class="mb-4 flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}"
               placeholder="Buscar por nombre, documento o correo..."
               class="flex-1 border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
        <button type="submit"
                class="sgd-btn-primary px-4 py-2.5 rounded-lg text-sm font-semibold">
            Buscar
        </button>
        @if(request('search'))
            <a href="{{ route('super-admin.administradores.index') }}"
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
                        <th class="text-left">Administrador</th>
                        <th class="text-left">Documento</th>
                        <th class="text-left">Correo</th>
                        <th class="text-left">Centro asignado</th>
                        <th class="text-left">Estado</th>
                        <th class="text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($usuarios as $usuario)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-xs font-semibold text-amber-800">
                                    {{ $usuario->initials() }}
                                </span>
                                <span class="font-medium text-slate-900">
                                    {{ $usuario->person?->nombre_completo ?? $usuario->email }}
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $usuario->numero_documento }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $usuario->email }}</td>
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
                            <form method="POST" action="{{ route('super-admin.administradores.toggle_estado', $usuario->id) }}">
                                @csrf
                                <button type="submit"
                                        class="text-xs font-medium px-3 py-1.5 rounded-lg border transition-all
                                               {{ $usuario->estado?->value === 'activo'
                                                  ? 'border-red-200 text-red-700 hover:bg-red-50'
                                                  : 'border-green-200 text-green-700 hover:bg-green-50' }}">
                                    {{ $usuario->estado?->value === 'activo' ? 'Desactivar' : 'Activar' }}
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-slate-500 text-sm">
                            No hay administradores del sistema registrados.
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
