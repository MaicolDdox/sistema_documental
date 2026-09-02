@extends('layouts.sgd')

@section('title', 'Reportes')
@section('header', 'Generación de Reportes')

@section('content')
<div class="mb-8">
    <h2 class="text-sm font-semibold text-slate-800">Reportes Disponibles</h2>
    <p class="text-xs text-slate-500 mt-0.5">Selecciona el tipo de reporte que deseas generar y exportar.</p>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">

    @can('reportes.usuarios_por_rol')
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col hover:border-[#39A900] hover:shadow-md transition-all group">
        <div class="w-12 h-12 rounded-full bg-green-50 text-[#39A900] flex items-center justify-center mb-4 group-hover:bg-[#39A900] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-2">Usuarios por Rol</h3>
        <p class="text-sm text-slate-500 flex-1 mb-5">Listado de todos los usuarios de tu centro de formación, con su rol y estado actual.</p>

        <form action="{{ route('admin.reportes.exportar') }}" method="POST" class="mt-auto">
            @csrf
            <input type="hidden" name="tipo_reporte" value="Usuarios por Rol">
            <div class="flex gap-2">
                @can('reportes.exportar_pdf_excel')
                <button type="submit" name="formato" value="pdf" class="flex-1 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 px-3 rounded-lg text-sm transition-all">PDF</button>
                <button type="submit" name="formato" value="excel" class="flex-1 border border-green-200 bg-green-50 hover:bg-green-100 text-[#39A900] font-semibold py-2 px-3 rounded-lg text-sm transition-all">Excel</button>
                @endcan
            </div>
        </form>
    </div>
    @endcan

    @can('reportes.semilleros_con_metricas')
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col hover:border-[#39A900] hover:shadow-md transition-all group">
        <div class="w-12 h-12 rounded-full bg-green-50 text-[#39A900] flex items-center justify-center mb-4 group-hover:bg-[#39A900] group-hover:text-white transition-colors">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z" /></svg>
        </div>
        <h3 class="text-base font-semibold text-slate-900 mb-2">Semilleros con Métricas</h3>
        <p class="text-sm text-slate-500 flex-1 mb-5">Semilleros de tu centro con su líder, proyectos activos y productos aprobados.</p>

        <form action="{{ route('admin.reportes.exportar') }}" method="POST" class="mt-auto">
            @csrf
            <input type="hidden" name="tipo_reporte" value="Semilleros con Métricas">
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
