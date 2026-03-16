<x-app-layout>
<x-slot name="header">Mis Semilleros y Proyectos</x-slot>

{{-- Acciones de página --}}
<div class="flex items-center justify-between mb-6">
    <div></div>
    <div>
<div x-data="{ openExport: false }" class="flex items-center gap-2">
    <button @click="openExport = true" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-all shadow-sm">
        <svg class="w-4 h-4" transform="rotate(180)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
        Descargar Reportes
    </button>

    {{-- Modal de Exportación --}}
    <div x-show="openExport" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm" x-cloak>
        <div @click.away="openExport = false" class="bg-white rounded-2xl p-6 w-full max-w-md shadow-xl border border-slate-100 text-left" x-transition>
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-slate-800">Descargar Reporte Semilleros</h3>
                <button @click="openExport = false" class="text-slate-400 hover:text-red-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="GET" action="{{ route('asesor.exportar.semilleros') }}">
                <div class="mb-5">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Rango de tiempo (Opcional)</label>
                    <select name="rango" class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 outline-none pr-8">
                        <option value="">Todo el histórico</option>
                        <option value="hoy">El día de hoy</option>
                        <option value="semanal">Esta semana</option>
                        <option value="mensual">Este mes</option>
                        <option value="anual">Este año</option>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="openExport = false" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 rounded-lg hover:bg-slate-200">Cancelar</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white rounded-lg bg-[#39A900] hover:bg-[#2b8000] flex items-center gap-1.5 focus:ring-2 focus:ring-offset-2 focus:ring-[#39A900]">
                        <svg class="w-4 h-4" transform="rotate(180)" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Generar PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
    </div>
</div>


@if($semilleros->isEmpty())
{{-- Estado vacío --}}
<div class="bg-white rounded-xl border border-slate-200 p-14 text-center">
    <div class="w-16 h-16 rounded-2xl flex items-center justify-center bg-slate-50 mx-auto mb-4">
        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
        </svg>
    </div>

    @if($semilleros->isEmpty())
        {{-- Estado vacío --}}
        <div class="bg-white rounded-2xl border border-dashed border-slate-200 px-10 py-14 text-center">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center bg-slate-50 mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                </svg>
            </div>
            <h3 class="text-slate-900 font-semibold mb-1">Sin semilleros asignados</h3>
            <p class="text-slate-400 text-sm max-w-md mx-auto">
                Aún no estás vinculado a ningún semillero. Contacta al Líder o Director de semilleros para que te asignen.
            </p>
        </div>
    @else

        {{-- Resumen rápido --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs text-slate-400 mb-1 uppercase tracking-wide font-semibold">Semilleros</p>
                <p class="text-3xl font-bold" style="color:#39A900">{{ $semilleros->count() }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5">
                <p class="text-xs text-slate-400 mb-1 uppercase tracking-wide font-semibold">Proyectos</p>
                <p class="text-3xl font-bold text-slate-800">{{ $semilleros->sum(fn($s) => $s->projects->count()) }}</p>
            </div>
            <div class="bg-white rounded-xl border border-slate-200 p-5 col-span-2 sm:col-span-1">
                <p class="text-xs text-slate-400 mb-1 uppercase tracking-wide font-semibold">Mi cargo</p>
                <p class="text-sm font-semibold text-slate-700">{{ $advisor?->institucion ?? 'Asesor' }}</p>
                @if($advisor?->email)
                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ $advisor->email }}</p>
                @endif
            </div>
        </div>

        {{-- Lista de semilleros --}}
        <div class="space-y-6">
            @foreach($semilleros as $sem)
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden">

                {{-- Header del semillero --}}
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-4">
            {{-- Logo / inicial --}}
            @if($sem->logo)
                <img src="{{ Storage::url($sem->logo) }}" alt="{{ $sem->nombre }}"
                     class="w-12 h-12 rounded-xl object-cover flex-shrink-0 border border-slate-200">
            @else
                <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white text-lg font-bold flex-shrink-0"
                     style="background:#0a1628">
                    {{ strtoupper(substr($sem->nombre, 0, 1)) }}
                </div>
            @endif

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <h2 class="font-semibold text-slate-900 text-base">{{ $sem->nombre }}</h2>
                    @if($sem->codigo)
                    <span class="text-xs px-2 py-0.5 rounded-full bg-slate-100 text-slate-500 font-mono">{{ $sem->codigo }}</span>
                    @endif
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium
                        @if($sem->estado?->value === 'activo') bg-green-100 text-green-700 @else bg-slate-100 text-slate-500 @endif">
                        <div class="w-1.5 h-1.5 rounded-full
                            @if($sem->estado?->value === 'activo') bg-green-500 @else bg-slate-400 @endif"></div>
                        {{ ucfirst($sem->estado?->value ?? '—') }}
                    </span>
                </div>
                <div class="flex items-center gap-4 mt-1 flex-wrap">
                    @if($sem->researchGroup)
                    <span class="text-xs text-slate-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253"/></svg>
                        Grupo: {{ $sem->researchGroup->nombre }}
                    </span>
                    @endif
                    @if($sem->leader)
                    <span class="text-xs text-slate-500 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Líder: {{ $sem->leader->person?->primer_nombre }} {{ $sem->leader->person?->primer_apellido }}
                    </span>
                    @endif
                    <span class="text-xs text-slate-500">
                        {{ $sem->projects->count() }} proyecto(s)
                    </span>
                </div>
            </div>
                </div>

</x-app-layout>
