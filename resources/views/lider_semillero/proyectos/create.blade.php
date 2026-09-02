@extends('layouts.sgd')

@section('title', 'Nuevo Proyecto')
@section('header', '')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-5">
        <a href="{{ route('lider-sem.proyectos') }}" class="text-sm text-slate-500 hover:text-[#39A900] flex items-center gap-1 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
            Volver a proyectos
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-base font-semibold text-slate-900">Nuevo proyecto — {{ $semillero->nombre }}</h3>
            <p class="text-xs text-slate-500 mt-0.5">Debes asignar un Líder de Proyecto de tu lista para poder crearlo.</p>
        </div>

        <form action="{{ route('lider-sem.proyectos.store') }}" method="POST" class="p-6">
            @csrf
            @include('lider_semillero.proyectos._form')

            <div class="border-t border-slate-100 pt-5 flex items-center justify-end gap-3">
                <a href="{{ route('lider-sem.proyectos') }}" class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-2.5 px-4 rounded-lg text-sm transition-all">
                    Cancelar
                </a>
                <button type="submit" class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-6 rounded-lg text-sm transition-all">
                    Crear proyecto
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
