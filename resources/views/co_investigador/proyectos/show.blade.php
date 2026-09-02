@extends('layouts.sgd')

@section('title', $proyecto->nombre)
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">{{ $proyecto->nombre }}</h1>
    <p class="text-sm text-slate-500 mt-0.5">Semillero {{ $proyecto->seedling?->nombre ?? '—' }}</p>
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

    {{-- Card: Actores del proyecto (solo lectura) --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">Actores del Proyecto</h2>
        </div>
        <div class="p-6 space-y-5">
            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Líder de Proyecto</p>
                @if($proyecto->liderProyecto)
                <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-lg px-3 py-2.5 w-fit">
                    <span class="w-8 h-8 rounded-full bg-[#39A900]/20 flex items-center justify-center text-xs font-bold text-[#39A900]">{{ $proyecto->liderProyecto->initials() }}</span>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $proyecto->liderProyecto->person?->nombre_completo ?? $proyecto->liderProyecto->email }}</p>
                        <p class="text-xs text-slate-500">{{ $proyecto->liderProyecto->email }}</p>
                    </div>
                </div>
                @else
                <p class="text-xs text-slate-400">Sin líder de proyecto asignado.</p>
                @endif
            </div>

            <div>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Aprendices Registrados ({{ $proyecto->learners->count() }})</p>
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
        </div>
    </div>

    {{-- Card: Subir evidencia --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5">
        <h2 class="text-base font-semibold text-slate-900 mb-1">Subir evidencia</h2>
        <p class="text-sm text-slate-500 mb-4">Solo evidencias de desarrollo — visibles para el líder de proyecto y los demás co-investigadores del proyecto.</p>
        <form action="{{ route('co-investigador.proyectos.evidencias.store', $proyecto) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                <input type="text" name="nombre" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Archivo <span class="text-red-500">*</span></label>
                <input type="file" name="archivo" required class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm bg-white">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Descripción</label>
                <textarea name="descripcion" rows="2" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm"></textarea>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm">Subir evidencia</button>
            </div>
        </form>
    </div>

    {{-- Card: Evidencias de desarrollo (compartidas entre líder y co-investigadores) --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Evidencias de Desarrollo</h3>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($proyecto->evidenciasDesarrollo as $ev)
            <li class="px-5 py-3 flex items-center justify-between text-sm gap-3">
                <div class="min-w-0">
                    <p class="font-medium text-slate-800 truncate">{{ $ev->nombre }}</p>
                    <p class="text-xs text-slate-500">
                        Subida por {{ $ev->uploadedBy?->person?->nombre_completo ?? $ev->uploadedBy?->email ?? '—' }}
                        · {{ $ev->created_at->format('d/m/Y H:i') }}
                    </p>
                </div>
                <div class="flex items-center gap-3 shrink-0">
                    @if($ev->archivo)
                    <a href="{{ route('co-investigador.evidencias.descargar', $ev) }}" class="text-[#39A900] hover:underline text-xs font-medium">Descargar</a>
                    @endif
                    @if((int) $ev->uploaded_by === auth()->id())
                    <form action="{{ route('co-investigador.evidencias.destroy', $ev) }}" method="POST" onsubmit="return confirm('¿Eliminar esta evidencia?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-xs text-red-600 hover:text-red-800">Eliminar</button>
                    </form>
                    @endif
                </div>
            </li>
            @empty
            <li class="px-5 py-6 text-center text-sm text-slate-500">Sin evidencias de desarrollo todavía.</li>
            @endforelse
        </ul>
    </div>

    {{-- Card: Producto final (solo lectura — subido por el líder de proyecto) --}}
    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
            <h3 class="text-sm font-semibold text-slate-900">Producto Final</h3>
            <p class="text-xs text-slate-500 mt-0.5">Solo el líder de proyecto puede subir el producto final. Aquí se refleja su estado de revisión.</p>
        </div>
        <ul class="divide-y divide-slate-100">
            @forelse($proyecto->evidenciasProductoFinal as $ev)
            @php
                $eLider = $ev->estado_revision_lider?->value ?? $ev->estado_revision_lider;
                $eDirector = $ev->estado_revision_director?->value ?? $ev->estado_revision_director;
            @endphp
            <li class="px-5 py-3 flex items-center justify-between text-sm gap-3">
                <div class="min-w-0">
                    <p class="font-medium text-slate-800 truncate">{{ $ev->nombre }}</p>
                    <p class="text-xs text-slate-500">{{ $ev->created_at->format('d/m/Y H:i') }}</p>
                </div>
                <div class="flex items-center gap-4 shrink-0">
                    <div class="text-right">
                        <p class="text-[10px] text-slate-400 uppercase tracking-wide">Líder Semillero</p>
                        @if($eLider === 'aprobado')
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>
                        @elseif($eLider === 'rechazado')
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pendiente</span>
                        @endif
                    </div>
                    <div class="text-right">
                        <p class="text-[10px] text-slate-400 uppercase tracking-wide">Director Semilleros</p>
                        @if($eDirector === 'aprobado')
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>
                        @elseif($eDirector === 'rechazado')
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">Pendiente</span>
                        @endif
                    </div>
                </div>
            </li>
            @empty
            <li class="px-5 py-6 text-center text-sm text-slate-500">El líder de proyecto aún no ha subido el producto final.</li>
            @endforelse
        </ul>
    </div>

</div>
@endsection
