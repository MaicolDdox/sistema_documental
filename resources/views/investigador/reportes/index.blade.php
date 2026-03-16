<x-app-layout>
    <x-slot name="header">Mis Reportes</x-slot>

    <div class="max-w-5xl">

        {{-- Métricas --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
            <h2 class="text-lg font-semibold text-slate-900 mb-2">Resumen de mi Actividad</h2>
            <p class="text-sm text-slate-500 mb-6">Métricas generales de tu participación en el grupo.</p>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                    <p class="text-3xl font-bold text-slate-800">{{ $metricas['total_proyectos'] }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase mt-1">Proyectos creados</p>
                </div>
                <div class="bg-slate-50 border border-slate-100 rounded-xl p-4">
                    <p class="text-3xl font-bold text-slate-800">{{ $metricas['total_productos'] }}</p>
                    <p class="text-xs font-semibold text-slate-500 uppercase mt-1">Productos registrados</p>
                </div>
                <div class="bg-green-50 border border-green-100 rounded-xl p-4">
                    <p class="text-3xl font-bold text-green-700">{{ $metricas['productos_aprobados'] }}</p>
                    <p class="text-xs font-semibold text-green-600 uppercase mt-1">Prod. Aprobados</p>
                </div>
                <div class="bg-red-50 border border-red-100 rounded-xl p-4">
                    <p class="text-3xl font-bold text-red-700">{{ $metricas['productos_rechazados'] }}</p>
                    <p class="text-xs font-semibold text-red-600 uppercase mt-1">Prod. Rechazados</p>
                </div>
            </div>
        </div>

        {{-- Panel de Descarga CSV --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5 mb-6 shadow-sm" x-data="{ tipo: 'aprobados', periodo: '' }">
            <h3 class="text-sm font-semibold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#39A900]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                </svg>
                Descargar Reporte CSV
            </h3>

            <div class="flex flex-wrap gap-4 items-end">

                {{-- Tipo --}}
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1.5">Productos a incluir</label>
                    <div class="flex gap-2">
                        <button type="button" @click="tipo = 'aprobados'"
                                :class="tipo === 'aprobados' ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">
                            Solo Aprobados
                        </button>
                        <button type="button" @click="tipo = 'todos'"
                                :class="tipo === 'todos' ? 'bg-[#39A900] text-white border-[#39A900]' : 'bg-white text-slate-600 border-slate-200 hover:border-[#39A900] hover:text-[#39A900]'"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold border transition-all">
                            Todos
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

                {{-- Botón descarga --}}
                <form method="GET" action="{{ route('investigador.reportes.exportar') }}"
                      @submit.prevent="
                          document.getElementById('inp-tipo-inv').value = tipo;
                          document.getElementById('inp-periodo-inv').value = periodo;
                          $el.submit();
                      ">
                    <input type="hidden" id="inp-tipo-inv" name="tipo" value="aprobados">
                    <input type="hidden" id="inp-periodo-inv" name="periodo" value="">
                    <button type="submit"
                            class="flex items-center gap-2 bg-[#39A900] hover:bg-[#2d8500] text-white text-sm font-semibold px-5 py-2 rounded-lg transition-all shadow hover:shadow-md">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/>
                        </svg>
                        Descargar CSV
                    </button>
                </form>
            </div>

            {{-- Indicador dinámico --}}
            <p class="mt-3 text-xs text-slate-400">
                <span x-show="periodo === ''" x-cloak>Todos los registros (<strong x-text="tipo === 'aprobados' ? 'solo aprobados' : 'todos los estados'"></strong>).</span>
                <span x-show="periodo === 'semanal'" x-cloak>Solo productos de la semana actual.</span>
                <span x-show="periodo === 'mensual'" x-cloak>Solo productos del mes de <strong>{{ now()->translatedFormat('F Y') }}</strong>.</span>
                <span x-show="periodo === 'anual'" x-cloak>Solo productos del año <strong>{{ now()->year }}</strong>.</span>
            </p>
        </div>

        {{-- Ver Reporte de Estados (vista) --}}
        <a href="{{ route('investigador.reportes.estado') }}"
           class="group flex items-center gap-4 bg-white rounded-xl border border-slate-200 p-5 hover:shadow-lg hover:border-[#39A900] transition-all">
            <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 group-hover:bg-[#39A900] group-hover:text-white transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6a7.5 7.5 0 107.5 7.5h-7.5V6z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 10.5H21A7.5 7.5 0 0013.5 3v7.5z" />
                </svg>
            </div>
            <div>
                <h3 class="text-sm font-bold text-slate-900 group-hover:text-[#39A900] transition-colors">Ver Productos por Estado</h3>
                <p class="text-xs text-slate-500 mt-0.5">Análisis detallado según estado de revisión (Aprobado, Rechazado, Pendiente).</p>
            </div>
            <svg class="w-5 h-5 text-slate-300 group-hover:text-[#39A900] ml-auto shrink-0 transition-colors" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5"/>
            </svg>
        </a>

    </div>
</x-app-layout>
