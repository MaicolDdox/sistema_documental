<x-app-layout>
    <x-slot name="header">Revisión de Productos</x-slot>

    <div class="mb-6 flex items-center justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">Productos del Grupo</h1>
            <p class="text-sm text-slate-500 mt-0.5">Revisa y valida los productos de investigación</p>
        </div>
    </div>

    {{-- Filtros --}}
    <form method="GET" action="{{ route('director.productos.index') }}"
          class="bg-white rounded-xl border border-slate-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Estado de revisión</label>
            <select name="estado_revision"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                <option value="">Todos</option>
                @foreach($estadosRevision as $e)
                    <option value="{{ $e->value }}" {{ request('estado_revision') === $e->value ? 'selected' : '' }}>
                        {{ ucfirst(str_replace('_', ' ', $e->value)) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-600 mb-1">Año</label>
            <select name="anio"
                    class="border border-slate-200 rounded-lg px-3 py-2 text-sm bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10">
                <option value="">Todos</option>
                @foreach($anios as $anio)
                    <option value="{{ $anio }}" {{ request('anio') == $anio ? 'selected' : '' }}>{{ $anio }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="sgd-btn-primary px-4 py-2 rounded-lg text-sm font-medium">Filtrar</button>
        @if(request()->anyFilled(['estado_revision','anio','investigador_id']))
            <a href="{{ route('director.productos.index') }}" class="text-sm text-slate-500 hover:text-red-500 py-2">✕ Limpiar</a>
        @endif
    </form>

    <div class="sgd-table-card bg-white overflow-hidden">
        <div class="overflow-x-auto">
            <table class="sgd-table text-sm">
                <thead>
                    <tr>
                        <th class="text-left">Producto</th>
                        <th class="text-left">Investigador</th>
                        <th class="text-left">Año</th>
                        <th class="text-left">Estado</th>
                        <th class="text-left">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $gp)
                    <tr>
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800 max-w-[220px] truncate">
                                {{ $gp->product?->titulo ?? $gp->titulo ?? '—' }}
                            </p>
                            <p class="text-xs text-slate-400">{{ $gp->mincienciasTypology?->nombre ?? '' }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            {{ $gp->author?->person?->primer_nombre ?? $gp->author?->email ?? '—' }}
                            {{ $gp->author?->person?->primer_apellido ?? '' }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $gp->anio_publicacion ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php $estado = $gp->estado_revision?->value ?? 'pendiente'; @endphp
                            @if($estado === 'aprobado')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aprobado
                                </span>
                            @elseif($estado === 'rechazado')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Rechazado
                                </span>
                            @elseif($estado === 'en_revision')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>En revisión
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Pendiente
                                </span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('director.productos.show', $gp) }}"
                               class="text-[#39A900] hover:underline text-xs font-semibold">
                                Revisar →
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-12 text-center text-slate-500">
                            No hay productos registrados con los filtros aplicados.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($productos->hasPages())
        <div class="px-4 py-3 border-t border-slate-100">
            {{ $productos->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
