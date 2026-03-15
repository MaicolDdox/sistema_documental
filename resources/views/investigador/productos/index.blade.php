<x-app-layout>
    <x-slot name="header">Mis Productos</x-slot>

    <div class="space-y-4">

        {{-- Filtros + toolbar --}}
        <div class="flex items-center justify-between gap-4">
            <form method="GET" action="{{ route('investigador.productos.index') }}" class="flex items-center gap-2">
                <select name="estado_revision" onchange="this.form.submit()"
                        class="border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 transition-all">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" {{ request('estado_revision') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                    <option value="en_revision" {{ request('estado_revision') === 'en_revision' ? 'selected' : '' }}>En revisión</option>
                    <option value="aprobado" {{ request('estado_revision') === 'aprobado' ? 'selected' : '' }}>Aprobado</option>
                    <option value="rechazado" {{ request('estado_revision') === 'rechazado' ? 'selected' : '' }}>Rechazado</option>
                </select>
            </form>
            <a href="{{ route('investigador.productos.create') }}"
               class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-2.5 px-4 rounded-lg text-sm transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                Registrar producto
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ $errors->first('error') }}</div>
        @endif

        {{-- Tabla de productos --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Título</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Proyecto</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Año</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Estado</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $gp)
                    @php
                        $badge = match($gp->estado_revision?->value) {
                            'pendiente'   => ['bg-amber-100 text-amber-700', 'bg-amber-500', 'Pendiente'],
                            'en_revision' => ['bg-blue-100 text-blue-700', 'bg-blue-500', 'En revisión'],
                            'aprobado'    => ['bg-green-100 text-green-700', 'bg-green-500', 'Aprobado'],
                            'rechazado'   => ['bg-red-100 text-red-700', 'bg-red-500', 'Rechazado'],
                            default       => ['bg-slate-100 text-slate-600', 'bg-slate-400', 'Desconocido'],
                        };
                    @endphp
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800 line-clamp-1">{{ $gp->titulo }}</p>
                            <p class="text-xs text-slate-400">{{ $gp->mincienciasTypology?->nombre ?? '—' }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $gp->product?->project?->nombre ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">{{ $gp->anio_publicacion }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge[0] }}">
                                <div class="w-1.5 h-1.5 rounded-full {{ $badge[1] }}"></div>
                                {{ $badge[2] }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2 justify-end">
                                <a href="{{ route('investigador.productos.show', $gp) }}"
                                   class="text-slate-400 hover:text-[#39A900] transition-colors" title="Ver">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.574-3.007-9.964-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </a>
                                @if($gp->estado_revision?->value === 'rechazado')
                                <a href="{{ route('investigador.productos.edit', $gp) }}"
                                   class="text-amber-500 hover:text-amber-700 transition-colors" title="Corregir">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Z" />
                                    </svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400 text-sm">
                            No tienes productos registrados.
                            <a href="{{ route('investigador.productos.create') }}" class="text-[#39A900] font-medium">Registra tu primer producto</a>.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $productos->links() }}
    </div>
</x-app-layout>
