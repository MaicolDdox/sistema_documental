@extends('layouts.sgd')

@section('title', 'Archivos del Semillero')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Archivos del Semillero</h1>
</div>

@if(!$semillero)
<div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
    <div class="p-8 text-center">
        <div class="w-16 h-16 rounded-full bg-amber-100 flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
        </div>
        <h2 class="text-lg font-semibold text-slate-800 mb-2">Sin semillero asignado</h2>
        <p class="text-sm text-slate-600 max-w-md mx-auto">No tienes un semillero asignado como líder.</p>
        <a href="{{ route('lider-sem.dashboard') }}" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium mt-4">Volver al Dashboard</a>
    </div>
</div>
@else
@if(session('success'))
<div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
@endif

<form action="{{ route('lider-sem.archivos.store') }}" method="post" enctype="multipart/form-data" class="mb-6" id="form-archivo-semillero">
    @csrf
    <label class="block border-2 border-dashed border-slate-300 rounded-xl p-8 text-center cursor-pointer hover:border-[#39A900]/50 hover:bg-slate-50/50 transition-colors">
        <input type="file" name="archivo" id="archivo-semillero" class="hidden" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png" onchange="if(this.files.length) this.form.submit()">
        <div class="flex flex-col items-center gap-3 text-slate-600">
            <svg class="w-12 h-12 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3v12"/></svg>
            <span class="font-medium text-slate-700">Arrastra o haz clic para subir archivo</span>
            <span class="text-xs">PDF, DOCX, XLSX, PPTX, JPG, PNG</span>
            <span class="text-xs text-slate-500">Máx. 20 MB</span>
        </div>
    </label>
    @error('archivo')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</form>

<div class="space-y-3">
    @forelse($archivos as $f)
    @php
        $fecha = $f->created_at?->format('Y-m-d');
        $sizeB = $f->size_bytes ?? 0;
        $sizeStr = $sizeB >= 1048576 ? round($sizeB / 1048576, 1) . ' MB' : ($sizeB >= 1024 ? round($sizeB / 1024) . ' KB' : $sizeB . ' B');
        $ext = strtolower(pathinfo($f->archivo ?? '', PATHINFO_EXTENSION));
        $url = $f->url_archivo ? \Illuminate\Support\Facades\Storage::disk('public')->url($f->url_archivo) : '#';
    @endphp
    <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm flex items-center justify-between px-5 py-4">
        <div class="flex items-center gap-3 min-w-0">
            <div class="w-9 h-9 rounded-lg bg-slate-100 flex items-center justify-center shrink-0">
                @if($ext === 'pdf')
                <span class="text-xs font-semibold text-red-600">PDF</span>
                @elseif(in_array($ext, ['xls','xlsx']))
                <span class="text-xs font-semibold text-emerald-600">XLSX</span>
                @elseif(in_array($ext, ['ppt','pptx']))
                <span class="text-xs font-semibold text-orange-600">PPTX</span>
                @else
                <span class="text-xs font-semibold text-slate-600">{{ strtoupper($ext ?: 'FILE') }}</span>
                @endif
            </div>
            <div class="min-w-0">
                <a href="{{ $url }}" target="_blank" class="block font-medium text-slate-800 truncate hover:underline">{{ $f->archivo ?? '—' }}</a>
                <p class="text-xs text-slate-500 mt-0.5">
                    @if($f->subido_por_mi ?? false)
                        Subido por: tú
                    @else
                        Subido por: {{ $f->subido_por_nombre ?? 'Otro' }}
                    @endif
                    · {{ $fecha ?? '—' }} · {{ $sizeStr }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            @if($f->subido_por_mi ?? false)
            <form action="{{ route('lider-sem.archivos.destroy', $f) }}" method="post" onsubmit="return confirm('¿Eliminar este archivo?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="inline-flex items-center gap-1 text-xs font-medium text-red-600 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Eliminar
                </button>
            </form>
            @else
            <span class="text-xs text-slate-400 italic">No puedes eliminar</span>
            @endif
        </div>
    </div>
    @empty
    <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm px-6 py-10 text-center text-slate-500">
        Aún no hay archivos en el semillero.
    </div>
    @endforelse
</div>
@endif
@endsection
