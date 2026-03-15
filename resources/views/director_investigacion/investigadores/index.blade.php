<x-app-layout>
    <x-slot name="header">Investigadores del Grupo</x-slot>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Investigadores</h1>
            <p class="text-sm text-slate-500 mt-0.5">Miembros del grupo de investigación</p>
        </div>
        <a href="{{ route('director.investigadores.create') }}"
           class="sgd-btn-primary px-4 py-2.5 rounded-xl text-sm font-medium flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nuevo Investigador
        </a>
    </div>

    <div class="sgd-table-card bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="sgd-table text-sm">
                <thead>
                    <tr>
                        <th class="text-left">Investigador</th>
                        <th class="text-left">Correo</th>
                        <th class="text-left">Rol en grupo</th>
                        <th class="text-left">Estado</th>
                        <th class="text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($investigadores as $pivot)
                    @php $inv = $pivot->user; @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#39A900]/20 flex items-center justify-center shrink-0">
                                    <span class="text-xs font-bold text-[#39A900]">
                                        {{ strtoupper(substr($inv?->person?->primer_nombre ?? $inv?->email ?? 'U', 0, 1)) }}{{ strtoupper(substr($inv?->person?->primer_apellido ?? '', 0, 1)) }}
                                    </span>
                                </div>
                                <div>
                                    <p class="font-medium text-slate-800">
                                        {{ $inv?->person?->primer_nombre ?? '—' }} {{ $inv?->person?->primer_apellido ?? '' }}
                                    </p>
                                    <p class="text-xs text-slate-400">{{ $inv?->numero_documento ?? '' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $inv?->email ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="text-xs font-medium text-slate-600 bg-slate-100 px-2 py-1 rounded-md">
                                {{ ucfirst(str_replace('_', ' ', $pivot->rol->value ?? '')) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if(($inv?->estado?->value ?? '') === 'activo')
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Activo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3" x-data="{ open: false }">
                            <div class="relative">
                                <button @click="open = !open" @click.away="open = false"
                                        class="text-slate-400 hover:text-slate-600 p-1 rounded-md hover:bg-slate-100">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z"/>
                                    </svg>
                                </button>
                                <div x-show="open" x-cloak
                                     class="absolute right-0 z-10 mt-1 w-44 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                                    {{-- Cambiar estado --}}
                                    <form method="POST" action="{{ route('director.investigadores.toggle-estado', $inv) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-slate-700 hover:bg-slate-50 flex items-center gap-2">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99"/>
                                            </svg>
                                            {{ ($inv?->estado?->value ?? '') === 'activo' ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    </form>
                                    {{-- Desvincular --}}
                                    <form method="POST" action="{{ route('director.investigadores.desvincular', $inv) }}"
                                          onsubmit="return confirm('¿Desvincular este investigador del grupo?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2 border-t border-slate-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M22 10.5h-6m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
                                            </svg>
                                            Desvincular
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                            <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                            </svg>
                            No hay investigadores vinculados al grupo.
                            <br>
                            <a href="{{ route('director.investigadores.create') }}" class="text-[#39A900] hover:underline text-sm font-medium mt-2 inline-block">
                                Agregar el primero →
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
