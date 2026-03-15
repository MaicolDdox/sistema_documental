<x-app-layout>
    <x-slot name="header">Estado de mis Productos</x-slot>

    <div class="space-y-5">

        {{-- Contadores --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('investigador.estados.index', ['estado' => 'pendiente']) }}"
               class="bg-white rounded-xl border {{ request('estado') === 'pendiente' ? 'border-amber-400' : 'border-slate-200' }} p-4 hover:border-amber-400 transition-all">
                <p class="text-2xl font-bold text-amber-500">{{ $contadores['pendiente'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Pendientes</p>
            </a>
            <a href="{{ route('investigador.estados.index', ['estado' => 'en_revision']) }}"
               class="bg-white rounded-xl border {{ request('estado') === 'en_revision' ? 'border-blue-400' : 'border-slate-200' }} p-4 hover:border-blue-400 transition-all">
                <p class="text-2xl font-bold text-blue-500">{{ $contadores['en_revision'] }}</p>
                <p class="text-xs text-slate-500 mt-1">En revisión</p>
            </a>
            <a href="{{ route('investigador.estados.index', ['estado' => 'aprobado']) }}"
               class="bg-white rounded-xl border {{ request('estado') === 'aprobado' ? 'border-green-400' : 'border-slate-200' }} p-4 hover:border-green-400 transition-all">
                <p class="text-2xl font-bold text-green-600">{{ $contadores['aprobado'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Aprobados</p>
            </a>
            <a href="{{ route('investigador.estados.index', ['estado' => 'rechazado']) }}"
               class="bg-white rounded-xl border {{ request('estado') === 'rechazado' ? 'border-red-400' : 'border-slate-200' }} p-4 hover:border-red-400 transition-all">
                <p class="text-2xl font-bold text-red-500">{{ $contadores['rechazado'] }}</p>
                <p class="text-xs text-slate-500 mt-1">Rechazados</p>
            </a>
        </div>

        {{-- Filtros --}}
        <div class="flex items-center gap-3">
            <a href="{{ route('investigador.estados.index') }}"
               class="px-3 py-1.5 rounded-lg text-sm {{ !request('estado') ? 'bg-[#39A900] text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }} transition-all">
                Todos
            </a>
            @foreach($estadosRevision as $e)
            <a href="{{ route('investigador.estados.index', ['estado' => $e->value]) }}"
               class="px-3 py-1.5 rounded-lg text-sm capitalize {{ request('estado') === $e->value ? 'bg-[#39A900] text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }} transition-all">
                {{ str_replace('_', ' ', $e->value) }}
            </a>
            @endforeach
        </div>

        {{-- Lista de productos --}}
        <div class="space-y-3">
            @forelse($productos as $gp)
            @php
                $estado = $gp->estado_revision?->value;
                $badge = match($estado) {
                    'pendiente'   => 'bg-amber-100 text-amber-700',
                    'en_revision' => 'bg-blue-100 text-blue-700',
                    'aprobado'    => 'bg-green-100 text-green-700',
                    'rechazado'   => 'bg-red-100 text-red-700',
                    default       => 'bg-slate-100 text-slate-600',
                };
            @endphp
            <div class="bg-white rounded-xl border {{ $estado === 'rechazado' ? 'border-red-200' : 'border-slate-200' }} p-5">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badge }}">
                                {{ str_replace('_', ' ', $estado) }}
                            </span>
                            <span class="text-xs text-slate-400">{{ $gp->updated_at->format('d/m/Y') }}</span>
                        </div>
                        <h4 class="text-sm font-semibold text-slate-900">{{ $gp->titulo }}</h4>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $gp->product?->project?->nombre ?? '—' }}</p>

                        @if($estado === 'rechazado' && $gp->observaciones_revision)
                        <div class="mt-2 bg-red-50 rounded-lg p-3">
                            <p class="text-xs font-semibold text-red-700 mb-0.5">Observaciones:</p>
                            <p class="text-xs text-red-700">{{ $gp->observaciones_revision }}</p>
                        </div>
                        @endif

                        @if($gp->reviews->isNotEmpty())
                        <p class="text-xs text-slate-400 mt-2">Última revisión: {{ $gp->reviews->sortByDesc('created_at')->first()?->reviewer?->person?->primer_nombre ?? 'Director' }}
                            — {{ $gp->reviews->sortByDesc('created_at')->first()?->created_at?->format('d/m/Y H:i') }}</p>
                        @endif
                    </div>
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <a href="{{ route('investigador.productos.show', $gp) }}"
                           class="border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-semibold py-1.5 px-3 rounded-lg text-xs transition-all">
                            Ver
                        </a>
                        @if($estado === 'rechazado')
                        <a href="{{ route('investigador.productos.edit', $gp) }}"
                           class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-1.5 px-3 rounded-lg text-xs transition-all">
                            Corregir
                        </a>
                        @endif
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
                <p class="text-slate-400 text-sm">No hay productos con este estado.</p>
                <a href="{{ route('investigador.productos.create') }}" class="mt-2 inline-block text-[#39A900] text-sm font-medium hover:underline">
                    Registrar producto →
                </a>
            </div>
            @endforelse
        </div>

        {{ $productos->links() }}
    </div>
</x-app-layout>
