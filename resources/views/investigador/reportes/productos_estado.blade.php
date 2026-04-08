<x-app-layout>
    <x-slot name="header">Productos por Estado</x-slot>

    <div class="max-w-4xl">
        <div class="mb-4">
            <a href="{{ route('investigador.reportes.index') }}" class="text-sm text-[#39A900] hover:underline font-medium">&larr; Volver a Reportes</a>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="font-semibold text-slate-800">Desglose de productos</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="border border-slate-100 rounded-lg p-4 bg-slate-50">
                        <div class="text-3xl font-bold text-amber-500">{{ $data['pendiente'] }}</div>
                        <div class="text-xs uppercase font-semibold text-slate-500 mt-1">Pendientes</div>
                    </div>
                    <div class="border border-slate-100 rounded-lg p-4 bg-slate-50">
                        <div class="text-3xl font-bold text-blue-500">{{ $data['en_revision'] }}</div>
                        <div class="text-xs uppercase font-semibold text-slate-500 mt-1">En Revisión</div>
                    </div>
                    <div class="border border-slate-100 rounded-lg p-4 bg-slate-50">
                        <div class="text-3xl font-bold text-green-600">{{ $data['aprobado'] }}</div>
                        <div class="text-xs uppercase font-semibold text-slate-500 mt-1">Aprobados</div>
                    </div>
                    <div class="border border-slate-100 rounded-lg p-4 bg-slate-50">
                        <div class="text-3xl font-bold text-red-500">{{ $data['rechazado'] }}</div>
                        <div class="text-xs uppercase font-semibold text-slate-500 mt-1">Rechazados</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="sgd-table">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Proyecto asociado</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($productos as $p)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $p->titulo }}</td>
                            <td class="text-slate-500">{{ $p->product->project->nombre ?? 'N/A' }}</td>
                            <td>
                                @if($p->estado_revision->value === 'pendiente')
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-amber-100 text-amber-800">Pendiente</span>
                                @elseif($p->estado_revision->value === 'en_revision')
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-blue-100 text-blue-800">En revisión</span>
                                @elseif($p->estado_revision->value === 'aprobado')
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-green-100 text-green-800">Aprobado</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded text-xs font-semibold bg-red-100 text-red-800">Rechazado</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="text-center py-6 text-slate-500">No hay productos registrados.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
