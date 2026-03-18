<x-app-layout>
<div x-data="{
    createOpen: false,
    createUrl: '{{ route('admin.research-groups.create') }}?embedded=1',
    editOpen: false,
    editUrl: ''
}">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Grupos de Investigación</h1>
            <p class="text-sm text-slate-500 mt-0.5">Administra los grupos y su responsable.</p>
        </div>
        <button type="button"
                @click="createOpen = true"
                class="sgd-btn-primary px-4 py-2.5 rounded-xl text-sm font-medium">
            + Nuevo grupo
        </button>
    </div>

@if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
        {{ session('success') }}
    </div>
@endif

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden sgd-card">
    <table class="w-full text-sm">
        <thead class="bg-slate-50 border-b border-slate-100">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Centro</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Responsable</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse($grupos as $grupo)
            @php
                // Buscar primero por rol 'director', luego 'investigador_lider', y si no hay, tomar el primero
                $responsable = $grupo->users->firstWhere('pivot.rol', 'director')
                    ?? $grupo->users->firstWhere('pivot.rol', 'investigador_lider')
                    ?? $grupo->users->first();
                // Nombre legible: persona.nombre_completo o email
                $responsableNombre = $responsable?->person?->nombre_completo
                    ?? $responsable?->email
                    ?? null;
            @endphp
                <tr>
                    <td class="px-4 py-3">
                        <div class="font-medium text-slate-900">{{ $grupo->nombre }}</div>
                        @if($grupo->codigo)
                            <div class="text-xs text-slate-400 mt-0.5">Código: {{ $grupo->codigo }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        {{ $grupo->trainingCenter?->nombre ?? '—' }}
                    </td>
                    <td class="px-4 py-3 text-slate-700">
                        {{ $responsableNombre ?? 'Sin asignar' }}
                    </td>
                    <td class="px-4 py-3">
                        @if($grupo->estado === \App\Enums\EstadoEnum::Activo)
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700">
                                <span class="w-2 h-2 rounded-full bg-green-500"></span> Activo
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-600">
                                <span class="w-2 h-2 rounded-full bg-slate-400"></span> Inactivo
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="relative inline-flex justify-end w-full" x-data="{ open: false }">
                            <button type="button"
                                    @click.stop="open = !open"
                                    @keydown.escape.window="open = false"
                                    class="inline-flex items-center justify-center rounded-full p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                    aria-haspopup="true"
                                    :aria-expanded="open ? 'true' : 'false'">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </button>

                            <div x-show="open"
                                 x-cloak
                                 @click.away="open = false"
                                 class="absolute right-0 mt-2 w-44 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                <button type="button"
                                        @click="open = false; editUrl = '{{ route('admin.research-groups.edit', $grupo) }}?embedded=1'; editOpen = true;"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.688-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                    <span>Editar</span>
                                </button>

                                <form action="{{ route('admin.research-groups.destroy', $grupo) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button"
                                            @click="open = false; if (confirm('¿Eliminar este grupo de investigación?')) { $el.closest('form').submit(); }"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-left text-xs text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7H5m3 0V4a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v3m-1 0v12a2 2 0 0 1-2 2H10a2 2 0 0 1-2-2V7h8Z" />
                                        </svg>
                                        <span>Eliminar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-10 text-center text-slate-400 text-sm">
                        No hay grupos de investigación registrados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div class="mt-4">
        {{ $grupos->links() }}
    </div>

    @if(request()->boolean('embedded') && session('success'))
        <script>
            if (window.parent && window.parent !== window) {
                window.parent.location.reload();
            }
        </script>
    @endif

    {{-- Modal crear grupo (iframe) --}}
    <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-sm font-semibold text-slate-900">Nuevo grupo de investigación</h2>
                <button type="button" @click="createOpen = false" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500" aria-label="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="w-full h-[80vh]">
                <iframe :src="createUrl" class="w-full h-full border-0" loading="lazy"></iframe>
            </div>
        </div>
    </div>

    {{-- Modal editar grupo (iframe) --}}
    <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
        <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden border border-slate-200">
            <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
                <h2 class="text-sm font-semibold text-slate-900">Editar grupo de investigación</h2>
                <button type="button" @click="editOpen = false" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500" aria-label="Cerrar">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="w-full h-[80vh]">
                <iframe :src="editUrl" class="w-full h-full border-0" loading="lazy"></iframe>
            </div>
        </div>
    </div>
</div>
</x-app-layout>


