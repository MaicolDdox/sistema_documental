<x-app-layout>
<x-slot name="header">Proyectos del Semillero</x-slot>

{{-- Acciones de página --}}
<div class="flex items-center justify-between mb-6">
    <div></div>
    <div>
<div x-data="{ openExport: false }" class="flex items-center gap-2">
    <button @click="openExport = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-all shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
        Descargar Reportes
    </button>

    {{-- Modal de Exportación --}}
    <div x-show="openExport" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div @click.away="openExport = false" class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-slate-100 text-left" x-transition>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800">Descargar Reporte de Proyectos</h3>
                <button @click="openExport = false" class="text-slate-400 hover:text-red-500">
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
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
           style="background:#39A900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nuevo Proyecto
        </a>
    @endcan
</div>
    </div>
</div>

<form method="GET" class="mb-4 flex gap-3">
    <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 15.803 7.5 7.5 0 0016.803 15.803z"/></svg>
        <input type="text" name="buscar" value="{{ request('buscar') }}" placeholder="Buscar proyecto..."
               class="w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-lg text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
    </div>
    <button type="submit" class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-700 hover:bg-slate-50">Filtrar</button>
    @if(request('buscar'))
        <a href="{{ route('asesor.proyectos.index') }}" class="px-4 py-2.5 bg-white border border-slate-200 rounded-lg text-sm font-medium text-slate-500 hover:bg-slate-50">Limpiar</a>
    @endif
</form>

@if($proyectos->isEmpty())
<div class="bg-white rounded-xl border border-slate-200 p-10 text-center">
    <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
    <p class="text-slate-500 text-sm">No hay proyectos registrados en tu semillero.</p>
        <a href="{{ route('asesor.proyectos.create') }}" class="mt-3 inline-block text-sm font-medium" style="color:#39A900">Crear el primer proyecto →</a>
</div>
@else
<div class="sgd-table-card bg-white overflow-x-auto rounded-xl border border-slate-200/80 shadow-sm">
    <table class="sgd-table projects-table min-w-[850px] table-fixed">
        <thead>
            <tr>
                <th class="w-[32%] text-left py-3.5 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Proyecto</th>
                <th class="w-[24%] text-left py-3.5 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Línea investigación</th>
                <th class="w-[16%] text-left py-3.5 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Modalidad</th>
                <th class="w-[12%] text-left py-3.5 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                <th class="w-[18%] text-left py-3.5 px-4 text-xs font-semibold text-slate-500 uppercase tracking-wide">Fecha inicio</th>
                <th class="w-[10%] text-right py-3.5 px-4 pr-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acciones</th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-slate-100">
            @foreach($proyectos as $proyecto)
            <tr class="hover:bg-emerald-50/50 transition-colors align-middle">
                {{-- Proyecto: nombre + descripción corta --}}
                <td class="px-4 py-3 align-middle">
                    <p class="font-semibold text-slate-900 truncate" title="{{ $proyecto->nombre }}">
                        {{ $proyecto->nombre }}
                    </p>
                    @if($proyecto->descripccion)
                        <p class="text-xs text-slate-500 mt-0.5 line-clamp-2">
                            {{ Str::limit($proyecto->descripccion, 80) }}
                        </p>
                    @endif
                </td>
                {{-- Línea investigación --}}
                <td class="px-4 py-3 align-middle text-xs whitespace-nowrap">
                    @if($proyecto->researchLine)
                        <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">{{ $proyecto->researchLine->nombre }}</span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                {{-- Modalidad --}}
                <td class="px-4 py-3 align-middle text-xs whitespace-nowrap">
                    @if($proyecto->projectModality)
                        <span class="px-2 py-0.5 rounded-full bg-blue-50 text-blue-700">{{ $proyecto->projectModality->nombre }}</span>
                    @else
                        <span class="text-slate-400">—</span>
                    @endif
                </td>
                {{-- Estado (calculado con fechas) --}}
                <td class="px-4 py-3 align-middle text-xs whitespace-nowrap">
                    @php
                        $hoy = \Carbon\Carbon::today();
                        $inicio = $proyecto->fecha_inicio ? \Carbon\Carbon::parse($proyecto->fecha_inicio) : null;
                        $fin = $proyecto->fecha_fin ? \Carbon\Carbon::parse($proyecto->fecha_fin) : null;
                        $estado = 'Sin fecha';
                        $badgeClasses = 'bg-slate-100 text-slate-700';
                        if ($inicio) {
                            if ($inicio->isFuture()) { $estado = 'Programado'; $badgeClasses = 'bg-amber-50 text-amber-700'; }
                            elseif ($fin && $fin->isPast()) { $estado = 'Finalizado'; $badgeClasses = 'bg-emerald-50 text-emerald-700'; }
                            else { $estado = 'En curso'; $badgeClasses = 'bg-sky-50 text-sky-700'; }
                        }
                    @endphp
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClasses }}">
                        {{ $estado }}
                    </span>
                </td>
                {{-- Fecha inicio --}}
                <td class="px-4 py-3 align-middle text-xs whitespace-nowrap text-slate-600">
                    {{ $proyecto->fecha_inicio ? \Carbon\Carbon::parse($proyecto->fecha_inicio)->format('d/m/Y') : '—' }}
                </td>
                {{-- Acciones --}}
                <td class="px-3 py-3 align-middle text-right">
                    <div class="inline-flex items-center justify-end gap-1.5 whitespace-nowrap">
                        {{-- Detalle --}}
                        <button type="button"
                                @click="showDetailId = {{ $proyecto->id }}"
                                class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-600 hover:bg-slate-200 hover:text-slate-900 transition-all"
                                title="Ver detalles">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12s2.25-6.75 9.75-6.75S21.75 12 21.75 12 19.5 18.75 12 18.75 2.25 12 2.25 12z" />
                                <circle cx="12" cy="12" r="3.25" />
                            </svg>
                        </button>
                        {{-- Eliminar inmediato --}}
                        <form method="POST" action="{{ route('asesor.proyectos.destroy', $proyecto->id) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="inline-flex items-center justify-center w-7 h-7 rounded-full border border-red-200 text-red-500 bg-red-50/60 hover:bg-red-100 transition-all"
                                    title="Eliminar proyecto">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </form>
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
