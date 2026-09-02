@extends('layouts.sgd')

@section('title', 'Reportes')
@section('header', 'Generación de Reportes')

@section('content')
<div class="mb-8">
    <h2 class="text-sm font-semibold text-slate-800">Reportes Disponibles</h2>
    <p class="text-xs text-slate-500 mt-0.5">Selecciona el tipo de reporte que deseas generar y exportar.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

    @can('reportes.semilleros_con_metricas')
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col hover:border-[#39A900] hover:shadow-md transition-all group">
        <div class="w-12 h-12 rounded-full bg-green-50 text-[#39A900] flex items-center justify-center mb-4 group-hover:bg-[#39A900] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" /></svg>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-2">Proyectos del Semillero</h3>
        <p class="text-sm text-slate-500 flex-1 mb-5">Proyectos de tu semillero con su líder, estado y cantidad de evidencias.</p>

        <form action="{{ route('lider-sem.reportes.exportar') }}" method="POST" class="mt-auto">
            @csrf
            <input type="hidden" name="tipo_reporte" value="Proyectos del Semillero">
            <div class="flex gap-2">
                @can('reportes.exportar_pdf_excel')
                <button type="submit" name="formato" value="pdf" class="flex-1 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 px-3 rounded-lg text-sm transition-all">PDF</button>
                <button type="submit" name="formato" value="excel" class="flex-1 border border-green-200 bg-green-50 hover:bg-green-100 text-[#39A900] font-semibold py-2 px-3 rounded-lg text-sm transition-all">Excel</button>
                @endcan
            </div>
        </form>
    </div>
    @endcan

    @can('reportes.aprendices_por_semillero')
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col hover:border-[#39A900] hover:shadow-md transition-all group">
        <div class="w-12 h-12 rounded-full bg-green-50 text-[#39A900] flex items-center justify-center mb-4 group-hover:bg-[#39A900] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.26 10.147a60.438 60.438 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.636 50.636 0 00-2.658-.813A59.906 59.906 0 0112 3.493a59.903 59.903 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5z" /></svg>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-2">Aprendices por Semillero</h3>
        <p class="text-sm text-slate-500 flex-1 mb-5">Aprendices registrados en los proyectos de tu semillero, con su ficha y tecnólogo.</p>

        <form action="{{ route('lider-sem.reportes.exportar') }}" method="POST" class="mt-auto">
            @csrf
            <input type="hidden" name="tipo_reporte" value="Aprendices por Semillero">
            <div class="flex gap-2">
                @can('reportes.exportar_pdf_excel')
                <button type="submit" name="formato" value="pdf" class="flex-1 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 px-3 rounded-lg text-sm transition-all">PDF</button>
                <button type="submit" name="formato" value="excel" class="flex-1 border border-green-200 bg-green-50 hover:bg-green-100 text-[#39A900] font-semibold py-2 px-3 rounded-lg text-sm transition-all">Excel</button>
                @endcan
            </div>
        </form>
    </div>
    @endcan

</div>
@endsection
