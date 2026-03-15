<x-app-layout>
    <x-slot name="header">Bandeja de Semilleros</x-slot>

    <div class="space-y-4">

        <div class="mb-4">
            <h1 class="text-2xl font-bold text-slate-900">Productos de Semilleros</h1>
            <p class="text-sm text-slate-500 mt-0.5">Productos pre-aprobados listos para ser formalizados ante el Director de Grupo</p>
        </div>

        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ $errors->first('error') }}</div>
        @endif

        {{-- Tabla de productos entrantes --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-100 bg-slate-50">
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Título del Producto</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Proyecto Asociado</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Autor en Semillero</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Evidencia</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wide">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($productos as $prod)
                    <tr class="border-b border-slate-100 hover:bg-slate-50 transition-colors">
                        <td class="px-4 py-3">
                            <p class="font-medium text-slate-800 line-clamp-1">{{ $prod->nombre }}</p>
                            <p class="text-xs text-slate-400">Enviado: {{ $prod->updated_at->format('d/m/Y') }}</p>
                        </td>
                        <td class="px-4 py-3 text-slate-600 text-xs">{{ $prod->project?->nombre ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-600">
                            @php
                                $autorSecundario = $prod->productAuthors->first()?->projectAuthor?->user;
                            @endphp
                            {{ $autorSecundario ? ($autorSecundario->person?->nombre_completo ?? $autorSecundario->email) : 'Desconocido' }}
                        </td>
                        <td class="px-4 py-3 text-slate-600">
                            @if($prod->url_repositorio)
                                <a href="{{ $prod->url_repositorio }}" target="_blank" class="text-[#39A900] hover:underline flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                    </svg>
                                    URL Repo
                                </a>
                            @elseif($prod->archivo)
                                <span class="text-slate-500 flex items-center gap-1" title="El archivo se adjuntará automáticamente al formalizar">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                                    </svg>
                                    Documento Múltiple
                                </span>
                            @else
                                <span class="text-slate-400">Sin archivo</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('investigador.productos.formalizar', $prod) }}"
                               class="bg-[#39A900] hover:bg-[#2d8500] text-white font-semibold py-1.5 px-3 rounded-lg text-xs transition-all inline-flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zM19.5 7.125L16.862 4.487" />
                                </svg>
                                Formalizar
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-slate-400 text-sm">
                            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-3">
                                <svg class="w-6 h-6 text-slate-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 9v.906a2.25 2.25 0 01-1.183 1.981l-6.478 3.488M2.25 9v.906a2.25 2.25 0 001.183 1.981l6.478 3.488m8.839 2.51l-4.66-2.51m0 0l-1.023-.55a2.25 2.25 0 00-2.134 0l-1.022.55m0 0l-4.661 2.51m16.5 1.615a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V8.844c0-1.136.84-2.099 1.97-2.193a48.56 48.56 0 0115.06 0c1.13.094 1.97 1.057 1.97 2.193v8.611z" />
                                </svg>
                            </div>
                            No tienes productos de semillero pendientes por formalizar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $productos->links() }}
    </div>
</x-app-layout>
