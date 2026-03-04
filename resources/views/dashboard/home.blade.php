<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <!-- Saludo -->
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">
            Bienvenido, {{ Auth::user()->name ?? 'Usuario' }}
        </h2>
        <p class="text-sm text-slate-500 mt-0.5">
            Resumen general del sistema de gestión documental
        </p>
    </div>

    <!-- Tarjetas de estadísticas (placeholders) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['label' => 'Grupos de Investigación', 'value' => '—', 'color' => 'text-blue-600'],
            ['label' => 'Semilleros Activos',      'value' => '—', 'color' => 'text-emerald-600'],
            ['label' => 'Proyectos',                'value' => '—', 'color' => 'text-purple-600'],
            ['label' => 'Productos Registrados',    'value' => '—', 'color' => 'text-amber-600'],
        ] as $stat)
        <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-sm transition-shadow">
            <p class="text-xs font-medium text-slate-500 mb-3">
                {{ $stat['label'] }}
            </p>
            <p class="text-2xl font-bold {{ $stat['color'] }}">
                {{ $stat['value'] }}
            </p>
        </div>
        @endforeach
    </div>

    <!-- Panel principal vacío (placeholder) -->
    <div class="bg-white rounded-xl border border-slate-200 p-8 text-center">
        <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center
                    justify-center mx-auto mb-4">
            <svg class="w-6 h-6 text-slate-400" fill="none"
                 stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6z"/>
            </svg>
        </div>
        <h3 class="text-sm font-semibold text-slate-700 mb-1">
            Panel en construcción
        </h3>
        <p class="text-xs text-slate-400">
            Los módulos del sistema se irán agregando progresivamente.
        </p>
    </div>
</x-app-layout>
