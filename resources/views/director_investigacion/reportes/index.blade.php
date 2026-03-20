<x-app-layout>
    <x-slot name="header">Reportes del Grupo</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Reportes del Grupo</h1>
            <p class="text-sm text-slate-500 mt-0.5">Estadísticas de producción académica</p>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('director.reportes.index') }}"
          class="bg-white rounded-xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-end shadow-sm">
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Estado de revisión</label>
            <select name="estado_revision"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                <option value="">Todos</option>
                <option value="pendiente"   {{ request('estado_revision') === 'pendiente'   ? 'selected' : '' }}>Pendiente</option>
                <option value="en_revision" {{ request('estado_revision') === 'en_revision' ? 'selected' : '' }}>En revisión</option>
                <option value="aprobado"    {{ request('estado_revision') === 'aprobado'    ? 'selected' : '' }}>Aprobado</option>
                <option value="rechazado"   {{ request('estado_revision') === 'rechazado'   ? 'selected' : '' }}>Rechazado</option>
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Año de publicación</label>
            <div class="flex items-center gap-2 flex-wrap">
                <input type="number" name="anio" id="anio-inp"
                       value="{{ request('anio') }}"
                       min="2000" max="{{ date('Y') + 5 }}"
                       placeholder="{{ date('Y') }}"
                       class="w-28 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <button type="button"
                            onclick="document.getElementById('anio-inp').value='{{ $y }}'"
                            class="px-2 py-1.5 text-xs rounded-lg border {{ request('anio') == $y ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]' }} transition-all">
                        {{ $y }}
                    </button>
                @endfor
            </div>
        </div>
        <button type="submit" class="sgd-btn-primary px-4 py-2 rounded-lg text-sm font-medium">Filtrar</button>
        @if(request()->hasAny(['estado_revision','anio']))
            <a href="{{ route('director.reportes.index') }}" class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2">Limpiar</a>
        @endif
    </form>

    {{-- Tarjetas resumen --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Investigadores</p>
            <p class="text-3xl font-bold text-slate-900">{{ $actividadGeneral['total_investigadores'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500 mb-1 font-semibold uppercase tracking-wider">Total</p>
            <p class="text-3xl font-bold text-slate-900">{{ $aprobadosVsRechazados['total'] }}</p>
        </div>
        <div class="bg-green-50 rounded-xl border border-green-100 p-4 shadow-sm">
            <p class="text-xs text-green-600 mb-1 font-semibold uppercase tracking-wider">Aprobados</p>
            <p class="text-3xl font-bold text-green-700">{{ $aprobadosVsRechazados['aprobado'] }}</p>
        </div>
        <div class="bg-amber-50 rounded-xl border border-amber-100 p-4 shadow-sm">
            <p class="text-xs text-amber-600 mb-1 font-semibold uppercase tracking-wider">Pendientes</p>
            <p class="text-3xl font-bold text-amber-700">{{ $aprobadosVsRechazados['pendiente'] }}</p>
        </div>
    </div>

    {{-- Tabla de productos --}}
    @if($productos->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-slate-100">
            <h2 class="text-base font-semibold text-slate-900">Productos del Grupo ({{ $productos->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="sgd-table text-sm">
                <thead>
                    <tr>
                        <th class="text-left">Investigador</th>
                        <th class="text-left">Título</th>
                        <th class="text-left">Proyecto</th>
                        <th class="text-left">Año</th>
                        <th class="text-left">Estado</th>
                        <th class="text-left">Tipología</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($productos as $p)
                    @php $estado = $p->estado_revision?->value ?? $p->estado_revision ?? 'pendiente'; @endphp
                    <tr>
                        <td class="text-slate-600">{{ $p->author?->person?->nombre_completo ?? $p->author?->email ?? '—' }}</td>
                        <td class="font-medium text-slate-800">{{ $p->titulo }}</td>
                        <td class="text-slate-500">{{ $p->product?->project?->nombre ?? '—' }}</td>
                        <td class="text-slate-600">{{ $p->anio_publicacion ?? '—' }}</td>
                        <td>
                            @php $badgeMap = ['aprobado' => 'bg-green-100 text-green-800', 'pendiente' => 'bg-amber-100 text-amber-800', 'en_revision' => 'bg-blue-100 text-blue-800', 'rechazado' => 'bg-red-100 text-red-800']; @endphp
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold {{ $badgeMap[$estado] ?? 'bg-slate-100 text-slate-700' }}">
                                {{ ucfirst(str_replace('_', ' ', $estado)) }}
                            </span>
                        </td>
                        <td class="text-slate-500">{{ $p->mincienciasTypology?->nombre ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Por investigador --}}
    @if(count($porInvestigador))
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900">Por Investigador</h2>
            </div>
            @php $maxInv = collect($porInvestigador)->max('total') ?: 1; @endphp
            <div class="divide-y divide-slate-50">
                @foreach($porInvestigador as $item)
                <div class="flex items-center gap-3 px-5 py-3">
                    <span class="w-8 h-8 rounded-full bg-[#39A900]/15 flex items-center justify-center shrink-0 text-xs font-bold text-[#39A900]">
                        {{ strtoupper(substr($item['investigador'] ?? 'U', 0, 1)) }}
                    </span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $item['investigador'] }}</p>
                        <div class="mt-1 bg-slate-100 rounded-full h-1.5 overflow-hidden">
                            <div class="bg-[#39A900] h-1.5 rounded-full" style="width: {{ round(($item['total'] / $maxInv) * 100) }}%"></div>
                        </div>
                    </div>
                    <span class="text-base font-bold text-slate-900 shrink-0">{{ $item['total'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="text-base font-semibold text-slate-900">Por Año</h2>
            </div>
            @php $maxAnio = $porAnio->max('total') ?: 1; @endphp
            <div class="divide-y divide-slate-50">
                @forelse($porAnio as $item)
                <div class="flex items-center gap-4 px-5 py-3">
                    <span class="w-14 text-sm font-mono text-slate-600 shrink-0">{{ $item->anio_publicacion ?? '—' }}</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                        <div class="bg-[#39A900] h-2 rounded-full" style="width: {{ round(($item->total / $maxAnio) * 100) }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-slate-900 shrink-0">{{ $item->total }}</span>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-8">Sin datos.</p>
                @endforelse
            </div>
        </div>
    </div>
    @endif

    {{-- Panel de descargas --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm" x-data="{ tipo: 'general', periodo: '' }">
        <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Descargar Reporte
        </h3>

        <div class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Tipo</label>
                <div class="flex gap-2 flex-wrap">
                    <button type="button" @click="tipo = 'general'"
                            :class="tipo === 'general' ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">General</button>
                    <button type="button" @click="tipo = 'por_investigador'"
                            :class="tipo === 'por_investigador' ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Por Investigador</button>
                    <button type="button" @click="tipo = 'por_anio'"
                            :class="tipo === 'por_anio' ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Por Año</button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Periodo</label>
                <div class="flex gap-2 flex-wrap">
                    <button type="button" @click="periodo = ''"
                            :class="periodo === '' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Todos</button>
                    <button type="button" @click="periodo = 'semanal'"
                            :class="periodo === 'semanal' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Semanal</button>
                    <button type="button" @click="periodo = 'mensual'"
                            :class="periodo === 'mensual' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Mensual</button>
                    <button type="button" @click="periodo = 'anual'"
                            :class="periodo === 'anual' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">Anual</button>
                </div>
            </div>

            <div class="flex gap-2">
                <button type="button"
                        @click="window.location = '{{ route('director.reportes.exportar.csv') }}?tipo=' + tipo + '&periodo=' + periodo + '{{ request()->filled('estado_revision') ? '&estado_revision=' . request('estado_revision') : '' }}{{ request()->filled('anio') ? '&anio=' . request('anio') : '' }}'"
                        class="flex items-center gap-2 bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-all shadow">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    CSV (Excel)
                </button>
                <button type="button"
                        @click="window.open('{{ route('director.reportes.exportar.pdf') }}?tipo=' + tipo + '&periodo=' + periodo + '{{ request()->filled('anio') ? '&anio=' . request('anio') : '' }}', '_blank')"
                        class="flex items-center gap-2 bg-rose-600 hover:bg-rose-700 text-white text-sm font-semibold px-4 py-2.5 rounded-lg transition-all shadow">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
                    </svg>
                    Descargar PDF
                </button>
            </div>
        </div>
    </div>

</x-app-layout>
