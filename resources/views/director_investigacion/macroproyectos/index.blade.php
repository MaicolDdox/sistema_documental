<x-app-layout>
    <x-slot name="header">Catálogo de Macroproyectos</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Macroproyectos</h1>
            <p class="text-sm text-slate-500 mt-0.5">Gestiona los macroproyectos asociados al grupo de investigación.</p>
        </div>
        <a href="{{ route('director.macroproyectos.create') }}"
           class="sgd-btn-primary px-4 py-2.5 rounded-xl text-sm font-medium flex items-center gap-2 w-max">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
            </svg>
            Nuevo Macroproyecto
        </a>
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
                                <a href="{{ route('director.macroproyectos.edit', $mp) }}"
                                   class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 p-1.5 rounded-md transition-colors" title="Editar">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                @if($mp->projects_count === 0)
                                <form method="POST" action="{{ route('director.macroproyectos.destroy', $mp) }}" class="inline-block"
                                      onsubmit="return confirm('¿Seguro que deseas eliminar este macroproyecto de forma permanente?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 p-1.5 rounded-md transition-colors" title="Eliminar">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
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
</x-app-layout>
