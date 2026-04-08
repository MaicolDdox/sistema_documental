<x-app-layout>
    <x-slot name="header">Catálogo de Macroproyectos</x-slot>

    <div x-data="{
            createOpen: false,
            createUrl: '{{ route('director.macroproyectos.create') }}?embedded=1',
            detailOpen: false,
            detailUrl: ''
        }">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Macroproyectos</h1>
                <p class="text-sm text-slate-500 mt-0.5">Gestiona los macroproyectos asociados al grupo de investigación.</p>
            </div>
            <button type="button"
               @click="createOpen = true"
               class="sgd-btn-primary px-4 py-2.5 rounded-xl text-sm font-medium flex items-center gap-2 w-max">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                </svg>
                Nuevo Macroproyecto
            </button>
        </div>

    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-green-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ session('error') }}
        </div>
    @endif

    {{-- Filtros --}}
    <form method="GET" action="{{ route('director.macroproyectos.index') }}" class="mb-6 flex flex-col sm:flex-row gap-3">
        <div class="flex-1 relative">
            <svg class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar por código o nombre..."
                   class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] transition-all">
        </div>
        <select name="estado" class="w-full sm:w-40 py-2 px-3 text-sm border border-slate-200 rounded-lg focus:ring-2 focus:ring-[#39A900]/20 focus:border-[#39A900] transition-all">
            <option value="">Todos los estados</option>
            <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activos</option>
            <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivos</option>
        </select>
        <button class="sgd-btn-secondary px-5 py-2 rounded-lg text-sm whitespace-nowrap">Aplicar Filtros</button>
        @if(request()->hasAny(['buscar', 'estado']))
            <a href="{{ route('director.macroproyectos.index') }}" class="px-3 py-2 text-sm text-slate-500 hover:text-red-500 font-medium whitespace-nowrap">Limpiar</a>
        @endif
    </form>

    {{-- Tabla --}}
    <div class="sgd-table-card bg-white overflow-hidden rounded-xl border border-slate-200">
        <div class="overflow-x-auto">
            <table class="sgd-table w-full text-sm text-left">
                <thead class="bg-slate-50 text-slate-500 font-medium">
                    <tr>
                        <th class="px-5 py-3 border-b border-slate-200">Código</th>
                        <th class="px-5 py-3 border-b border-slate-200">Nombre del Macroproyecto</th>
                        <th class="px-5 py-3 border-b border-slate-200">Proyectos vinculados</th>
                        <th class="px-5 py-3 border-b border-slate-200">Estado</th>
                        <th class="px-5 py-3 border-b border-slate-200 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($macroproyectos as $mp)
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-5 py-4 font-mono text-xs text-slate-600">{{ $mp->codigo }}</td>
                        <td class="px-5 py-4 font-medium text-slate-900">{{ $mp->nombre }}</td>
                        <td class="px-5 py-4 text-slate-600">{{ $mp->projects_count }} proyecto(s)</td>
                        <td class="px-5 py-4">
                            @if($mp->estado->value === 'activo')
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-green-50 text-green-700 border border-green-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Activo
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Inactivo
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                {{-- Detalles (modal) --}}
                                <button type="button"
                                   @click="detailUrl = '{{ route('director.macroproyectos.edit', $mp) }}?embedded=1'; detailOpen = true;"
                                   class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-50 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition"
                                   title="Detalles">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.651-1.651a1.875 1.875 0 112.653 2.653L10.582 16.072a4.5 4.5 0 01-1.897 1.13L6 18l.798-2.685a4.5 4.5 0 011.13-1.897l8.934-8.931z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 19.5h15" />
                                    </svg>
                                </button>

                                {{-- Activar / Desactivar --}}
                                @if($mp->estado->value === 'activo')
                                    <form method="POST"
                                          action="{{ route('director.macroproyectos.desactivar', $mp) }}"
                                          class="inline-block"
                                          onsubmit="return confirm('¿Desactivar este macroproyecto?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-50 text-amber-700 hover:bg-amber-100 hover:text-amber-900 transition"
                                                title="Desactivar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 7.5v9m10.5-9v9" />
                                            </svg>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST"
                                          action="{{ route('director.macroproyectos.activar', $mp) }}"
                                          class="inline-block"
                                          onsubmit="return confirm('¿Activar este macroproyecto?');">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:text-emerald-900 transition"
                                                title="Activar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 15.75l7.5-7.5m0 0H9.75m6 0v6" />
                                            </svg>
                                        </button>
                                    </form>
                                @endif

                                {{-- Eliminar (solo sin proyectos vinculados) --}}
                                @if($mp->projects_count === 0)
                                    <form method="POST" action="{{ route('director.macroproyectos.destroy', $mp) }}" class="inline-block"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este macroproyecto de forma permanente?');">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-50 text-red-600 hover:bg-red-100 hover:text-red-700 transition"
                                                title="Eliminar">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center text-slate-500">
                            <svg class="w-10 h-10 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3v11.25A2.25 2.25 0 006 16.5h2.25M3.75 3h-1.5m1.5 0h16.5m0 0h1.5m-1.5 0v11.25A2.25 2.25 0 0118 16.5h-2.25m-7.5 0h7.5m-7.5 0l-1 3m8.5-3l1 3m0 0l.5 1.5m-.5-1.5h-9.5m0 0l-.5 1.5m.75-9l3-3 2.148 2.148A12.061 12.061 0 0116.5 7.605"/>
                            </svg>
                            <p>No se encontraron macroproyectos.</p>
                            @if(!request()->hasAny(['buscar', 'estado']))
                            <a href="{{ route('director.macroproyectos.create') }}" class="text-[#39A900] hover:underline text-sm font-medium mt-2 inline-block">
                                Crear el primer macroproyecto →
                            </a>
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($macroproyectos->hasPages())
        <div class="px-5 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $macroproyectos->links() }}
        </div>
        @endif
    </div>

        {{-- Modal creación de macroproyecto (iframe con formulario completo) --}}
        <div x-show="createOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[95vh] overflow-hidden border border-slate-200">
                <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-900">Nuevo Macroproyecto</h2>
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

        {{-- Modal detalles / edición de macroproyecto --}}
        <div x-show="detailOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50" aria-modal="true">
            <div class="relative bg-white rounded-2xl shadow-2xl max-w-3xl w-full max-h-[95vh] overflow-hidden border border-slate-200">
                <div class="flex items-center justify-between px-6 py-3 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-sm font-semibold text-slate-900">Detalles del Macroproyecto</h2>
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
    </div>
</x-app-layout>
