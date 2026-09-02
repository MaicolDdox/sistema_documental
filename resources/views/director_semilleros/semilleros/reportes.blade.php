<p class="text-xs text-slate-500 mb-4">Reportes descargables filtrados a este semillero.</p>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

    @can('reportes.semilleros_con_metricas')
    <div class="border border-slate-200 rounded-lg p-4 flex flex-col">
        <h4 class="text-sm font-semibold text-slate-900 mb-1">Semillero con Métricas</h4>
        <p class="text-xs text-slate-500 flex-1 mb-4">Integrantes, proyectos y productos de este semillero.</p>
        <form action="{{ route('dir-sem.reportes.exportar') }}" method="POST">
            @csrf
            <input type="hidden" name="tipo_reporte" value="Semilleros con Métricas">
            <input type="hidden" name="semillero_id" value="{{ $semillero->id }}">
            <div class="flex gap-2">
                @can('reportes.exportar_pdf_excel')
                <button type="submit" name="formato" value="pdf" class="flex-1 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 px-3 rounded-lg text-xs">PDF</button>
                <button type="submit" name="formato" value="excel" class="flex-1 border border-green-200 bg-green-50 hover:bg-green-100 text-[#39A900] font-semibold py-2 px-3 rounded-lg text-xs">Excel</button>
                @endcan
            </div>
        </form>
    </div>
    @endcan

    @can('reportes.aprendices_por_semillero')
    <div class="border border-slate-200 rounded-lg p-4 flex flex-col">
        <h4 class="text-sm font-semibold text-slate-900 mb-1">Aprendices del Semillero</h4>
        <p class="text-xs text-slate-500 flex-1 mb-4">Aprendices registrados en los proyectos de este semillero.</p>
        <form action="{{ route('dir-sem.reportes.exportar') }}" method="POST">
            @csrf
            <input type="hidden" name="tipo_reporte" value="Aprendices por Semillero">
            <input type="hidden" name="semillero_id" value="{{ $semillero->id }}">
            <div class="flex gap-2">
                @can('reportes.exportar_pdf_excel')
                <button type="submit" name="formato" value="pdf" class="flex-1 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 px-3 rounded-lg text-xs">PDF</button>
                <button type="submit" name="formato" value="excel" class="flex-1 border border-green-200 bg-green-50 hover:bg-green-100 text-[#39A900] font-semibold py-2 px-3 rounded-lg text-xs">Excel</button>
                @endcan
            </div>
        </form>
    </div>
    @endcan

    @can('reportes.proyectos_por_estado')
    <div class="border border-slate-200 rounded-lg p-4 flex flex-col">
        <h4 class="text-sm font-semibold text-slate-900 mb-1">Proyectos por Estado</h4>
        <p class="text-xs text-slate-500 flex-1 mb-4">Estado de los proyectos vinculados a este semillero.</p>
        <form action="{{ route('dir-sem.reportes.exportar') }}" method="POST">
            @csrf
            <input type="hidden" name="tipo_reporte" value="Proyectos por Estado">
            <input type="hidden" name="semillero_id" value="{{ $semillero->id }}">
            <div class="flex gap-2">
                @can('reportes.exportar_pdf_excel')
                <button type="submit" name="formato" value="pdf" class="flex-1 border border-red-200 bg-red-50 hover:bg-red-100 text-red-600 font-semibold py-2 px-3 rounded-lg text-xs">PDF</button>
                <button type="submit" name="formato" value="excel" class="flex-1 border border-green-200 bg-green-50 hover:bg-green-100 text-[#39A900] font-semibold py-2 px-3 rounded-lg text-xs">Excel</button>
                @endcan
            </div>
        </form>
    </div>
    @endcan

</div>
