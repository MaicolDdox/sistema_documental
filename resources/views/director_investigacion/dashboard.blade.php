<x-app-layout>
    <x-slot name="header">Dashboard — Grupo de Investigación</x-slot>

    @php
        $grupoId = \App\Models\ResearchGroupUser::where('user_id', auth()->id())
            ->where('rol', \App\Enums\RolGrupoEnum::Director)
            ->value('research_group_id');

        $grupo   = $grupoId ? \App\Models\ResearchGroup::find($grupoId) : null;
        $userIds = $grupoId
            ? \App\Models\ResearchGroupUser::where('research_group_id', $grupoId)->pluck('user_id')
            : collect();

        $totalInvestigadores = $userIds->count();
        $productosPendientes = \App\Models\GroupProduct::whereIn('author_id', $userIds)
            ->where('estado_revision', \App\Enums\EstadoRevisionEnum::Pendiente)->count();
        $productosAprobados  = \App\Models\GroupProduct::whereIn('author_id', $userIds)
            ->where('estado_revision', \App\Enums\EstadoRevisionEnum::Aprobado)->count();
        $productosTotal      = \App\Models\GroupProduct::whereIn('author_id', $userIds)->count();

        $ultimosProductos = \App\Models\GroupProduct::with(['author.person', 'product'])
            ->whereIn('author_id', $userIds)
            ->latest()->take(6)->get();
    @endphp

    {{-- Encabezado --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
        <p class="text-sm text-slate-500 mt-0.5">
            {{ $grupo?->nombre ?? 'Grupo de Investigación' }} — Panel del Director
        </p>
    </div>

    {{-- 4 tarjetas de resumen --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500 mb-1">Investigadores</p>
            <p class="text-2xl font-bold text-slate-900">{{ $totalInvestigadores }}</p>
            <p class="text-xs text-slate-500 mt-1 border-b-2 border-[#39A900] pb-0.5 w-fit">en mi grupo</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-xs font-medium text-slate-500 mb-1">Productos totales</p>
            <p class="text-2xl font-bold text-slate-900">{{ $productosTotal }}</p>
            <p class="text-xs text-slate-500 mt-1">registrados</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-amber-100 p-5 shadow-sm bg-amber-50/40">
            <p class="text-xs font-medium text-amber-600 mb-1">Pendientes de revisión</p>
            <p class="text-2xl font-bold text-amber-700">{{ $productosPendientes }}</p>
            <a href="{{ route('director.productos.index', ['estado_revision' => 'pendiente']) }}"
               class="text-xs text-amber-600 mt-1 hover:underline block">Ver pendientes →</a>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-green-100 p-5 shadow-sm bg-green-50/40">
            <p class="text-xs font-medium text-green-600 mb-1">Aprobados</p>
            <p class="text-2xl font-bold text-green-700">{{ $productosAprobados }}</p>
            <p class="text-xs text-green-600 mt-1">productos validados</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Últimos productos --}}
        <div class="lg:col-span-2">
            <div class="sgd-table-card bg-white overflow-hidden">
                <div class="px-5 py-4 flex items-center justify-between border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <div>
                        <h2 class="text-base font-semibold text-slate-900">Últimos Productos</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Productos de investigadores del grupo</p>
                    </div>
                    <a href="{{ route('director.productos.index') }}"
                       class="sgd-btn-primary px-4 py-2 rounded-xl text-sm font-medium">
                        Ver todos
                    </a>
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Producto</th>
                                <th class="text-left">Investigador</th>
                                <th class="text-left">Estado</th>
                                <th class="text-left">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ultimosProductos as $gp)
                            <tr>
                                <td class="px-4 py-3 font-medium text-slate-800 max-w-[200px] truncate">
                                    {{ $gp->product?->titulo ?? $gp->titulo ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    {{ $gp->author?->person?->primer_nombre ?? $gp->author?->email ?? '—' }}
                                    {{ $gp->author?->person?->primer_apellido ?? '' }}
                                </td>
                                <td class="px-4 py-3">
                                    @php $estado = $gp->estado_revision->value ?? 'pendiente'; @endphp
                                    @if($estado === 'aprobado')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>Aprobado
                                        </span>
                                    @elseif($estado === 'rechazado')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span>Rechazado
                                        </span>
                                    @elseif($estado === 'en_revision')
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>En revisión
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('director.productos.show', $gp) }}"
                                       class="text-[#39A900] hover:underline text-xs font-medium">Revisar</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-slate-500">
                                    No hay productos registrados en el grupo aún.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Acciones rápidas --}}
        <div class="space-y-4">
            <div class="sgd-card bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50">
                    <h2 class="text-base font-semibold text-slate-900">Acciones Rápidas</h2>
                </div>
                <div class="p-4 space-y-2">
                    <a href="{{ route('director.investigadores.create') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                        <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z"/>
                        </svg>
                        Nuevo Investigador
                    </a>
                    <a href="{{ route('director.productos.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                        <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/>
                        </svg>
                        Revisar Productos
                        @if($productosPendientes > 0)
                            <span class="ml-auto bg-amber-100 text-amber-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $productosPendientes }}</span>
                        @endif
                    </a>
                    <a href="{{ route('director.documentos.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                        <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12"/>
                        </svg>
                        Documentos del Grupo
                    </a>
                    <a href="{{ route('director.reportes.index') }}"
                       class="flex items-center gap-3 px-4 py-2.5 rounded-xl border border-slate-200 text-sm font-medium text-slate-700 hover:bg-[#f0fdf4] hover:border-[#39A900]/30 transition-all">
                        <svg class="w-4 h-4 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/>
                        </svg>
                        Reportes
                    </a>
                </div>
            </div>

            {{-- Info del grupo --}}
            @if($grupo)
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-900 mb-3">Mi Grupo</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-slate-500">Nombre</span>
                        <span class="font-medium text-slate-800 text-right max-w-[140px]">{{ $grupo->nombre }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Código</span>
                        <span class="font-mono text-slate-700">{{ $grupo->codigo }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-slate-500">Estado</span>
                        <span class="inline-flex items-center gap-1 text-green-700 text-xs font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            {{ ucfirst($grupo->estado->value ?? 'activo') }}
                        </span>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
