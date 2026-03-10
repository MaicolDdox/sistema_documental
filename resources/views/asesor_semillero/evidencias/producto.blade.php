@extends('asesor_semillero.layout')

@section('title', 'Evidencias del Producto')
@section('header', 'Evidencias del Producto')

@section('content')
<div class="mb-3">
    <a href="{{ route('asesor.productos.show', $producto->id) }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/></svg>
        {{ Str::limit($producto->nombre, 55) }}
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
    {{-- Subir evidencia --}}
    @can('evidencias.subir_producto')
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-900 mb-4">Subir nueva evidencia</h3>
        <form method="POST" action="{{ route('asesor.evidencias.producto.store', $producto->id) }}" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nombre / descripción <span class="text-red-500">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}" placeholder="Ej: Certificado publicación"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all @error('nombre') border-red-400 @enderror">
                    @error('nombre') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Archivo <span class="text-red-500">*</span></label>
                    <input type="file" name="archivo" accept=".pdf,.docx,.jpg,.jpeg,.png,.xlsx"
                           class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm focus:border-[#39A900] transition-all @error('archivo') border-red-400 @enderror">
                    <p class="text-xs text-slate-400 mt-1">PDF, Word, imagen (JPG, PNG) o Excel. Máx. 10 MB.</p>
                    @error('archivo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nota adicional</label>
                    <textarea name="descripccion" rows="2" class="w-full border border-slate-200 rounded-lg px-3 py-2.5 text-sm text-slate-800 focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all resize-none">{{ old('descripccion') }}</textarea>
                </div>
                <button type="submit" class="w-full py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90" style="background:#39A900">
                    Subir evidencia
                </button>
            </div>
        </form>
    </div>
    @endcan

    {{-- Lista de evidencias --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h3 class="font-semibold text-slate-900 mb-4">Evidencias registradas ({{ $evidencias->count() }})</h3>
        @if($evidencias->isEmpty())
            <p class="text-sm text-slate-400">Sin evidencias subidas para este producto.</p>
        @else
        <div class="space-y-3">
            @foreach($evidencias as $ev)
            <div class="flex items-start justify-between gap-3 py-3 border-b border-slate-100 last:border-0">
                <div class="flex items-start gap-3 flex-1 min-w-0">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-slate-100 flex-shrink-0 mt-0.5">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 18.32m.009-.01l-.01.01m5.699-9.941l-7.81 7.81a1.5 1.5 0 002.112 2.13"/></svg>
                    </div>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-800 truncate">{{ $ev->nombre ?: basename($ev->archivo) }}</p>
                        <p class="text-xs text-slate-400">
                            {{ $ev->uploadedBy?->person?->primer_nombre ?? 'Desconocido' }}
                            · {{ $ev->created_at?->diffForHumans() }}
                        </p>
                        @if($ev->descripccion)
                        <p class="text-xs text-slate-500 mt-0.5">{{ Str::limit($ev->descripccion, 80) }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($ev->archivo)
                    <a href="{{ asset('storage/' . $ev->archivo) }}" target="_blank"
                       class="text-xs px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all">Ver</a>
                    @endif
                    @can('evidencias.eliminar_propia')
                    @if($ev->uploaded_by === auth()->id())
                    <form method="POST" action="{{ route('asesor.evidencias.destroy', $ev->id) }}"
                          onsubmit="return confirm('¿Eliminar esta evidencia?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs px-2.5 py-1.5 rounded-lg border border-red-200 text-red-600 hover:bg-red-50 transition-all">
                            Eliminar
                        </button>
                    </form>
                    @endif
                    @endcan
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>
@endsection
