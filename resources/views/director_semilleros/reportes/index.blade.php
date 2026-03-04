@extends('director_semilleros.layout')

@section('title', 'Reportes')
@section('header', 'Generación de Reportes')

@section('content')
<div class="mb-8">
    <h2 class="text-sm font-semibold text-slate-800">Reportes Disponibles</h2>
    <p class="text-xs text-slate-500 mt-0.5">Selecciona el tipo de reporte que deseas generar y exportar.</p>
</div>

<!-- Opciones de Reportes -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
    
    @can('reportes.semilleros_con_metricas')
    <!-- Tarjeta Reporte 1 -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col hover:border-[#39A900] hover:shadow-md transition-all group">
        <div class="w-12 h-12 rounded-full bg-green-50 text-[#39A900] flex items-center justify-center mb-4 group-hover:bg-[#39A900] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-2">Semilleros con Métricas</h3>
        <p class="text-sm text-slate-500 flex-1 mb-5">Obtén un listado general de los semilleros del centro con la cantidad de integrantes, proyectos y productos activos.</p>
        
        <form action="{{ route('dir-sem.reportes.exportar') }}" method="POST" class="mt-auto">
            @csrf
            <input type="hidden" name="tipo_reporte" value="Semilleros con Métricas">
            <div class="flex gap-2">
                @can('reportes.exportar_pdf_excel')
                <button type="submit" name="formato" value="pdf" class="flex-1 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 px-3 rounded-lg text-sm transition-all flex items-center justify-center gap-1">
                    PDF
                </button>
                <button type="submit" name="formato" value="excel" class="flex-1 border border-green-200 bg-green-50 hover:bg-green-100 text-[#39A900] font-semibold py-2 px-3 rounded-lg text-sm transition-all flex items-center justify-center gap-1">
                    Excel
                </button>
                @endcan
            </div>
        </form>
    </div>
    @endcan

    @can('reportes.aprendices_por_semillero')
    <!-- Tarjeta Reporte 2 -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col hover:border-blue-400 hover:shadow-md transition-all group">
        <div class="w-12 h-12 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center mb-4 group-hover:bg-blue-500 group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443m-7.007 11.55A5.981 5.981 0 006.75 15.75v-1.5" /></svg>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-2">Aprendices por Semillero</h3>
        <p class="text-sm text-slate-500 flex-1 mb-5">Detalle nominal de los integrantes clasificados por cada semillero activo en el centro de formación.</p>
        
        <form action="{{ route('dir-sem.reportes.exportar') }}" method="POST" class="mt-auto">
            @csrf
            <input type="hidden" name="tipo_reporte" value="Aprendices por Semillero">
            <div class="flex gap-2">
                @can('reportes.exportar_pdf_excel')
                <button type="submit" name="formato" value="pdf" class="flex-1 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 px-3 rounded-lg text-sm transition-all flex items-center justify-center gap-1">
                    PDF
                </button>
                <button type="submit" name="formato" value="excel" class="flex-1 border border-green-200 bg-green-50 hover:bg-green-100 text-[#39A900] font-semibold py-2 px-3 rounded-lg text-sm transition-all flex items-center justify-center gap-1">
                    Excel
                </button>
                @endcan
            </div>
        </form>
    </div>
    @endcan

    @can('reportes.proyectos_por_estado')
    <!-- Tarjeta Reporte 3 -->
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col hover:border-purple-400 hover:shadow-md transition-all group">
        <div class="w-12 h-12 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center mb-4 group-hover:bg-purple-500 group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15.042 21.672L13.684 16.6m0 0l-2.51 2.225.569-9.47 5.227 7.917-3.286-.672zm-7.518-.267A8.25 8.25 0 1120.25 10.5M8.288 14.212A5.25 5.25 0 1117.25 10.5" /></svg>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-2">Proyectos por Estado</h3>
        <p class="text-sm text-slate-500 flex-1 mb-5">Estado actual de todos los proyectos de investigación vinculados a los semilleros (En formulación, En ejecución, Terminados, etc).</p>
        
        <form action="{{ route('dir-sem.reportes.exportar') }}" method="POST" class="mt-auto">
            @csrf
            <input type="hidden" name="tipo_reporte" value="Proyectos por Estado">
            <div class="flex gap-2">
                @can('reportes.exportar_pdf_excel')
                <button type="submit" name="formato" value="pdf" class="flex-1 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 px-3 rounded-lg text-sm transition-all flex items-center justify-center gap-1">
                    PDF
                </button>
                <button type="submit" name="formato" value="excel" class="flex-1 border border-green-200 bg-green-50 hover:bg-green-100 text-[#39A900] font-semibold py-2 px-3 rounded-lg text-sm transition-all flex items-center justify-center gap-1">
                    Excel
                </button>
                @endcan
            </div>
        </form>
    </div>
    @endcan

</div>

<!-- Filtros Adicionales (Visual) -->
<div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden mb-8">
    <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
        <h3 class="text-sm font-semibold text-slate-900">Aplicar filtros globales a reportes</h3>
        <p class="text-xs text-slate-500 mt-0.5">Opcionalmente, puedes aplicar estos filtros antes de generar la descarga.</p>
    </div>
    <div class="p-6 grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Semillero Específico</label>
            <select class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] disabled:bg-slate-50" disabled>
                <option value="">Selecciona (Próximamente)</option>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Rango de Fechas (Desde)</label>
            <input type="date" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] disabled:bg-slate-50" disabled>
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-700 mb-1.5">Rango de Fechas (Hasta)</label>
            <input type="date" class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 focus:border-[#39A900] disabled:bg-slate-50" disabled>
        </div>
    </div>
</div>

@endsection
