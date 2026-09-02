<x-app-layout>
    <x-slot name="header">{{ $producto->nombre }}</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-700">Administración</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.minciencias.index') }}" class="hover:text-slate-700">Productos Minciencias</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">{{ $producto->nombre }}</span>
    </nav>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
    <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
        <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <div class="space-y-6">

        {{-- Card: Detalle del producto --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Detalle</h2>
                <p class="text-xs text-slate-500">Co-investigador: <span class="font-medium text-slate-700">{{ $producto->user?->person?->nombre_completo ?? $producto->user?->email ?? '—' }}</span></p>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Descripción</p>
                    <p class="text-sm text-slate-700">{{ $producto->descripcion ?? 'Sin descripción.' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Línea de investigación</p>
                    <p class="text-sm text-slate-700">{{ $producto->researchLine?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Línea tecnológica</p>
                    <p class="text-sm text-slate-700">{{ $producto->technologicalLine?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Área temática</p>
                    <p class="text-sm text-slate-700">{{ $producto->thematicArea?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Modalidad</p>
                    <p class="text-sm text-slate-700">{{ $producto->projectModality?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Tipo de investigación</p>
                    <p class="text-sm text-slate-700">{{ $producto->investigationType?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Fechas</p>
                    <p class="text-sm text-slate-700">
                        {{ $producto->fecha_inicio?->format('d/m/Y') ?? '—' }} - {{ $producto->fecha_fin?->format('d/m/Y') ?? '—' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Card: Archivos adjuntos (ver / descargar) --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
                <h3 class="text-sm font-semibold text-slate-900">Archivos Adjuntos ({{ $producto->files->count() }})</h3>
            </div>
            <ul class="divide-y divide-slate-100">
                @forelse($producto->files as $archivo)
                <li class="px-5 py-3 flex items-center justify-between text-sm gap-3">
                    <div class="min-w-0">
                        <p class="font-medium text-slate-800 truncate">{{ $archivo->descripcion ?? basename($archivo->archivo ?? 'Archivo') }}</p>
                        <p class="text-xs text-slate-500">
                            Subido por {{ $archivo->uploadedBy?->person?->nombre_completo ?? $archivo->uploadedBy?->email ?? '—' }}
                            · {{ $archivo->created_at->format('d/m/Y H:i') }}
                        </p>
                    </div>
                    @if($archivo->archivo)
                    <div class="flex items-center gap-3 shrink-0">
                        <a href="{{ route('admin.minciencias.archivos.descargar', $archivo) }}"
                           class="text-[#39A900] hover:underline text-xs font-medium">Descargar</a>
                    </div>
                    @endif
                </li>
                @empty
                <li class="px-5 py-6 text-center text-sm text-slate-500">Sin archivos adjuntos.</li>
                @endforelse
            </ul>
        </div>

        {{-- Card: Revisión --}}
        @php $revVal = $producto->estado_revision?->value ?? 'pendiente'; @endphp
        <div class="bg-white rounded-xl border border-slate-200 p-5">
            <h2 class="text-base font-semibold text-slate-900 mb-3">Revisión</h2>

            <div class="flex items-center gap-3 mb-4">
                @if($revVal === 'aprobado')
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>
                @elseif($revVal === 'rechazado')
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado</span>
                @else
                <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pendiente</span>
                @endif
                @if($producto->revisado_at)
                <span class="text-xs text-slate-500">{{ $producto->revisado_at->format('d/m/Y H:i') }} — {{ $producto->revisadoPor?->person?->nombre_completo ?? $producto->revisadoPor?->email ?? '—' }}</span>
                @endif
            </div>

            @if($producto->observacion_admin)
            <p class="text-sm text-slate-700 mb-4"><span class="font-medium">Observaciones registradas:</span> {{ $producto->observacion_admin }}</p>
            @endif

            @can('minciencias.aprobar')
            <div class="flex items-center gap-3">
                <form action="{{ route('admin.minciencias.aprobar', $producto) }}" method="POST">
                    @csrf
                    <button type="submit" class="sgd-btn-primary px-4 py-2.5 rounded-xl text-sm font-medium">Aprobar</button>
                </form>
                <button type="button" onclick="document.getElementById('rechazo-form').classList.toggle('hidden')"
                        class="px-4 py-2.5 rounded-xl text-sm font-medium border border-red-200 text-red-600 hover:bg-red-50">Rechazar</button>
            </div>
            <form id="rechazo-form" action="{{ route('admin.minciencias.rechazar', $producto) }}" method="POST" class="hidden mt-4">
                @csrf
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Motivo del rechazo <span class="text-red-500">*</span></label>
                <textarea name="observaciones" rows="3" required placeholder="Explica por qué se rechaza este producto"
                          class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm mb-3"></textarea>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2.5 rounded-xl text-sm">Confirmar rechazo</button>
            </form>
            @endcan
        </div>

    </div>
</x-app-layout>
