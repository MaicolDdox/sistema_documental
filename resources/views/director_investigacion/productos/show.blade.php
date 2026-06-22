<x-app-layout>
    <x-slot name="header">Detalle del Producto</x-slot>

    <div class="mb-4 flex items-center gap-2 text-sm text-slate-500">
        <a href="{{ route('director.productos.index') }}" class="hover:text-[#39A900]">Productos</a>
        <span>/</span>
        <span class="text-slate-800 font-medium truncate max-w-[200px]">{{ $producto->product?->titulo ?? 'Detalle' }}</span>
    </div>

    @if($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
        @foreach($errors->all() as $e)
            <p class="text-sm text-red-700">• {{ $e }}</p>
        @endforeach
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Columna principal --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Info del producto --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <h2 class="text-base font-semibold text-slate-900">Información del Producto</h2>
                </div>
                <div class="p-5 space-y-4">
                    <div>
                        <p class="text-xs text-slate-500 mb-1">Título</p>
                        <p class="text-slate-800 font-medium">{{ $producto->product?->titulo ?? $producto->titulo ?? '—' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Tipología Minciencias</p>
                            <p class="text-slate-700 text-sm">{{ $producto->mincienciasTypology?->nombre ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Subcategoría</p>
                            <p class="text-slate-700 text-sm">{{ $producto->mincienciasSubcategory?->nombre ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Año de publicación</p>
                            <p class="text-slate-700 text-sm">{{ $producto->anio_publicacion ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-500 mb-1">Área del conocimiento</p>
                            <p class="text-slate-700 text-sm">{{ $producto->knowledgeArea?->nombre ?? '—' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Investigador --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Investigador autor</h3>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-[#39A900]/20 flex items-center justify-center shrink-0">
                        <span class="text-sm font-bold text-[#39A900]">
                            {{ strtoupper(substr($producto->author?->person?->primer_nombre ?? $producto->author?->email ?? 'U', 0, 1)) }}{{ strtoupper(substr($producto->author?->person?->primer_apellido ?? '', 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <p class="font-medium text-slate-800">
                            {{ $producto->author?->person?->primer_nombre ?? '—' }}
                            {{ $producto->author?->person?->primer_apellido ?? '' }}
                        </p>
                        <p class="text-xs text-slate-500">{{ $producto->author?->email ?? '' }}</p>
                    </div>
                </div>
            </div>

            {{-- Evidencias --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">
                        Evidencias
                        <span class="ml-2 text-xs font-normal text-slate-400">({{ $producto->product?->productEvidences?->count() ?? 0 }})</span>
                    </h3>
                </div>
                <div class="p-4 space-y-2">
                    @forelse($producto->product?->productEvidences ?? [] as $ev)
                    <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-100 hover:bg-slate-50">
                        <svg class="w-5 h-5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 20.552m5.108-11.479l-2.28-2.28"/>
                        </svg>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 truncate">{{ $ev->nombre ?? basename($ev->archivo ?? '') }}</p>
                        </div>
                        <a href="{{ route('director.productos.evidencias.download', [$producto, $ev]) }}"
                           class="text-xs text-[#39A900] hover:underline font-medium shrink-0">Ver →</a>
                    </div>
                    @empty
                    <p class="text-sm text-amber-600 bg-amber-50 rounded-lg p-3 border border-amber-100">
                        ⚠️ Sin evidencias adjuntas — no se puede aprobar hasta agregar al menos una.
                    </p>
                    @endforelse
                </div>
            </div>

            {{-- Historial de revisiones --}}
            @if($producto->reviews->isNotEmpty())
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h3 class="text-sm font-semibold text-slate-900">Historial de Revisiones</h3>
                </div>
                <div class="p-4 space-y-3">
                    @foreach($producto->reviews->sortByDesc('created_at') as $rev)
                    <div class="flex gap-3">
                        <div class="w-1.5 rounded-full
                            @if($rev->accion === 'aprobado') bg-green-400
                            @elseif($rev->accion === 'rechazado') bg-red-400
                            @else bg-blue-400 @endif shrink-0 self-stretch"></div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between">
                                <p class="text-xs font-semibold
                                    @if($rev->accion === 'aprobado') text-green-700
                                    @elseif($rev->accion === 'rechazado') text-red-700
                                    @else text-blue-700 @endif">
                                    {{ ucfirst(str_replace('_', ' ', $rev->accion)) }}
                                </p>
                                <p class="text-xs text-slate-400">{{ $rev->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <p class="text-xs text-slate-500">Por: {{ $rev->reviewer?->person?->primer_nombre ?? $rev->reviewer?->email ?? '—' }}</p>
                            @if($rev->observaciones)
                            <p class="text-sm text-slate-700 mt-1 bg-slate-50 rounded p-2">{{ $rev->observaciones }}</p>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Panel lateral de acciones --}}
        <div class="space-y-4">
            {{-- Estado actual --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Estado actual</h3>
                @php $est = $producto->estado_revision?->value ?? 'pendiente'; @endphp
                <div class="@if($est === 'aprobado') bg-green-50 border-green-200 text-green-700
                            @elseif($est === 'rechazado') bg-red-50 border-red-200 text-red-700
                            @elseif($est === 'en_revision') bg-blue-50 border-blue-200 text-blue-700
                            @else bg-amber-50 border-amber-200 text-amber-700 @endif
                            border rounded-lg p-3 text-center font-semibold text-sm">
                    {{ ucfirst(str_replace('_', ' ', $est)) }}
                </div>
                @if($producto->observaciones_revision)
                <div class="mt-3 p-3 bg-slate-50 rounded-lg">
                    <p class="text-xs text-slate-500 mb-1 font-medium">Observaciones previas</p>
                    <p class="text-sm text-slate-700">{{ $producto->observaciones_revision }}</p>
                </div>
                @endif
            </div>

            {{-- Acciones --}}
            <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-5 space-y-3">
                <h3 class="text-sm font-semibold text-slate-900">Decisión de revisión</h3>

                {{-- Aprobar --}}
                <form method="POST" action="{{ route('director.productos.aprobar', $producto) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                            onclick="return confirm('¿Aprobar este producto?')"
                            class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                        </svg>
                        Aprobar producto
                    </button>
                </form>

                {{-- Marcar en revisión --}}
                <form method="POST" action="{{ route('director.productos.en-revision', $producto) }}">
                    @csrf @method('PATCH')
                    <button type="submit"
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.964-7.178z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Marcar en revisión
                    </button>
                </form>

                {{-- Rechazar con observaciones --}}
                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open"
                            class="w-full bg-red-500 hover:bg-red-600 text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                        Rechazar
                    </button>
                    <div x-show="open" x-cloak class="mt-3">
                        <form method="POST" action="{{ route('director.productos.rechazar', $producto) }}">
                            @csrf @method('PATCH')
                            <div class="mb-2">
                                <label class="block text-xs font-medium text-slate-700 mb-1">Observaciones <span class="text-red-500">*</span></label>
                                <textarea name="observaciones" rows="3" required minlength="10"
                                          placeholder="Describe el motivo del rechazo..."
                                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 focus:border-red-400 focus:ring-2 focus:ring-red-100 transition-all"></textarea>
                            </div>
                            <button type="submit"
                                    class="w-full border border-red-400 text-red-600 hover:bg-red-50 font-semibold py-2 px-4 rounded-lg text-sm transition-all">
                                Confirmar rechazo
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
