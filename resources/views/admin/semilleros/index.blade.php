<x-app-layout>
    <x-slot name="header">Semilleros</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-700">Administración</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">Semilleros</span>
    </nav>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Semilleros de Investigación</h2>
        <p class="text-sm text-slate-500 mt-1">Vista de solo lectura: líder de semillero, proyectos, líder de proyecto, integrantes y co-investigadores. La gestión (crear/editar/eliminar) la hace quien creó cada registro.</p>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="text-left px-4 py-3">Semillero</th>
                        <th class="text-left px-4 py-3">Líder de Semillero</th>
                        <th class="text-left px-4 py-3">Proyectos</th>
                        <th class="text-left px-4 py-3">Estado</th>
                        <th class="text-right px-4 py-3">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($semilleros as $s)
                    <tr>
                        <td class="px-4 py-3 font-medium text-slate-900">{{ $s->nombre }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $s->leader?->person?->nombre_completo ?? $s->leader?->email ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $s->projects_count }}</td>
                        <td class="px-4 py-3">
                            @if($s->estado?->value === 'activo')
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo</span>
                            @else
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.semilleros.show', $s) }}" class="text-[#39A900] hover:underline text-xs font-medium">Ver detalle</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-500 text-sm">No hay semilleros registrados en tu centro de formación.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
