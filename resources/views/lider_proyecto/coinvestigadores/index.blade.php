@extends('layouts.sgd')

@section('title', 'Co-investigadores')
@section('header', '')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-slate-900">Co-investigadores — {{ $proyecto->nombre }}</h1>
    <p class="text-sm text-slate-500 mt-0.5">Un co-investigador puede participar en varios proyectos a la vez.</p>
</div>

@if(session('success'))
<div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
@endif

<div class="bg-white rounded-xl border border-slate-200 p-5 mb-6">
    <h2 class="text-base font-semibold text-slate-900 mb-3">Buscar y vincular</h2>
    <form method="GET" action="{{ route('lider-proyecto.coinvestigadores.index') }}" class="flex gap-2 mb-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Buscar por nombre, documento o correo..."
               class="flex-1 border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm">
        <button type="submit" class="border border-slate-200 bg-white text-slate-700 font-medium py-2.5 px-4 rounded-lg text-sm">Buscar</button>
    </form>

    @forelse($disponibles as $u)
    <div class="flex items-center justify-between py-2 border-b border-slate-50 last:border-0">
        <div>
            <p class="text-sm font-medium text-slate-800">{{ $u->person?->nombre_completo ?? $u->email }}</p>
            <p class="text-xs text-slate-500">{{ $u->email }} · {{ $u->numero_documento }}</p>
        </div>
        <form action="{{ route('lider-proyecto.coinvestigadores.store') }}" method="POST">
            @csrf
            <input type="hidden" name="user_id" value="{{ $u->id }}">
            <button type="submit" class="text-xs font-medium text-[#39A900] hover:text-[#2d8500]">Vincular</button>
        </form>
    </div>
    @empty
    <p class="text-sm text-slate-500">
        @if(request('search'))
            No se encontraron co-investigadores con ese criterio.
        @else
            No hay co-investigadores disponibles para vincular.
        @endif
    </p>
    @endforelse
</div>

<div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-semibold text-slate-900">Vinculados a este proyecto</h3>
    </div>
    <ul class="divide-y divide-slate-100">
        @forelse($vinculados as $co)
        <li class="px-5 py-3 flex items-center justify-between text-sm">
            <div>
                <p class="font-medium text-slate-800">{{ $co->person?->nombre_completo ?? $co->email }}</p>
                <p class="text-xs text-slate-500">{{ $co->email }}</p>
            </div>
            <form action="{{ route('lider-proyecto.coinvestigadores.destroy', $co) }}" method="POST" onsubmit="return confirm('¿Desvincular a este co-investigador del proyecto?');">
                @csrf @method('DELETE')
                <button type="submit" class="text-xs text-red-600 hover:text-red-800">Desvincular</button>
            </form>
        </li>
        @empty
        <li class="px-5 py-8 text-center text-sm text-slate-500">Sin co-investigadores vinculados todavía.</li>
        @endforelse
    </ul>
</div>
@endsection
