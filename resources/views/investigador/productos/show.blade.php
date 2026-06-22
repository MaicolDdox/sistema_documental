<x-app-layout>
    <x-slot name="header">Detalle del Producto</x-slot>

    <div class="space-y-6">

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        @php
            $estado = $producto->estado_revision?->value;
            $badgeColor = match($estado) {
                'pendiente'   => 'bg-amber-100 text-amber-700',
                'en_revision' => 'bg-blue-100 text-blue-700',
                'aprobado'    => 'bg-green-100 text-green-700',
                'rechazado'   => 'bg-red-100 text-red-700',
                default       => 'bg-slate-100 text-slate-600',
            };
            $estadoLabel = match($estado) {
                'pendiente'   => 'Pendiente',
                'en_revision' => 'En revisión',
                'aprobado'    => 'Aprobado',
                'rechazado'   => 'Rechazado',
                default       => 'Desconocido',
            };
        @endphp

        {{-- Encabezado --}}
        <div class="bg-white rounded-xl border border-slate-200 p-6">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeColor }}">
                            {{ $estadoLabel }}
                        </span>
                        @if($producto->tiene_repositorio && $producto->url_repositorio)
                            <a href="{{ $producto->url_repositorio }}" target="_blank" class="text-xs text-[#39A900] hover:underline">Ver repositorio →</a>
                        @endif
                    </div>
                    <h2 class="text-lg font-semibold text-slate-900">{{ $producto->titulo }}</h2>
                    <p class="text-sm text-slate-500 mt-1">{{ $producto->descripcion ?? 'Sin descripción.' }}</p>
                </div>
                @if($estado === 'rechazado')
                <a href="{{ route('investigador.productos.edit', $producto) }}"
                   class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-3 rounded-lg text-sm transition-all flex-shrink-0">
                    Corregir
                </a>
                @endif
            </div>

            {{-- Observaciones del Director --}}
            @if($estado === 'rechazado' && $producto->observaciones_revision)
            <div class="mt-4 bg-red-50 border border-red-200 rounded-lg p-4">
                <p class="text-xs font-semibold text-red-700 mb-1 uppercase tracking-wide">Observaciones del Director</p>
                <p class="text-sm text-red-800">{{ $producto->observaciones_revision }}</p>
            </div>
            @endif

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 pt-5 border-t border-slate-100">
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Año</p>
                    <p class="text-sm font-medium text-slate-700">{{ $producto->anio_publicacion }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Tipología</p>
                    <p class="text-sm font-medium text-slate-700">{{ $producto->mincienciasTypology?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Subcategoría</p>
                    <p class="text-sm font-medium text-slate-700">{{ $producto->mincienciasSubcategory?->nombre ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-400 uppercase tracking-wide">Proyecto</p>
                    <p class="text-sm font-medium text-slate-700">{{ $producto->product?->project?->nombre ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Autores --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-900">Autores del producto</h3>
            </div>
            <div class="p-5">
                @forelse($producto->product?->productAuthors ?? [] as $pa)
                <div class="flex items-center gap-3 py-2.5 border-b border-slate-100 last:border-0">
                    <div class="w-8 h-8 rounded-full bg-[#0a1628] flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                        {{ strtoupper(substr($pa->user?->person?->primer_nombre ?? $pa->user?->email ?? '?', 0, 1)) }}
                    </div>
                    <p class="text-sm text-slate-700">{{ $pa->user?->person?->primer_nombre ?? $pa->user?->email ?? 'Usuario no encontrado' }}
                        {{ $pa->user?->person?->primer_apellido ?? '' }}</p>
                </div>
                @empty
                <p class="text-sm text-slate-400">Sin autores registrados.</p>
                @endforelse
            </div>
        </div>

        {{-- Evidencias del producto --}}
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-semibold text-slate-900">Evidencias</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Las evidencias son obligatorias para la aprobación del Director.</p>
                </div>
                @if($estado !== 'aprobado')
                <span class="text-xs text-slate-500">Máx. 10 MB | PDF, imagen, Word</span>
                @endif
            </div>
            <div class="p-5 space-y-3">
                @if($estado !== 'aprobado')
                <form method="POST" action="{{ route('investigador.productos.evidencias.store', $producto) }}" enctype="multipart/form-data"
                      class="flex items-end gap-3">
                    @csrf
                    <div class="flex-1">
                        <input type="file" name="archivos[]" accept=".pdf,.jpg,.jpeg,.png,.docx,.xlsx" multiple
                               class="w-full text-sm text-slate-600 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-sm file:font-medium hover:file:bg-slate-200 transition-all">
                        @error('archivos')
                            <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                        @enderror
                        @foreach($errors->messages() as $key => $messages)
                            @if(str_starts_with($key, 'archivos.'))
                                @foreach($messages as $msg)
                                    <p class="text-xs text-red-600 mt-1">{{ $msg }}</p>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                    <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all flex-shrink-0">
                        Subir evidencia
                    </button>
                </form>
                @endif

                @forelse($producto->product?->productEvidences ?? [] as $evidencia)
                <div class="flex items-center justify-between py-2.5 border-b border-slate-100 last:border-0">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 0 1-6.364-6.364l10.94-10.94A3 3 0 1 1 19.5 7.372L8.552 18.32m.009-.01-.01.01m5.699-9.941-7.81 7.81a1.5 1.5 0 0 0 2.112 2.13" />
                        </svg>
                        <span class="text-sm text-slate-700">{{ $evidencia->nombre ?? basename($evidencia->archivo) }}</span>
                        <span class="text-xs text-slate-400">{{ $evidencia->created_at->format('d/m/Y') }}</span>
                    </div>
                    @if($estado !== 'aprobado')
                    <form method="POST" action="{{ route('investigador.evidencias.producto.destroy', $evidencia) }}">
                        @csrf @method('DELETE')
                        <button onclick="return confirm('¿Eliminar esta evidencia?')"
                                class="text-slate-400 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                            </svg>
                        </button>
                    </form>
                    @endif
                </div>
                @empty
                    <p class="text-sm text-slate-400">
                        Sin evidencias subidas aún.
                        @if($estado !== 'aprobado')
                            <strong class="text-amber-600">El Director requiere al menos una evidencia para aprobar.</strong>
                        @endif
                    </p>
                @endforelse
            </div>
        </div>

        {{-- Historial de revisiones --}}
        @if($producto->reviews->isNotEmpty())
        <div class="bg-white rounded-xl border border-slate-200">
            <div class="px-5 py-4 border-b border-slate-100">
                <h3 class="text-sm font-semibold text-slate-900">Historial de revisiones</h3>
            </div>
            <div class="p-5 space-y-3">
                @foreach($producto->reviews->sortByDesc('created_at') as $review)
                <div class="flex items-start gap-3">
                    <div class="w-1.5 h-1.5 rounded-full mt-1.5 flex-shrink-0 {{ $review->accion === 'aprobado' ? 'bg-green-500' : ($review->accion === 'rechazado' ? 'bg-red-500' : 'bg-blue-400') }}"></div>
                    <div class="flex-1">
                        <p class="text-xs text-slate-400">{{ $review->created_at->format('d/m/Y H:i') }}
                            — {{ $review->reviewer?->person?->primer_nombre ?? 'Director' }}</p>
                        <p class="text-sm text-slate-700 capitalize">{{ str_replace('_', ' ', $review->accion) }}</p>
                        @if($review->observaciones)
                            <p class="text-sm text-slate-500 mt-0.5">{{ $review->observaciones }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div>
            <a href="{{ route('investigador.productos.index') }}"
               class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                ← Volver a productos
            </a>
        </div>
    </div>
</x-app-layout>
