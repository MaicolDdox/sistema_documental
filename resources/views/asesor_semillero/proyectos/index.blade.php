<x-app-layout>
<div x-data="{ showDetailId: null }">

{{-- Mensaje local de éxito --}}
@if(session('success'))
    <div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

<div class="mb-4 flex justify-end">
    <a href="{{ route('asesor.proyectos.create') }}"
       class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90"
       style="background:#39A900">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
        Nuevo Proyecto
    </a>
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

{{-- Modal detalle proyecto --}}
<div x-show="showDetailId" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" aria-modal="true" x-transition>
    <div class="relative bg-white rounded-2xl shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto border border-slate-200"
         @click.self="showDetailId = null">
        <div class="sticky top-0 bg-white flex items-center justify-between px-6 py-4 border-b border-slate-100 rounded-t-2xl z-10">
            <h2 class="text-base font-semibold text-slate-900">Detalle del proyecto</h2>
            <button type="button" @click="showDetailId = null" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors" aria-label="Cerrar">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="p-6 space-y-5">
            @foreach($proyectos as $proyecto)
            @php
                $macro = $proyecto->vinculacion_macro_proyecto ? $proyecto->macroProjectLinkages?->first() : null;
            @endphp
            <div x-show="showDetailId === {{ $proyecto->id }}" x-cloak class="space-y-5">
                <div>
                    <h3 class="font-outfit font-bold text-xl text-slate-900 mb-1">{{ $proyecto->nombre }}</h3>
                    @if($proyecto->descripccion)
                        <p class="text-sm text-slate-600 mb-4">{{ $proyecto->descripccion }}</p>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Línea de investigación</p>
                            <p class="text-sm font-medium text-slate-700">{{ $proyecto->researchLine?->nombre ?? '—' }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Modalidad</p>
                            <p class="text-sm font-medium text-slate-700">{{ $proyecto->projectModality?->nombre ?? '—' }}</p>
                        </div>
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Tipo de investigación</p>
                            <p class="text-sm font-medium text-slate-700">{{ $proyecto->investigationType?->nombre ?? '—' }}</p>
                        </div>
                        @if($proyecto->technologicalLine)
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Línea tecnológica</p>
                            <p class="text-sm font-medium text-slate-700">{{ $proyecto->technologicalLine->nombre }}</p>
                        </div>
                        @endif
                        @if($proyecto->thematicArea)
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Área temática</p>
                            <p class="text-sm font-medium text-slate-700">{{ $proyecto->thematicArea->nombre }}</p>
                        </div>
                        @endif
                        <div class="bg-slate-50 rounded-lg p-3">
                            <p class="text-xs text-slate-400 mb-0.5">Fechas</p>
                            <p class="text-sm font-medium text-slate-700">
                                {{ $proyecto->fecha_inicio ? \Carbon\Carbon::parse($proyecto->fecha_inicio)->format('d/m/Y') : '—' }}
                                @if($proyecto->fecha_fin)
                                    → {{ \Carbon\Carbon::parse($proyecto->fecha_fin)->format('d/m/Y') }}
                                @endif
                            </p>
                        </div>
                    </div>
                    @if($macro)
                    <div class="mt-4 bg-blue-50 border border-blue-100 rounded-lg p-3">
                        <p class="text-xs font-semibold text-blue-700 mb-1">Macroproyecto vinculado</p>
                        <p class="text-sm text-blue-800">{{ $macro->nombre }}</p>
                        <p class="text-xs text-blue-600">Código: {{ $macro->codigo }}</p>
                    </div>
                    @endif
                </div>
                <div class="bg-white border border-slate-200 rounded-xl p-4">
                    <h4 class="text-sm font-semibold text-slate-900 mb-3">Autores y productos</h4>
                    <div class="flex flex-wrap gap-2">
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-purple-50 text-purple-700 text-xs font-medium">
                            {{ $proyecto->projectAuthors->count() }} autor(es)
                        </span>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-green-50 text-green-700 text-xs font-medium">
                            {{ $proyecto->products->count() }} producto(s)
                        </span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

</div>

</x-app-layout>
