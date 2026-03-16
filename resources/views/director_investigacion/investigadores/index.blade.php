<x-app-layout>
    <x-slot name="header">Investigadores del Grupo</x-slot>

    <div x-data="{ createOpen: false, createUrl: '{{ route('director.investigadores.create') }}?embedded=1' }">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Investigadores</h1>
                <p class="text-sm text-slate-500 mt-0.5">Miembros del grupo de investigación</p>
            </div>
            <button type="button"
               @click="createOpen = true"
               class="sgd-btn-primary px-4 py-2.5 rounded-xl text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nuevo Investigador
            </button>
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
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                {{-- Activar / desactivar --}}
                                <form method="POST" action="{{ route('director.investigadores.toggle-estado', $inv) }}">
                                    @csrf @method('PATCH')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-slate-200 bg-slate-50 text-slate-600 hover:bg-slate-100 hover:text-slate-900 transition-all"
                                            title="{{ ($inv?->estado?->value ?? '') === 'activo' ? 'Desactivar investigador' : 'Activar investigador' }}">
                                        @if(($inv?->estado?->value ?? '') === 'activo')
                                            {{-- Icono apagar --}}
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v9m6.364-6.364A9 9 0 1112 4.5"/>
                                            </svg>
                                        @else
                                            {{-- Icono encender --}}
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 5.25v13.5m6.364-9.75A9 9 0 115.636 9"/>
                                            </svg>
                                        @endif
                                    </button>
                                </form>

                                {{-- Desvincular --}}
                                <form method="POST" action="{{ route('director.investigadores.desvincular', $inv) }}"
                                      onsubmit="return confirm('¿Desvincular este investigador del grupo?')">
                                    @csrf @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center w-9 h-9 rounded-full border border-red-200 bg-red-50 text-red-500 hover:bg-red-100 hover:text-red-700 transition-all"
                                            title="Desvincular investigador">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                                        </svg>
                                    </button>
                                </form>
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

        {{-- Modal creación investigador (iframe con formulario completo) --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[95vh] overflow-hidden border border-slate-200">
                <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-900">Nuevo Investigador</h2>
                    <button type="button" @click="createOpen = false" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors" aria-label="Cerrar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="w-full h-[80vh]">
                    <iframe
                        :src="createUrl"
                        class="w-full h-full border-0"
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
