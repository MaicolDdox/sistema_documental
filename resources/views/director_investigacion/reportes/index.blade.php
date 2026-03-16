<x-app-layout>
    <x-slot name="header">Reportes del Grupo</x-slot>

    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Reportes del Grupo</h1>
            <p class="text-sm text-slate-500 mt-0.5">Estadísticas de producción académica del grupo de investigación</p>
        </div>
    </div>

    {{-- Panel de Descarga --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6 shadow-sm" x-data="{ tipo: 'general', periodo: '' }">
        <h2 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
            <svg class="w-4 h-4 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
            </svg>
            Descargar Reporte CSV
        </h2>

        <div class="flex flex-wrap gap-4 items-end">
            {{-- Tipo de reporte --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Tipo de reporte</label>
                <div class="flex gap-2 flex-wrap">
                    <button type="button" @click="tipo = 'general'"
                            :class="tipo === 'general' ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">
                        General
                    </button>
                    <button type="button" @click="tipo = 'por_investigador'"
                            :class="tipo === 'por_investigador' ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">
                        Por Investigador
                    </button>
                    <button type="button" @click="tipo = 'por_anio'"
                            :class="tipo === 'por_anio' ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">
                        Por Año
                    </button>
                </div>
            </div>

            {{-- Periodo --}}
            <div>
                <label class="block text-xs font-medium text-slate-500 mb-1.5">Periodo</label>
                <div class="flex gap-2 flex-wrap">
                    <button type="button" @click="periodo = ''"
                            :class="periodo === '' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">
                        Todos
                    </button>
                    <button type="button" @click="periodo = 'semanal'"
                            :class="periodo === 'semanal' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">
                        Semanal
                    </button>
                    <button type="button" @click="periodo = 'mensual'"
                            :class="periodo === 'mensual' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">
                        Mensual
                    </button>
                    <button type="button" @click="periodo = 'anual'"
                            :class="periodo === 'anual' ? 'bg-slate-700 text-white border-slate-700' : 'bg-white text-slate-600 border-slate-200 hover:border-slate-400'"
                            class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">
                        Anual
                    </button>
                </div>
            </div>

            {{-- Botón de descarga --}}
            <form method="POST" action="{{ route('director.reportes.exportar') }}" @submit.prevent="
                document.getElementById('inp-tipo-dir').value = tipo;
                document.getElementById('inp-periodo-dir').value = periodo;
                $el.submit();
            ">
                @csrf
                <input type="hidden" id="inp-tipo-dir" name="tipo" value="general">
                <input type="hidden" id="inp-periodo-dir" name="periodo" value="">
                @foreach(request()->only(['estado_revision','anio']) as $k => $v)
                    <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                @endforeach
                <button type="submit"
                        class="flex items-center gap-2 bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold px-5 py-2 rounded-lg transition-all shadow hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                    </svg>
                    Descargar CSV
                </button>
            </form>
        </div>

        {{-- Texto informativo dinámico --}}
        <p class="mt-3 text-xs text-slate-400">
            <span x-show="periodo === ''" x-cloak>Descargando <strong x-text="tipo.replace('_',' ')"></strong> de todos los registros.</span>
            <span x-show="periodo === 'semanal'" x-cloak>Descargando <strong x-text="tipo.replace('_',' ')"></strong> de la semana actual.</span>
            <span x-show="periodo === 'mensual'" x-cloak>Descargando <strong x-text="tipo.replace('_',' ')"></strong> del mes de <strong>{{ now()->translatedFormat('F Y') }}</strong>.</span>
            <span x-show="periodo === 'anual'" x-cloak>Descargando <strong x-text="tipo.replace('_',' ')"></strong> del año <strong>{{ now()->year }}</strong>.</span>
        </p>
    </div>

    {{-- Filtros de vista --}}
    <form method="GET" action="{{ route('director.reportes.index') }}"
          class="bg-white rounded-xl border border-slate-200 p-4 mb-6 flex flex-wrap gap-3 items-end">
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
            <label class="block text-xs font-medium text-slate-600 mb-1">Año</label>
            <input type="number" name="anio" value="{{ request('anio') }}" min="2015" max="{{ date('Y') }}"
                   placeholder="{{ date('Y') }}"
                   class="w-28 border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
        </div>
        <button type="submit" class="sgd-btn-primary px-4 py-2 rounded-lg text-sm font-medium">Filtrar</button>
        @if(request()->hasAny(['estado_revision','anio']))
        <a href="{{ route('director.reportes.index') }}" class="text-sm text-slate-500 hover:text-slate-700 px-3 py-2">Limpiar</a>
        @endif
    </form>

    {{-- Tarjetas resumen --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">Investigadores</p>
            <p class="text-2xl font-bold text-slate-900">{{ $actividadGeneral['total_investigadores'] }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm">
            <p class="text-xs text-slate-500 mb-1">Total productos</p>
            <p class="text-2xl font-bold text-slate-900">{{ $aprobadosVsRechazados['total'] }}</p>
        </div>
        <div class="bg-green-50 rounded-xl border border-green-100 p-4 shadow-sm">
            <p class="text-xs text-green-600 mb-1">Aprobados</p>
            <p class="text-2xl font-bold text-green-700">{{ $aprobadosVsRechazados['aprobado'] }}</p>
        </div>
        <div class="bg-amber-50 rounded-xl border border-amber-100 p-4 shadow-sm">
            <p class="text-xs text-amber-600 mb-1">Pendientes</p>
            <p class="text-2xl font-bold text-amber-700">{{ $aprobadosVsRechazados['pendiente'] }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Por investigador --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-base font-semibold text-slate-900">Producción por Investigador</h2>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($porInvestigador as $item)
                <div class="flex items-center gap-4 px-5 py-4">
                    <div class="w-8 h-8 rounded-full bg-[#39A900]/15 flex items-center justify-center shrink-0">
                        <span class="text-xs font-bold text-[#39A900]">{{ strtoupper(substr((string)($item['investigador'] ?? 'U'), 0, 1)) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $item['investigador'] }}</p>
                    </div>
                    <span class="text-lg font-bold text-slate-900 shrink-0">{{ $item['total'] }}</span>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-8">Sin datos con los filtros aplicados.</p>
                @endforelse
            </div>
        </div>

        {{-- Por año --}}
        <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                <h2 class="text-base font-semibold text-slate-900">Producción por Año</h2>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($porAnio as $item)
                <div class="flex items-center gap-4 px-5 py-4">
                    <span class="w-12 text-sm font-mono font-medium text-slate-600">{{ $item->anio_publicacion ?? '—' }}</span>
                    <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                        @php $max = $porAnio->max('total') ?: 1; @endphp
                        <div class="bg-[#39A900] h-2 rounded-full" style="width: {{ round(($item->total / $max) * 100) }}%"></div>
                    </div>
                    <span class="text-sm font-bold text-slate-900 w-6 text-right shrink-0">{{ $item->total }}</span>
                </div>
                @empty
                <p class="text-sm text-slate-500 text-center py-8">Sin datos con los filtros aplicados.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
