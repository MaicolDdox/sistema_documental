@extends('layouts.sgd')

@section('title', 'Documentación Interna')
@section('header', '')

@section('content')
@if(!$semillero)
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Documentación Interna</h1>
    <p class="text-sm text-slate-500 mt-0.5">Actas, informes y documentos internos del semillero</p>
</div>
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
<div x-data="{ modalSubir: false }" x-init="@if($errors->any() && old('titulo')) $nextTick(() => { modalSubir = true; }); @endif">
<div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-900">Documentación Interna</h1>
        <p class="text-sm text-slate-500 mt-0.5">Actas, informes y documentos internos del semillero {{ $semillero->nombre }}</p>
    </div>
    <button type="button" @click="modalSubir = true" class="sgd-btn-primary inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-medium shrink-0">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        + Subir documento
    </button>
</div>
@if(session('success'))
<div class="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
@endif

<div class="sgd-table-card bg-white overflow-hidden">
    <div class="overflow-x-auto">
        <table class="sgd-table text-sm">
            <thead>
                <tr>
                    <th class="text-left">Documento</th>
                    <th class="text-left">Tipo</th>
                    <th class="text-left">Subido por</th>
                    <th class="text-left">Fecha</th>
                    <th class="text-left">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($documentos as $doc)
                @php
                    $url = $doc->url_archivo ? \Illuminate\Support\Facades\Storage::disk('public')->url($doc->url_archivo) : '#';
                    $tipoLabel = $tipos[$doc->tipo ?? 'otro'] ?? 'Otro';
                @endphp
                <tr>
                    <td class="px-5 py-3 font-medium text-slate-800">{{ $doc->titulo ?? '—' }}</td>
                    <td class="px-5 py-3">
                        @if(($doc->tipo ?? '') === 'acta')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $tipoLabel }}</span>
                        @elseif(($doc->tipo ?? '') === 'informe')
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">{{ $tipoLabel }}</span>
                        @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-700">{{ $tipoLabel }}</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-slate-600">
                        {{ $doc->subido_por_nombre ?? '—' }}{{ $doc->subido_por_mi ? ' (tú)' : '' }}
                    </td>
                    <td class="px-5 py-3 text-slate-600">{{ $doc->created_at?->format('Y-m-d') ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <div class="relative flex items-center justify-start" x-data="{ open: false }">
                            <button type="button"
                                    @click.stop="open = !open"
                                    @keydown.escape.window="open = false"
                                    class="inline-flex items-center justify-center rounded-full p-2 text-slate-500 hover:text-slate-800 hover:bg-slate-100 transition-colors"
                                    aria-haspopup="true"
                                    :aria-expanded="open ? 'true' : 'false'">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0zm6 0a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                            </button>
                            <div x-show="open"
                                 x-cloak
                                 @click.away="open = false"
                                 class="absolute left-0 mt-2 w-44 rounded-xl bg-white shadow-lg border border-slate-100 py-1 z-20">
                                <a href="{{ $url }}"
                                   target="_blank"
                                   rel="noopener"
                                   @click="open = false"
                                   class="w-full flex items-center gap-2 px-3 py-2 text-xs text-slate-700 hover:bg-slate-50">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6A2.25 2.25 0 005.25 5.25v13.5A2.25 2.25 0 007.5 21h9.75A2.25 2.25 0 0019.5 18.75V12M12 9l3-3m0 0l3 3m-3-3v12" />
                                    </svg>
                                    <span>Ver documento</span>
                                </a>
                                @if($doc->subido_por_mi ?? false)
                                <form action="{{ route('lider-sem.doc-interna.destroy', $doc) }}"
                                      method="post"
                                      class="m-0"
                                      onsubmit="return confirm('¿Eliminar este documento?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            @click="open = false"
                                            class="w-full flex items-center gap-2 px-3 py-2 text-xs text-red-600 hover:bg-red-50">
                                        <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
                                        </svg>
                                        <span>Eliminar</span>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-slate-500">Aún no hay documentos internos.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Subir documento --}}
<template x-teleport="body">
    <div x-show="modalSubir" x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-slate-900/50" @click="modalSubir = false"></div>
        <div class="relative bg-white rounded-2xl shadow-xl max-w-md w-full p-6" @click.stop>
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-bold text-slate-900">Subir documento</h3>
                <button type="button" @click="modalSubir = false" class="p-1 rounded-lg hover:bg-slate-100 text-slate-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form action="{{ route('lider-sem.doc-interna.store') }}" method="post" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Título del documento *</label>
                    <input type="text" name="titulo" value="{{ old('titulo') }}" required maxlength="255"
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]"
                        placeholder="Ej: Acta reunión enero 2025">
                    @error('titulo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tipo *</label>
                    <select name="tipo" required class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]">
                        @foreach($tipos as $valor => $etiqueta)
                        <option value="{{ $valor }}" {{ old('tipo') === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Archivo * (PDF, DOC, DOCX, XLS, XLSX)</label>
                    <input type="file" name="archivo" accept=".pdf,.doc,.docx,.xls,.xlsx" required
                        class="w-full rounded-xl border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-[#39A900]/30 focus:border-[#39A900]">
                    @error('archivo')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" @click="modalSubir = false" class="flex-1 px-4 py-2.5 rounded-xl border border-slate-200 text-slate-700 text-sm font-medium hover:bg-slate-50">Cancelar</button>
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-xl bg-[#39A900] text-white text-sm font-medium hover:opacity-90">Subir</button>
                </div>
            </form>
        </div>
    </div>
</template>
</div>
@endif
@endsection
