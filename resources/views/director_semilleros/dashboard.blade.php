@extends('director_semilleros.layout')

@section('title', 'Dashboard')
@section('header', 'Dashboard General')

@section('content')
<!-- Tarjetas de indicadores -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <!-- Total Semilleros -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center shadow-sm">
        <div class="p-3 rounded-lg bg-green-50 text-[#39A900] mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" />
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Semilleros Activos</p>
            <p class="text-2xl font-bold text-slate-900">{{ \App\Models\Seedling::active()->count() }}</p>
        </div>
    </div>

    <!-- Total Líderes -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center shadow-sm">
        <div class="p-3 rounded-lg bg-blue-50 text-blue-600 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Líderes de Semillero</p>
            <p class="text-2xl font-bold text-slate-900">{{ \App\Models\User::role('lider_semillero')->active()->count() }}</p>
        </div>
    </div>

    <!-- Total Proyectos -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center shadow-sm">
        <div class="p-3 rounded-lg bg-purple-50 text-purple-600 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Total Proyectos</p>
            <p class="text-2xl font-bold text-slate-900">{{ \App\Models\Project::count() }}</p>
        </div>
    </div>

    <!-- Total Documentos -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex items-center shadow-sm">
        <div class="p-3 rounded-lg bg-yellow-50 text-yellow-600 mr-4">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
        </div>
        <div>
            <p class="text-sm font-medium text-slate-500">Documentos</p>
            <p class="text-2xl font-bold text-slate-900">{{ \App\Models\SeedlingFile::count() }}</p>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Accesos Rápidos -->
    <div class="bg-white rounded-xl border border-slate-200">
        <div class="px-5 py-4 border-b border-slate-100">
            <h3 class="text-sm font-semibold text-slate-900">Accesos Rápidos</h3>
            <p class="text-xs text-slate-400 mt-0.5">Acciones frecuentes del módulo</p>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 gap-4">
            @can('semilleros.crear')
            <a href="{{ route('dir-sem.semilleros.create') }}" class="flex items-center p-4 border border-slate-200 rounded-lg hover:border-[#39A900] hover:bg-green-50 transition-colors group">
                <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-[#39A900]/10 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                </div>
                <span class="text-sm font-medium text-slate-700 group-hover:text-[#39A900]">Nuevo Semillero</span>
            </a>
            @endcan

            @can('usuarios.crear_lider_semillero')
            <a href="{{ route('dir-sem.lideres.create') }}" class="flex items-center p-4 border border-slate-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors group">
                <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-blue-100 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.66-1.548c0 .12.008.239.025.358A4.5 4.5 0 014 19.235z" /></svg>
                </div>
                <span class="text-sm font-medium text-slate-700 group-hover:text-blue-600">Nuevo Líder</span>
            </a>
            @endcan
            
            @can('documentos.subir')
            <a href="{{ route('dir-sem.documentos.create') }}" class="flex items-center p-4 border border-slate-200 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition-colors group">
                <div class="w-10 h-10 rounded-full bg-slate-100 group-hover:bg-yellow-100 flex items-center justify-center mr-3">
                    <svg class="w-5 h-5 text-slate-500 group-hover:text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" /></svg>
                </div>
                <span class="text-sm font-medium text-slate-700 group-hover:text-yellow-600">Subir Documento</span>
            </a>
            @endcan
        </div>
    </div>
</div>
@endsection
