<x-app-layout>
    <x-slot name="header">Productos Minciencias</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-700">Administración</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Productos Minciencias</span>
    </nav>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Productos Minciencias</h2>
        <p class="text-sm text-slate-500 mt-1">Productos de co-investigadores vinculados a tu centro de formación, pendientes o ya revisados.</p>
    </div>

    @if(session('success'))
    <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
    @endif

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-4 py-3">Producto</th>
                        <th class="text-left px-4 py-3">Co-investigador</th>
                        <th class="text-left px-4 py-3">Línea de investigación</th>
                        <th class="text-left px-4 py-3">Archivos</th>
                        <th class="text-left px-4 py-3">Revisión</th>
                        <th class="text-right px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($productos as $p)
                    @php $revVal = $p->estado_revision?->value ?? 'pendiente'; @endphp
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $p->nombre }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $p->user?->person?->nombre_completo ?? $p->user?->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $p->researchLine?->nombre ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $p->files_count }}</td>
                        <td class="px-4 py-3">
                            @if($revVal === 'aprobado')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Aprobado</span>
                            @elseif($revVal === 'rechazado')
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Rechazado</span>
                            @else
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">Pendiente</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.minciencias.show', $p) }}" class="text-[#39A900] hover:underline text-xs font-medium">Ver / Revisar</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500 text-sm">No hay productos Minciencias vinculados a tu centro de formación.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
