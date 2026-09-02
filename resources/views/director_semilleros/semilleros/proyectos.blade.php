<div x-data="{ filtro: '', seleccionado: {{ $semillero->projects->first()?->id ?? 'null' }} }">
    <div class="mb-4 relative max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
        <input type="text" x-model="filtro" placeholder="Buscar proyecto por nombre..."
               class="w-full pl-9 pr-3 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
    </div>

    @if($semillero->projects->isEmpty())
    <div class="py-8 text-center text-slate-500 border border-slate-100 border-dashed rounded-lg bg-slate-50">
        <p>No hay proyectos asociados a este semillero.</p>
    </div>
    @else
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Lista de proyectos (filtrable) --}}
        <div class="lg:col-span-1 space-y-1.5 max-h-[32rem] overflow-y-auto pr-1">
            @foreach($semillero->projects as $proyecto)
            @php $nombreProyectoJs = \Illuminate\Support\Js::from($proyecto->nombre); @endphp
            <button type="button"
                    @click="seleccionado = {{ $proyecto->id }}"
                    x-show="filtro === '' || {{ $nombreProyectoJs }}.toLowerCase().includes(filtro.toLowerCase())"
                    class="w-full text-left px-3.5 py-2.5 rounded-lg border transition-colors"
                    :class="seleccionado === {{ $proyecto->id }} ? 'border-[#39A900] bg-[#39A900]/5' : 'border-slate-200 hover:bg-slate-50'">
                <p class="text-sm font-medium text-slate-900 truncate">{{ $proyecto->nombre }}</p>
                <p class="text-xs text-slate-500 mt-0.5">{{ $proyecto->estado?->value }}</p>
            </button>
            @endforeach
        </div>

        {{-- Detalle del proyecto seleccionado --}}
        <div class="lg:col-span-2 space-y-4">
            @foreach($semillero->projects as $proyecto)
            <div x-show="seleccionado === {{ $proyecto->id }}" x-cloak class="border border-slate-200 rounded-lg bg-white p-4 space-y-5">
                <div>
                    <h4 class="text-base font-bold text-slate-900">{{ $proyecto->nombre }}</h4>
                    <p class="text-xs text-slate-500 mt-1">{{ $proyecto->descripcion ?? 'Sin descripción.' }}</p>
                    <div class="flex flex-wrap items-center gap-3 mt-2 text-xs text-slate-500">
                        <span>Estado: <strong class="text-slate-700">{{ $proyecto->estado?->value }}</strong></span>
                        <span>Inicio: <strong class="text-slate-700">{{ $proyecto->fecha_inicio?->format('d/m/Y') ?? 'N/A' }}</strong></span>
                        <span>Líder de Proyecto: <strong class="text-slate-700">{{ $proyecto->liderProyecto?->person?->nombre_completo ?? $proyecto->liderProyecto?->email ?? 'Sin asignar' }}</strong></span>
                    </div>
                </div>

                {{-- Integrantes (aprendices) --}}
                <div>
                    <h5 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Integrantes del Proyecto ({{ $proyecto->learners->count() }})</h5>
                    <div class="space-y-1.5">
                        @forelse($proyecto->learners as $aprendiz)
                        <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-lg px-3 py-2 text-xs">
                            <span class="font-medium text-slate-800">{{ $aprendiz->nombre_completo }}</span>
                            <span class="text-slate-500">Doc: {{ $aprendiz->numero_documento }} @if($aprendiz->ficha) · Ficha: {{ $aprendiz->ficha }} @endif</span>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400">Sin aprendices registrados.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Co-investigadores --}}
                <div>
                    <h5 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Co-investigadores Asociados ({{ $proyecto->authors->count() }})</h5>
                    <div class="space-y-1.5">
                        @forelse($proyecto->authors as $coinvestigador)
                        <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-lg px-3 py-2 text-xs">
                            <span class="font-medium text-slate-800">{{ $coinvestigador->person?->nombre_completo ?? $coinvestigador->email }}</span>
                            <span class="text-slate-500">{{ $coinvestigador->email }}</span>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400">Sin co-investigadores vinculados.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Evidencias de desarrollo --}}
                <div>
                    <h5 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Evidencias Cargadas ({{ $proyecto->evidenciasDesarrollo->count() }})</h5>
                    <div class="space-y-1.5">
                        @forelse($proyecto->evidenciasDesarrollo as $evidencia)
                        <div class="flex items-center justify-between gap-3 bg-slate-50 border border-slate-100 rounded-lg px-3 py-2">
                            <div class="min-w-0">
                                <p class="text-xs font-medium text-slate-800 truncate">{{ $evidencia->nombre }}</p>
                                @if($evidencia->descripcion)
                                <p class="text-xs text-slate-500 truncate">{{ $evidencia->descripcion }}</p>
                                @endif
                            </div>
                            @if($evidencia->archivo)
                            <a href="{{ route('dir-sem.evidencias.descargar', $evidencia) }}" class="text-[#39A900] hover:underline text-xs font-medium shrink-0">Descargar</a>
                            @endif
                        </div>
                        @empty
                        <p class="text-xs text-slate-400">Sin evidencias de desarrollo subidas.</p>
                        @endforelse
                    </div>
                </div>

                {{-- Producto final --}}
                <div>
                    <h5 class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Producto Final ({{ $proyecto->evidenciasProductoFinal->count() }})</h5>
                    <div class="space-y-2">
                        @forelse($proyecto->evidenciasProductoFinal as $producto)
                        @php
                            $eLider = $producto->estado_revision_lider?->value;
                            $eDirector = $producto->estado_revision_director?->value;
                        @endphp
                        <div class="bg-slate-50 border border-slate-100 rounded-lg px-3 py-2.5">
                            <div class="flex items-center justify-between gap-3 mb-2">
                                <p class="text-xs font-medium text-slate-800 truncate">{{ $producto->nombre }}</p>
                                @if($producto->archivo)
                                <a href="{{ route('dir-sem.evidencias.descargar', $producto) }}" class="text-[#39A900] hover:underline text-xs font-medium shrink-0">Descargar</a>
                                @endif
                            </div>
                            <div class="flex flex-wrap items-center gap-2 text-xs">
                                <span class="text-slate-500">Líder Semillero:</span>
                                @if($eLider === 'aprobado')
                                    <span class="px-2 py-0.5 rounded-full font-medium bg-green-100 text-green-800">Aprobado</span>
                                @elseif($eLider === 'rechazado')
                                    <span class="px-2 py-0.5 rounded-full font-medium bg-red-100 text-red-800">Rechazado</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full font-medium bg-amber-100 text-amber-800">Pendiente</span>
                                @endif
                                <span class="text-slate-300">|</span>
                                <span class="text-slate-500">Director:</span>
                                @if($eDirector === 'aprobado')
                                    <span class="px-2 py-0.5 rounded-full font-medium bg-green-100 text-green-800">Aprobado</span>
                                @elseif($eDirector === 'rechazado')
                                    <span class="px-2 py-0.5 rounded-full font-medium bg-red-100 text-red-800">Rechazado</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full font-medium bg-amber-100 text-amber-800">Pendiente</span>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-xs text-slate-400">Sin producto final subido todavía.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
