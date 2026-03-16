<x-app-layout>
<x-slot name="header">Detalle del Proyecto</x-slot>

{{-- Acciones de página --}}
<div class="flex items-center justify-between mb-6">
    <div></div>
    <div>
    @can('proyectos.editar')
        <a href="{{ route('asesor.proyectos.edit', $proyecto->id) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-all">
            Editar
        </a>
    @endcan
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Columna principal --}}
    <div class="lg:col-span-2 space-y-4">
        {{-- Info básica --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="font-outfit font-bold text-xl text-slate-900 mb-1">{{ $proyecto->nombre }}</h2>
            @if($proyecto->descripccion)
                <p class="text-sm text-slate-500 mb-4">{{ $proyecto->descripccion }}</p>
            @endif

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
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
                        @if($proyecto->fecha_fin) → {{ \Carbon\Carbon::parse($proyecto->fecha_fin)->format('d/m/Y') }} @endif
                    </p>
                </div>
            </div>

            @if($macro)
            <div class="mt-3 bg-blue-50 border border-blue-100 rounded-lg p-3">
                <p class="text-xs font-semibold text-blue-700 mb-1">Macroproyecto vinculado</p>
                <p class="text-sm text-blue-800">{{ $macro->nombre }}</p>
                <p class="text-xs text-blue-600">Código: {{ $macro->codigo }}</p>
            </div>
            @endif
        </div>

        {{-- Autores --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-900">Autores / Integrantes ({{ $autores->count() }})</h3>
                @can('proyectos.vincular_integrantes')
                <a href="{{ route('asesor.proyectos.integrantes', $proyecto->id) }}"
                   class="text-xs px-3 py-1.5 rounded-lg text-white hover:opacity-90 transition-all" style="background:#39A900">
                    Gestionar
                </a>
                @endcan
            </div>
            @if($autores->isEmpty())
                <p class="text-sm text-slate-400">No hay autores vinculados.</p>
            @else
            <div class="space-y-2">
                @foreach($autores as $autor)
                <div class="flex items-center gap-3 py-2 border-b border-slate-100 last:border-0">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0" style="background:#0a1628">
                        {{ strtoupper(substr($autor->user?->person?->primer_nombre ?? 'A', 0, 1)) }}{{ strtoupper(substr($autor->user?->person?->primer_apellido ?? 'U', 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">
                            {{ $autor->user?->person?->primer_nombre }} {{ $autor->user?->person?->primer_apellido }}
                            @if($autor->user_id === auth()->id())
                                <span class="ml-1 text-xs text-slate-400">(Asesor)</span>
                            @endif
                        </p>
                        <p class="text-xs text-slate-400">{{ $autor->user?->email }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Productos --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-slate-900">Productos ({{ $productos->count() }})</h3>
                @can('productos.registrar')
                <a href="{{ route('asesor.productos.create', ['proyecto_id' => $proyecto->id]) }}"
                   class="text-xs px-3 py-1.5 rounded-lg text-white hover:opacity-90 transition-all" style="background:#39A900">
                    + Registrar
                </a>
                @endcan
            </div>
            @if($productos->isEmpty())
                <p class="text-sm text-slate-400">Sin productos registrados.</p>
            @else
            <div class="space-y-2">
                @foreach($productos as $prod)
                    @php $gp = $prod->groupProducts->first(); @endphp
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-slate-800">{{ $prod->nombre }}</p>
                            <p class="text-xs text-slate-400">{{ $gp?->mincienciasTypology?->nombre ?? '—' }}</p>
                        </div>
                        @if($gp)
                        <span class="text-xs px-2.5 py-0.5 rounded-full
                            @if($gp->estado_revision?->value === 'aprobado') bg-green-100 text-green-700
                            @elseif($gp->estado_revision?->value === 'rechazado') bg-red-100 text-red-700
                            @else bg-slate-100 text-slate-600 @endif">
                            {{ $gp->estado_revision?->label() ?? 'Pendiente' }}
                        </span>
                        @endif
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        {{-- Evidencias --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-semibold text-slate-900">Evidencias ({{ $evidencias->count() }})</h3>
                @can('evidencias.listar')
                <a href="{{ route('asesor.evidencias.proyecto.index', $proyecto->id) }}"
                   class="text-xs" style="color:#39A900">Ver todas →</a>
                @endcan
            </div>
            @if($evidencias->isEmpty())
                <p class="text-xs text-slate-400">Sin evidencias subidas.</p>
            @else
                <ul class="space-y-1.5">
                    @foreach($evidencias->take(4) as $ev)
                    <li class="flex items-center gap-2 text-xs text-slate-600">
                        <svg class="w-3.5 h-3.5 text-slate-400 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                        {{ Str::limit($ev->nombre ?: basename($ev->archivo), 35) }}
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Acciones --}}
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Acciones</h3>
            <div class="space-y-2">
                @can('evidencias.subir_proyecto')
                <a href="{{ route('asesor.evidencias.proyecto.index', $proyecto->id) }}"
                   class="flex items-center gap-2 w-full px-3 py-2.5 rounded-lg text-sm text-slate-700 bg-slate-50 hover:bg-slate-100 transition-all">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5"/></svg>
                    Subir evidencia
                </a>
                @endcan
                <a href="{{ route('asesor.proyectos.index') }}"
                   class="flex items-center gap-2 w-full px-3 py-2.5 rounded-lg text-sm text-slate-600 bg-slate-50 hover:bg-slate-100 transition-all">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
                    Volver a proyectos
                </a>
            </div>
        </div>
    </div>
</div>
</x-app-layout>
