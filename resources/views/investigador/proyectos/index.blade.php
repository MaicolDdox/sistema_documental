<x-app-layout>
    <x-slot name="header">Mis Proyectos</x-slot>

    <div class="space-y-4"
         x-data="{
            createOpen: false,
            createUrl: '{{ route('investigador.proyectos.create') }}?embedded=1',
            detailOpen: false,
            detailUrl: '',
            editOpen: false,
            editUrl: ''
         }">

        {{-- Toolbar --}}
        <div class="flex items-center justify-between">
            <p class="text-sm text-slate-500">{{ $proyectos->total() }} proyecto(s) registrado(s)</p>
            <button type="button"
               @click="createOpen = true"
               class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Nuevo proyecto
            </button>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ $errors->first('error') }}</div>
        @endif

        {{-- Tabla --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre del proyecto</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Línea</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Productos</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha inicio</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($proyectos as $proyecto)
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800">{{ $proyecto->nombre }}</p>
                            @if($proyecto->vinculacion_macro_proyecto)
                                <span class="text-xs text-[#39A900]">Vinculado a macro-proyecto</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $proyecto->researchLine?->nombre ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">
                                {{ $proyecto->products->count() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $proyecto->fecha_inicio?->format('d/m/Y') ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @if($proyecto->fecha_fin && $proyecto->fecha_fin < now())
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 border border-blue-200 text-blue-700">
                                <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                                Finalizado
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 border border-green-200 text-green-700">
                                <div class="w-1.5 h-1.5 rounded-full bg-green-500"></div>
                                En ejecución
                            </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 justify-end">
                                <button type="button"
                                   @click="detailUrl = '{{ route('investigador.proyectos.show', $proyecto) }}?embedded=1'; detailOpen = true;"
                                   class="text-slate-400 hover:text-[#39A900] transition-colors" title="Ver detalle">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.574-3.007-9.964-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                                <button type="button"
                                   @click="editUrl = '{{ route('investigador.proyectos.edit', $proyecto) }}?embedded=1'; editOpen = true;"
                                   class="text-slate-400 hover:text-[#39A900] transition-colors" title="Editar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-slate-400 text-sm">
                            No tienes proyectos creados aún.
                            <button type="button" @click="createOpen = true" class="text-[#39A900] font-medium">Crea tu primer proyecto</button>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $proyectos->links() }}

        {{-- Modal creación proyecto (iframe con formulario completo) --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[95vh] overflow-hidden border border-slate-200">
                <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-900">Nuevo proyecto</h2>
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

        {{-- Modal detalle proyecto (iframe con vista completa) --}}
        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-5xl w-full max-h-[95vh] overflow-hidden border border-slate-200">
                <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-900">Detalle del proyecto</h2>
                    <button type="button" @click="detailOpen = false" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors" aria-label="Cerrar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="w-full h-[80vh]">
                    <iframe
                        :src="detailUrl"
                        class="w-full h-full border-0"
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>

        {{-- Modal editar proyecto (iframe con formulario completo) --}}
        <div x-show="editOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-4xl w-full max-h-[95vh] overflow-hidden border border-slate-200">
                <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-900">Editar proyecto</h2>
                    <button type="button" @click="editOpen = false" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors" aria-label="Cerrar">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="w-full h-[80vh]">
                    <iframe
                        :src="editUrl"
                        class="w-full h-full border-0"
                        loading="lazy">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
