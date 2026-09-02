@extends('layouts.sgd')

@section('title', $producto->nombre)
@section('header', '')

@section('content')
<div class="mb-6 flex items-start justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">{{ $producto->nombre }}</h1>
        <p class="text-sm text-slate-500 mt-0.5">Producto Minciencias personal</p>
    </div>
    <div class="flex items-center gap-3 shrink-0">
        <a href="{{ route('co-investigador.productos.edit', $producto) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium border border-slate-200 text-slate-700 hover:bg-slate-50">Editar</a>
        <a href="{{ route('co-investigador.productos.index') }}"
           class="text-sm font-medium text-slate-500 hover:text-slate-700">Volver</a>
    </div>
</div>

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
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">Detalle</h2>
        </div>
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Descripción</p>
                <p class="text-sm text-slate-700">{{ $producto->descripcion ?? 'Sin descripción.' }}</p>
            </div>
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-1">Centro de formación</p>
                <p class="text-sm text-slate-700">{{ $producto->trainingCenter?->nombre ?? '—' }}</p>
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

    {{-- Card: Revisión del administrador --}}
    @php $revVal = $producto->estado_revision?->value ?? 'pendiente'; @endphp
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-900 mb-3">Revisión</h2>
        <div class="flex items-center gap-3 mb-2">
            @if($revVal === 'aprobado')
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>
            @elseif($revVal === 'rechazado')
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado</span>
            @else
            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pendiente de revisión</span>
            @endif
            @if($producto->revisado_at)
            <span class="text-xs text-slate-500">{{ $producto->revisado_at->format('d/m/Y H:i') }} — {{ $producto->revisadoPor?->person?->nombre_completo ?? $producto->revisadoPor?->email ?? '—' }}</span>
            @endif
        </div>
        @if($producto->observacion_admin)
        <p class="text-sm text-slate-700"><span class="font-medium">Observaciones:</span> {{ $producto->observacion_admin }}</p>
        @endif
    </div>

    {{-- Card: Subir archivo --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-900 mb-1">Subir archivo</h2>
        <p class="text-sm text-slate-500 mb-4">Adjunta los archivos de soporte de este producto.</p>
        <form action="{{ route('co-investigador.productos.archivos.store', $producto) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Archivo <span class="text-red-500">*</span></label>
                <input type="file" name="archivo" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm bg-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                <input type="text" name="descripcion" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm">
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm">Subir archivo</button>
            </div>
        </form>
    </div>

    {{-- Card: Archivos adjuntos --}}
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
                <div class="flex items-center gap-3 shrink-0">
                    @if($archivo->archivo)
                    <a href="{{ route('co-investigador.archivos.descargar', $archivo) }}" class="text-[#39A900] hover:underline text-xs font-medium">Descargar</a>
                    @endif
                    <form action="{{ route('co-investigador.archivos.destroy', $archivo) }}" method="POST" onsubmit="return confirm('¿Eliminar este archivo?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Eliminar</button>
                    </form>
                </div>
            </li>
            @empty
            <li class="px-5 py-6 text-center text-sm text-slate-500">Sin archivos adjuntos todavía.</li>
            @endforelse
        </ul>
    </div>

</div>
@endsection
