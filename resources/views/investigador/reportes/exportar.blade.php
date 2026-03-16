<x-app-layout>
    <x-slot name="header">Exportar Productos Aprobados</x-slot>

    <div class="max-w-4xl">
        <div class="mb-4">
            <a href="{{ route('investigador.reportes.index') }}" class="text-sm text-[#39A900] hover:underline font-medium">&larr; Volver a Reportes</a>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 p-6 mb-6">
            <div class="flex items-start justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-800 mb-1">Listado para Exportación</h2>
                    <p class="text-sm text-slate-500">A continuación se muestran todos tus productos que actualmente están <span class="font-semibold text-green-700">Aprobados</span> por el director del grupo. Esta lista está lista para ser exportada o copiada.</p>
                </div>
                <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-colors flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0v2.796c0 .12.048.235.134.32l1.92 1.921m7.04-4.471a48.536 48.536 0 0110.5 0v2.796c0 .12-.048.235-.134.32l-1.92 1.921m-7.04-4.471L12 8.25" />
                    </svg>
                    Imprimir / Guardar PDF
                </button>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="sgd-table w-full text-left">
                    <thead>
                        <tr>
                            <th>Título del Producto</th>
                            <th>Año pub.</th>
                            <th>Tipología</th>
                            <th>Área Conocimiento</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($productos as $p)
                        <tr>
                            <td class="py-3 px-4">
                                <p class="font-medium text-slate-900">{{ $p->titulo }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">Proyecto: {{ $p->product->project->nombre ?? 'N/A' }}</p>
                            </td>
                            <td class="py-3 px-4">{{ $p->anio_publicacion }}</td>
                            <td class="py-3 px-4 text-sm text-slate-600 truncate max-w-[150px]" title="{{ $p->mincienciasTypology->nombre ?? 'N/A' }}">
                                {{ $p->mincienciasTypology->nombre ?? 'N/A' }}
                            </td>
                            <td class="py-3 px-4 text-sm text-slate-600 truncate max-w-[150px]" title="{{ $p->knowledgeArea->nombre ?? 'N/A' }}">
                                {{ $p->knowledgeArea->nombre ?? 'N/A' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-8 text-slate-500">
                                Aún no tienes productos aprobados.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
