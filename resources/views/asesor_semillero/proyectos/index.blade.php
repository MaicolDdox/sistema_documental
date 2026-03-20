<x-app-layout>
<x-slot name="header">Proyectos del Semillero</x-slot>

{{-- Acciones de página y Buscador --}}
<div x-data="{ openExport: false }" class="flex flex-wrap items-center gap-3 mb-6">
    {{-- Buscador --}}
    <form method="GET" class="flex gap-3">
        <div class="relative flex-1">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/></svg>
            <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar proyecto..."
                   class="w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
        </div>
        <button type="submit" class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50 flex-shrink-0">Filtrar</button>
        @if(request('buscar'))
            <a href="{{ route('asesor.proyectos.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-50 flex-shrink-0">Limpiar</a>
        @endif
    </form>

    {{-- Botones de acción --}}
    <div class="flex flex-wrap items-center gap-2 flex-shrink-0">
        <button @click="openExport = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-all shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Descargar Reportes
        </button>

        {{-- Modal de Exportación --}}
        <div x-show="openExport" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" x-cloak>
            <div @click.away="openExport = false" class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-slate-100 text-left" x-transition>
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-slate-800">Descargar Reporte de Proyectos</h3>
                    <button type="button" @click="openExport = false" class="text-slate-400 hover:text-red-500">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form method="GET" action="{{ route('asesor.exportar.proyectos') }}">
                    <div class="mb-5">
                        <label class="block text-sm font-medium text-slate-700 mb-1">Rango de tiempo (Opcional)</label>
                        <select name="rango" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 outline-none pr-8">
                            <option value="">Todo el histórico</option>
                            <option value="hoy">El día de hoy</option>
                            <option value="semanal">Esta semana</option>
                            <option value="mensual">Este mes</option>
                            <option value="anual">Este año</option>
                        </select>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="openExport = false" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200">Cancelar</button>
                        <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-[#39A900] hover:bg-[#2b8000] flex items-center gap-1.5 focus:ring-2 focus:ring-offset-2 focus:ring-[#39A900]">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                            Generar PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>

        @can('proyectos.crear_semillero')
            <a href="{{ route('asesor.proyectos.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
               style="background:#39A900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                Nuevo Proyecto
            </a>
        @endcan
    </div>
</div>

@if($proyectos->isEmpty())
<div class="bg-white rounded-xl border border-slate-200 p-10 text-center">
    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
    <p class="text-slate-500 text-sm">No hay proyectos registrados en tu semillero.</p>
    @can('proyectos.crear_semillero')
        <a href="{{ route('asesor.proyectos.create') }}" class="mt-3 inline-block text-sm font-medium" style="color:#39A900">Crear el primer proyecto →</a>
    @endcan
</div>
@else
<div class="bg-white rounded-xl border border-slate-200 overflow-x-auto">
    <table class="w-full text-sm min-w-[1100px]">
        <thead>
            <tr class="border-b border-slate-100 bg-slate-50">
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Nombre del proyecto</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Descripción</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Línea investigación</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Línea tecnológica</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Área temática</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Modalidad</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Tipo investigación</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Financiación</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha inicio</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha fin</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Macroproyecto</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Autores</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Productos</th>
                <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proyectos as $proyecto)
            <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors group">
                {{-- Nombre --}}
                <td class="px-4 py-3">
                    <p class="font-semibold text-slate-900 whitespace-nowrap max-w-[200px] truncate" title="{{ $proyecto->nombre }}">
                        {{ $proyecto->nombre }}
                    </p>
                </td>
                {{-- Descripción --}}
                <td class="px-4 py-3 text-slate-500 text-xs max-w-[160px]">
                    <span title="{{ $proyecto->descripccion }}">{{ Str::limit($proyecto->descripccion, 50) ?? '—' }}</span>
                </td>
                {{-- Línea investigación --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    @if($proyecto->researchLine)
                        <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">{{ $proyecto->researchLine->nombre }}</span>
                    @else <span class="text-slate-400">—</span> @endif
                </td>
                {{-- Línea tecnológica --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    {{ $proyecto->technologicalLine?->nombre ?? '—' }}
                </td>
                {{-- Área temática --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    {{ $proyecto->thematicArea?->nombre ?? '—' }}
                </td>
                {{-- Modalidad --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    @if($proyecto->projectModality)
                        <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">{{ $proyecto->projectModality->nombre }}</span>
                    @else <span class="text-slate-400">—</span> @endif
                </td>
                {{-- Tipo investigación --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    {{ $proyecto->investigationType?->nombre ?? '—' }}
                </td>
                {{-- Tipo financiación --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    @if($proyecto->tipo_financiacion)
                        <span class="px-2 py-0.5 rounded-full bg-teal-50 text-teal-700 capitalize">
                            {{ ucfirst(str_replace('_', ' ', $proyecto->tipo_financiacion)) }}
                        </span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                {{-- Fecha inicio --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap text-slate-600">
                    {{ $proyecto->fecha_inicio ? \Carbon\Carbon::parse($proyecto->fecha_inicio)->format('d/m/Y') : '—' }}
                </td>
                {{-- Fecha fin --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap text-slate-600">
                    {{ $proyecto->fecha_fin ? \Carbon\Carbon::parse($proyecto->fecha_fin)->format('d/m/Y') : '—' }}
                </td>
                {{-- Macroproyecto --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    @if($proyecto->vinculacion_macro_proyecto)
                        @php $macro = $proyecto->macroProjectLinkages?->first() @endphp
                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">
                            {{ $macro?->codigo ?? 'Sí' }}
                        </span>
                    @else
                        <span class="text-slate-400">No</span>
                    @endif
                </td>
                {{-- Autores --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-50 text-purple-700">
                        {{ $proyecto->projectAuthors->count() }} autor(es)
                    </span>
                </td>
                {{-- Productos --}}
                <td class="px-4 py-3 text-xs whitespace-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-50 text-green-700">
                        {{ $proyecto->products->count() }} producto(s)
                    </span>
                </td>
                {{-- Acciones --}}
                <td class="px-4 py-3">
                    <div class="flex items-center gap-1.5 whitespace-nowrap">
                        @can('proyectos.ver_detalle')
                        <a href="{{ route('asesor.proyectos.show', $proyecto->id) }}"
                           class="text-xs px-2 py-1 rounded border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">Ver</a>
                        @endcan
                        @can('proyectos.vincular_integrantes')
                        <a href="{{ route('asesor.proyectos.integrantes', $proyecto->id) }}"
                           class="text-xs px-2 py-1 rounded border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">Integrantes</a>
                        @endcan
                        @can('productos.registrar')
                        <a href="{{ route('asesor.productos.create', ['proyecto_id' => $proyecto->id]) }}"
                           class="text-xs px-2 py-1 rounded text-white transition-all hover:opacity-90" style="background:#39A900">+Producto</a>
                        @endcan
                        @can('proyectos.editar')
                        <a href="{{ route('asesor.proyectos.edit', $proyecto->id) }}"
                           class="text-xs px-2 py-1 rounded bg-blue-600 text-white hover:bg-blue-700 transition-all">Editar</a>
                        @endcan
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($proyectos->hasPages())
<div class="mt-4">{{ $proyectos->links() }}</div>
@endif
@endif
</x-app-layout>
