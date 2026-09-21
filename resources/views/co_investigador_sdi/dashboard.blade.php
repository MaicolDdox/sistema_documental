@extends('layouts.sgd')

@section('title', 'Dashboard')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
    <p class="text-sm text-slate-500 mt-0.5">Resumen de tu participación como co-investigador.</p>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Card: Resumen de proyectos vinculados --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h2 class="text-sm font-semibold text-slate-900">Proyectos Vinculados ({{ $vinculaciones->count() }})</h2>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                <tr>
                    <th class="text-left px-4 py-3">Proyecto</th>
                    <th class="text-left px-4 py-3">Semillero</th>
                    <th class="text-left px-4 py-3">Líder de Proyecto</th>
                    <th class="text-left px-4 py-3">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($vinculaciones as $v)
                <tr>
                    <td class="px-4 py-3 font-medium text-slate-900">{{ $v->project?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $v->project?->seedling?->nombre ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $v->project?->liderProyecto?->person?->nombre_completo ?? $v->project?->liderProyecto?->email ?? '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $v->project?->estado?->value ?? '—' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-4 py-8 text-center text-slate-400">Aún no estás vinculado a ningún proyecto.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Card: Reporte descargable --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 h-fit">
        <h2 class="text-sm font-semibold text-slate-900 mb-2">Reporte</h2>
        <p class="text-sm text-slate-500 mb-4">Descarga un PDF con el detalle de todos los proyectos a los que perteneces.</p>
        <a href="{{ route('co-investigador-sdi.reporte.descargar') }}" class="w-full inline-flex items-center justify-center gap-2 bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Descargar reporte
        </a>
    </div>

</div>
@endsection
