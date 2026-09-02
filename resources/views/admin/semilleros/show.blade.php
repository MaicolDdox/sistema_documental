<x-app-layout>
    <x-slot name="header">{{ $semillero->nombre }}</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('admin.dashboard') }}" class="hover:text-slate-700">Administración</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.semilleros.index') }}" class="hover:text-slate-700">Semilleros</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">{{ $semillero->nombre }}</span>
    </nav>

    <div class="space-y-6">

        {{-- Card: Semillero + líder --}}
        <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">{{ $semillero->nombre }}</h2>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $semillero->estado?->value === 'activo' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ $semillero->estado?->value === 'activo' ? 'Activo' : 'Inactivo' }}
                </span>
            </div>
            <div class="p-6">
                <p class="text-sm text-slate-700 mb-4">{{ $semillero->descripcion ?? 'Sin descripción registrada.' }}</p>
                <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Líder de Semillero</p>
                @if($semillero->leader)
                <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-lg px-3 py-2.5 w-fit">
                    <span class="w-8 h-8 rounded-full bg-[#39A900]/20 flex items-center justify-center text-xs font-bold text-[#39A900]">{{ $semillero->leader->initials() }}</span>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ $semillero->leader->person?->nombre_completo ?? $semillero->leader->email }}</p>
                        <p class="text-xs text-slate-500">{{ $semillero->leader->email }}</p>
                    </div>
                </div>
                @else
                <p class="text-xs text-slate-400">Sin líder de semillero asignado.</p>
                @endif
            </div>
        </div>

        {{-- Cards: un card por proyecto (líder de proyecto + integrantes + co-investigadores) --}}
        <div>
            <h3 class="text-sm font-semibold text-slate-900 mb-3">Proyectos ({{ $semillero->projects->count() }})</h3>
            <div class="space-y-4">
                @forelse($semillero->projects as $proyecto)
                <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                    <div class="px-5 py-3 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                        <h4 class="text-sm font-semibold text-slate-900">{{ $proyecto->nombre }}</h4>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold {{ $proyecto->estado?->value === 'activo' ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-600' }}">
                            {{ $proyecto->estado?->value === 'activo' ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                    <div class="p-5 space-y-4">
                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Líder de Proyecto</p>
                            @if($proyecto->liderProyecto)
                            <div class="flex items-center gap-3 bg-slate-50 border border-slate-100 rounded-lg px-3 py-2 w-fit">
                                <span class="w-7 h-7 rounded-full bg-[#39A900]/20 flex items-center justify-center text-xs font-bold text-[#39A900]">{{ $proyecto->liderProyecto->initials() }}</span>
                                <div>
                                    <p class="text-sm font-medium text-slate-800">{{ $proyecto->liderProyecto->person?->nombre_completo ?? $proyecto->liderProyecto->email }}</p>
                                    <p class="text-xs text-slate-500">{{ $proyecto->liderProyecto->email }}</p>
                                </div>
                            </div>
                            @else
                            <p class="text-xs text-slate-400">Sin líder de proyecto asignado.</p>
                            @endif
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Integrantes / Aprendices ({{ $proyecto->learners->count() }})</p>
                            <div class="space-y-1.5">
                                @forelse($proyecto->learners as $aprendiz)
                                <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-lg px-3 py-2 text-xs">
                                    <span class="font-medium text-slate-800">{{ $aprendiz->nombre_completo }}</span>
                                    <span class="text-slate-500">Doc: {{ $aprendiz->numero_documento }} @if($aprendiz->ficha) · Ficha: {{ $aprendiz->ficha }} @endif</span>
                                </div>
                                @empty
                                <p class="text-xs text-slate-400">Sin aprendices registrados.</p>
                                @endforelse
                            </div>
                        </div>

                        <div>
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-2">Co-investigadores ({{ $proyecto->authors->count() }})</p>
                            <div class="space-y-1.5">
                                @forelse($proyecto->authors as $coinvestigador)
                                <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-lg px-3 py-2 text-xs">
                                    <span class="font-medium text-slate-800">{{ $coinvestigador->person?->nombre_completo ?? $coinvestigador->email }}</span>
                                    <span class="text-slate-500">{{ $coinvestigador->email }}</span>
                                </div>
                                @empty
                                <p class="text-xs text-slate-400">Sin co-investigadores vinculados.</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <div class="bg-white rounded-xl border border-slate-200 p-8 text-center text-sm text-slate-500">
                    Este semillero aún no tiene proyectos registrados.
                </div>
                @endforelse
            </div>
        </div>

    </div>
</x-app-layout>
