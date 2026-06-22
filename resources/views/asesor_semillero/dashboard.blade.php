@php
use Illuminate\Support\Facades\DB;
use App\Models\Seedling;
use App\Models\Project;
use App\Models\Product;
use App\Models\ProjectAuthor;
@endphp

<x-app-layout>
<x-slot name="header">Dashboard</x-slot>

@php
// Todos los semilleros del asesor (puede tener varios)
$semilleros = Seedling::whereHas('advisors', function ($q) {
    $q->where('external_advisors.user_id', auth()->id())
      ->where('seedling_advisors.activo', true);
})->get();

$totalSemilleros = $semilleros->count();

// Proyectos de todos los semilleros
$allProjectIds = DB::table('project_seedlings')
    ->whereIn('seedling_id', $semilleros->pluck('id'))
    ->pluck('project_id');

$totalProyectos = $allProjectIds->count();

// Aprendices (autores activos en esos proyectos, excluyendo al asesor)
$totalAprendices = ProjectAuthor::whereIn('project_id', $allProjectIds)
    ->where('activo', true)
    ->where('user_id', '!=', auth()->id())
    ->distinct('user_id')
    ->count('user_id');

// Productos
$productos = Product::whereIn('project_id', $allProjectIds)->get();
$totalProductos     = $productos->count();
$pendienteCount     = $productos->where('estado_revision', 'pendiente')->count();
$aprobadoCount      = $productos->where('estado_revision', 'aprobado')->count();
$rechazadoCount     = $productos->where('estado_revision', 'rechazado')->count();

// Productos rechazados recientes
$productosRechazados = Product::whereIn('project_id', $allProjectIds)
    ->where('estado_revision', 'rechazado')
    ->with('project')
    ->latest()->limit(5)->get();

// Proyectos sin aprendices autores
$proyectosSinIntegrantes = Project::whereIn('id', $allProjectIds)
    ->whereDoesntHave('projectAuthors', function ($q) {
        $q->where('activo', true)->where('user_id', '!=', auth()->id());
    })->get(['id','nombre']);

// Semillero principal (el primero)
$semillero = $semilleros->first();
@endphp


{{-- ─── Bienvenida ────────────────────────────────────── --}}
<div class="mb-6 bg-white rounded-2xl border border-slate-200 overflow-hidden">
    <div class="flex items-center gap-5 p-5">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg flex-shrink-0 shadow-sm" style="background:#0a1628">
            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}
        </div>
        <div class="flex-1">
            <p class="text-xs text-slate-400 mb-0.5">Bienvenido de vuelta</p>
            <h2 class="font-outfit font-bold text-slate-900 text-lg leading-tight">
                {{ auth()->user()->person?->primer_nombre ?? auth()->user()->name ?? 'Asesor' }}
                {{ auth()->user()->person?->primer_apellido ?? '' }}
            </h2>
            <p class="text-xs text-slate-500 mt-0.5">
                @if($totalSemilleros > 0)
                    Asesor en <strong>{{ $totalSemilleros }}</strong> {{ $totalSemilleros === 1 ? 'semillero' : 'semilleros' }}
                    @if($semillero) · <span style="color:#39A900">{{ $semillero->nombre }}</span>@if($totalSemilleros > 1) y {{ $totalSemilleros - 1 }} más @endif
                    @endif
                @else
                    Sin semillero asignado aún
                @endif
            </p>
        </div>
        <div class="hidden sm:block">
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-50 text-[#39A900] border border-green-200">
                <div class="w-1.5 h-1.5 rounded-full bg-[#39A900]"></div>
                Activo
            </span>
        </div>
    </div>
    @if($semilleros->isNotEmpty())
    <div class="border-t border-slate-100 px-5 py-2.5 flex items-center gap-4 overflow-x-auto bg-slate-50/50">
        @foreach($semilleros as $sem)
        <a href="{{ route('asesor.mis_semilleros.index') }}" class="flex items-center gap-2 text-xs text-slate-600 hover:text-[#39A900] transition-colors whitespace-nowrap">
            <div class="w-5 h-5 rounded flex items-center justify-center text-white text-xs font-bold flex-shrink-0" style="background:#39A900">
                {{ strtoupper(substr($sem->nombre, 0, 1)) }}
            </div>
            {{ $sem->nombre }}
        </a>
        @endforeach
    </div>
    @endif
</div>

@if($totalSemilleros === 0)
<div class="mb-6 bg-amber-50 border border-amber-200 rounded-xl p-5 flex items-center gap-3">
    <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z"/></svg>
    <p class="text-amber-800 text-sm font-medium">No tienes un semillero asignado. Contacta al administrador o director de semilleros.</p>
</div>
@endif

{{-- ─── Tarjetas de métricas ──────────────────────────── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    {{-- Semilleros --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Semilleros</p>
            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background:#f0fdf4">
                <svg class="w-5 h-5" style="color:#39A900" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1 1 .03 2.798-1.318 2.552l-13.98-2.796c-1.348-.245-1.745-1.9-.79-2.855L5 14.5"/></svg>
            </div>
        </div>
        <p class="text-3xl font-outfit font-bold text-slate-900">{{ $totalSemilleros }}</p>
        <p class="text-xs text-slate-400 mt-1">Asignados a ti</p>
    </div>

    {{-- Aprendices --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Aprendices</p>
            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-blue-50">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-outfit font-bold text-slate-900">{{ $totalAprendices }}</p>
        <p class="text-xs text-slate-400 mt-1">En tus proyectos</p>
    </div>

    {{-- Proyectos --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Proyectos</p>
            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-purple-50">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z"/></svg>
            </div>
        </div>
        <p class="text-3xl font-outfit font-bold text-slate-900">{{ $totalProyectos }}</p>
        <p class="text-xs text-slate-400 mt-1">En tus semilleros</p>
    </div>

    {{-- Productos --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Productos</p>
            <div class="w-9 h-9 rounded-lg flex items-center justify-center bg-amber-50">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776"/></svg>
            </div>
        </div>
        <p class="text-3xl font-outfit font-bold text-slate-900">{{ $totalProductos }}</p>
        <div class="flex gap-1.5 mt-2 flex-wrap">
            <span class="text-xs px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-700">{{ $pendienteCount }} pend.</span>
            <span class="text-xs px-1.5 py-0.5 rounded-full bg-green-100 text-green-700">{{ $aprobadoCount }} apro.</span>
            @if($rechazadoCount > 0)<span class="text-xs px-1.5 py-0.5 rounded-full bg-red-100 text-red-700">{{ $rechazadoCount }} rech.</span>@endif
        </div>
    </div>

</div>

{{-- ─── Gráficas + Alertas ─────────────────────────────── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- Donut Chart: Estado de revisión de productos --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col">
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-slate-900">Estado de productos</h3>
            <p class="text-xs text-slate-400 mt-0.5">Revisión del líder de semillero</p>
        </div>
        @if($totalProductos > 0)
        <div class="flex-1 flex flex-col items-center justify-center gap-4">
            <div class="relative w-36 h-36">
                <canvas id="chartProductos" width="144" height="144"></canvas>
                <div class="absolute inset-0 flex items-center justify-center flex-col">
                    <span class="text-2xl font-outfit font-bold text-slate-900">{{ $totalProductos }}</span>
                    <span class="text-xs text-slate-400">total</span>
                </div>
            </div>
            <div class="flex flex-col gap-1.5 w-full">
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-amber-400"></div> Pendiente</span>
                    <span class="font-semibold text-slate-700">{{ $pendienteCount }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-green-500"></div> Aprobado</span>
                    <span class="font-semibold text-slate-700">{{ $aprobadoCount }}</span>
                </div>
                <div class="flex items-center justify-between text-xs">
                    <span class="flex items-center gap-1.5"><div class="w-2.5 h-2.5 rounded-full bg-red-500"></div> Rechazado</span>
                    <span class="font-semibold text-slate-700">{{ $rechazadoCount }}</span>
                </div>
            </div>
        </div>
        @else
        <div class="flex-1 flex items-center justify-center">
            <div class="text-center py-8">
                <svg class="w-10 h-10 text-slate-200 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776"/></svg>
                <p class="text-sm text-slate-400">Sin productos registrados</p>
                @can('productos.registrar')
                <a href="{{ route('asesor.productos.create') }}" class="mt-2 inline-block text-xs text-[#39A900] hover:underline">+ Registrar producto</a>
                @endcan
            </div>
        </div>
        @endif
    </div>

    {{-- Barras horizontales: Proyectos por semillero --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col">
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-slate-900">Proyectos por semillero</h3>
            <p class="text-xs text-slate-400 mt-0.5">Distribución en tus semilleros</p>
        </div>
        @if($semilleros->isNotEmpty())
        <div class="flex-1 space-y-3">
            @foreach($semilleros as $sem)
            @php
                $cnt = DB::table('project_seedlings')->where('seedling_id', $sem->id)->count();
                $pct = $totalProyectos > 0 ? round(($cnt / $totalProyectos) * 100) : 0;
            @endphp
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-slate-700 truncate max-w-[150px]" title="{{ $sem->nombre }}">{{ Str::limit($sem->nombre, 22) }}</span>
                    <span class="text-xs font-semibold text-slate-500">{{ $cnt }}</span>
                </div>
                <div class="h-2 bg-slate-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-700" style="width: {{ $pct }}%; background: #39A900;"></div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="flex-1 flex items-center justify-center">
            <p class="text-sm text-slate-400">Sin semilleros asignados</p>
        </div>
        @endif
    </div>

    {{-- Alertas --}}
    <div class="bg-white rounded-xl border border-slate-200 p-5 flex flex-col gap-4">
        <div class="mb-1">
            <h3 class="text-sm font-semibold text-slate-900">Alertas y avisos</h3>
        </div>

        @if($rechazadoCount > 0)
        <div class="bg-red-50 border border-red-200 rounded-lg p-3">
            <p class="text-xs font-semibold text-red-800 flex items-center gap-1.5 mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126z"/></svg>
                {{ $rechazadoCount }} producto(s) rechazado(s)
            </p>
            <ul class="space-y-1">
                @foreach($productosRechazados as $pr)
                <li class="text-xs text-red-700 flex items-center justify-between gap-1">
                    <span class="truncate">{{ Str::limit($pr->nombre, 28) }}</span>
                    @can('productos.editar')
                    <a href="{{ route('asesor.productos.edit', $pr->id) }}" class="shrink-0 text-red-600 underline hover:text-red-800">Editar</a>
                    @endcan
                </li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($proyectosSinIntegrantes->isNotEmpty())
        <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
            <p class="text-xs font-semibold text-amber-800 flex items-center gap-1.5 mb-2">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                {{ $proyectosSinIntegrantes->count() }} proyecto(s) sin aprendices
            </p>
            <ul class="space-y-1">
                @foreach($proyectosSinIntegrantes->take(4) as $proy)
                <li class="text-xs text-amber-700 truncate">• {{ $proy->nombre }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if($rechazadoCount === 0 && $proyectosSinIntegrantes->isEmpty())
        <div class="flex-1 flex items-center justify-center flex-col gap-2 py-6">
            <div class="w-10 h-10 rounded-full bg-green-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <p class="text-sm font-medium text-slate-700">Todo en orden</p>
            <p class="text-xs text-slate-400 text-center">Sin alertas pendientes en este momento</p>
        </div>
        @endif
    </div>

</div>

{{-- ─── Acciones rápidas ──────────────────────────────── --}}
<div class="bg-white rounded-xl border border-slate-200 p-5">
    <h3 class="text-sm font-semibold text-slate-900 mb-4">Acciones rápidas</h3>
    <div class="flex flex-wrap gap-3" x-data="{
        openExportModal: false,
        reporte: 'dashboard',
        rango: '',
        anio: {{ date('Y') }},
        mes: {{ date('n') }},
        semana: {{ date('W') }},
        fecha_desde: '',
        fecha_hasta: '',
        maxAnio: {{ date('Y') }},
        maxMes: {{ date('n') }},
        maxSemana: {{ date('W') }},
        hoy: '{{ date('Y-m-d') }}',
        semanas: Array.from({length: {{ date('W') }}}, (_, i) => i + 1),
        meses: [
            {v:1,l:'Enero'},{v:2,l:'Febrero'},{v:3,l:'Marzo'},{v:4,l:'Abril'},
            {v:5,l:'Mayo'},{v:6,l:'Junio'},{v:7,l:'Julio'},{v:8,l:'Agosto'},
            {v:9,l:'Septiembre'},{v:10,l:'Octubre'},{v:11,l:'Noviembre'},{v:12,l:'Diciembre'}
        ],
        mesesDisponibles() {
            if (this.anio < this.maxAnio) return this.meses;
            return this.meses.filter(m => m.v <= this.maxMes);
        },
        semanasDisponibles() {
            if (this.anio < this.maxAnio) return Array.from({length: 52}, (_, i) => i + 1);
            return this.semanas;
        },
        anioChanged() {
            if (this.anio == this.maxAnio) {
                if (this.mes > this.maxMes) this.mes = this.maxMes;
                if (this.semana > this.maxSemana) this.semana = this.maxSemana;
            }
        },
        buildUrl() {
            const routes = {
                'dashboard':  '{{ route('asesor.exportar.dashboard') }}',
                'semilleros': '{{ route('asesor.exportar.semilleros') }}',
                'proyectos':  '{{ route('asesor.exportar.proyectos') }}',
                'productos':  '{{ route('asesor.exportar.productos') }}',
                'aprendices': '{{ route('asesor.exportar.aprendices') }}',
            };
            let url = new URL(routes[this.reporte], window.location.origin);
            if (this.rango) {
                url.searchParams.set('rango', this.rango);
                if (this.rango === 'anual') url.searchParams.set('anio', this.anio);
                if (this.rango === 'mensual') { url.searchParams.set('anio', this.anio); url.searchParams.set('mes', this.mes); }
                if (this.rango === 'semanal') { url.searchParams.set('anio', this.anio); url.searchParams.set('semana', this.semana); }
                if (this.rango === 'personalizado') { if (this.fecha_desde) url.searchParams.set('fecha_desde', this.fecha_desde); if (this.fecha_hasta) url.searchParams.set('fecha_hasta', this.fecha_hasta); }
            }
            return url.toString();
        }
    }">
        <button @click="openExportModal = true" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-slate-700 bg-white border border-slate-200 hover:bg-slate-50 transition-all hover:shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
            Descargar Reportes
        </button>

        {{-- MODAL --}}
        <div x-show="openExportModal" style="display: none;"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm"
             x-cloak
             @keydown.escape.window="openExportModal = false">
            <div @click.away="openExportModal = false"
                 class="bg-white rounded-2xl w-full max-w-lg shadow-2xl border border-slate-100 overflow-hidden"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">

                {{-- Header del modal --}}
                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100" style="background:#0a1628">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#39A900] flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-white">Descargar Reportes</h3>
                            <p class="text-xs" style="color:#64748b">Selecciona el módulo y el período</p>
                        </div>
                    </div>
                    <button @click="openExportModal = false" class="text-slate-400 hover:text-red-400 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="px-6 py-5 space-y-5">
                    {{-- 1. Tipo de reporte --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">📄 Módulo del reporte</label>
                        <div class="grid grid-cols-3 gap-2">
                            @foreach([
                                ['val'=>'dashboard',  'label'=>'General',    'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                                ['val'=>'semilleros', 'label'=>'Semilleros',  'icon'=>'M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1 1 .03 2.798-1.318 2.552l-13.98-2.796c-1.348-.245-1.745-1.9-.79-2.855L5 14.5'],
                                ['val'=>'proyectos',  'label'=>'Proyectos',   'icon'=>'M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z'],
                                ['val'=>'aprendices', 'label'=>'Aprendices',  'icon'=>'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
                                ['val'=>'productos',  'label'=>'Productos',   'icon'=>'M3.75 9.776c.112-.017.227-.026.344-.026h15.812c.117 0 .232.009.344.026m-16.5 0a2.25 2.25 0 00-1.883 2.542l.857 6a2.25 2.25 0 002.227 1.932H19.05a2.25 2.25 0 002.227-1.932l.857-6a2.25 2.25 0 00-1.883-2.542m-16.5 0V6A2.25 2.25 0 016 3.75h3.879a1.5 1.5 0 011.06.44l2.122 2.12a1.5 1.5 0 001.06.44H18A2.25 2.25 0 0120.25 9v.776'],
                            ] as $btn)
                            <button type="button"
                                @click="reporte = '{{ $btn['val'] }}'"
                                :class="reporte === '{{ $btn['val'] }}' ? 'bg-[#39A900] text-white border-[#39A900] shadow-md' : 'bg-white text-slate-600 border-slate-200 hover:bg-slate-50'"
                                class="flex flex-col items-center gap-1.5 py-2.5 px-2 rounded-xl border text-xs font-medium transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $btn['icon'] }}"/>
                                </svg>
                                {{ $btn['label'] }}
                            </button>
                            @endforeach
                        </div>
                    </div>

                    {{-- 2. Tipo de período --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">🗓 Período del reporte</label>
                        <select x-model="rango"
                                class="w-full border border-slate-200 rounded-lg px-3.5 py-2.5 text-sm text-slate-800 bg-white focus:border-[#39A900] focus:ring-2 focus:ring-[#39A900]/10 outline-none transition-all">
                            <option value="">Todo el histórico</option>
                            <option value="hoy">Hoy</option>
                            <option value="semanal">Semanal – elegir semana</option>
                            <option value="mensual">Mensual – elegir mes y año</option>
                            <option value="anual">Anual – elegir año</option>
                            <option value="personalizado">Personalizado – rango de fechas</option>
                        </select>
                    </div>

                    {{-- Inputs dinámicos según el rango --}}
                    <div x-show="['semanal', 'mensual', 'anual'].includes(rango)" x-cloak class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                        {{-- Año (aplica para todos los anteriores) --}}
                        <div x-show="['semanal', 'mensual', 'anual'].includes(rango)">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Año</label>
                            <input type="number" x-model="anio" @change="anioChanged()" min="2020" :max="maxAnio"
                                   class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:border-[#39A900] focus:ring-1 focus:ring-[#39A900] outline-none">
                        </div>

                        {{-- Mes --}}
                        <div x-show="rango === 'mensual'">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Mes</label>
                            <select x-model="mes" class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:border-[#39A900] focus:ring-1 focus:ring-[#39A900] outline-none">
                                <template x-for="m in mesesDisponibles()" :key="m.v">
                                    <option :value="m.v" x-text="m.l"></option>
                                </template>
                            </select>
                        </div>

                        {{-- Semana --}}
                        <div x-show="rango === 'semanal'">
                            <label class="block text-xs font-medium text-slate-500 mb-1">Semana</label>
                            <select x-model="semana" class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:border-[#39A900] focus:ring-1 focus:ring-[#39A900] outline-none">
                                <template x-for="s in semanasDisponibles()" :key="s">
                                    <option :value="s" x-text="'Semana ' + s"></option>
                                </template>
                            </select>
                        </div>
                    </div>

                    {{-- Rango personalizado --}}
                    <div x-show="rango === 'personalizado'" x-cloak class="grid grid-cols-2 gap-3 p-3 bg-slate-50 rounded-xl border border-slate-100">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Desde</label>
                            <input type="date" x-model="fecha_desde" :max="fecha_hasta || hoy"
                                   class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:border-[#39A900] focus:ring-1 focus:ring-[#39A900] outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Hasta</label>
                            <input type="date" x-model="fecha_hasta" :min="fecha_desde" :max="hoy"
                                   class="w-full border border-slate-200 rounded-md px-3 py-2 text-sm focus:border-[#39A900] focus:ring-1 focus:ring-[#39A900] outline-none">
                        </div>
                    </div>
                </div>

                {{-- Footer del modal --}}
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end gap-2">
                    <button type="button" @click="openExportModal = false"
                            class="px-4 py-2 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors">
                        Cancelar
                    </button>
                    <a :href="buildUrl()" target="_blank" @click="openExportModal = false"
                       class="px-5 py-2 text-sm font-semibold text-white rounded-lg bg-[#39A900] hover:bg-[#2b8000] flex items-center gap-1.5 transition-colors shadow-sm focus:ring-2 focus:ring-offset-2 focus:ring-[#39A900]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                        Generar PDF
                    </a>
                </div>
            </div>
        </div>

        @can('aprendices.registrar')
        <a href="{{ route('asesor.aprendices.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-all hover:opacity-90 hover:shadow-sm"
           style="background:#39A900">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Registrar Aprendiz
        </a>
        @endcan
        @can('proyectos.crear_semillero')
        <a href="{{ route('asesor.proyectos.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-all bg-blue-600 hover:bg-blue-700 hover:shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Nuevo Proyecto
        </a>
        @endcan
        @can('productos.registrar')
        <a href="{{ route('asesor.productos.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold text-white transition-all bg-purple-600 hover:bg-purple-700 hover:shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Registrar Producto
        </a>
        @endcan
        <a href="{{ route('asesor.mis_semilleros.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg text-sm font-semibold border border-slate-200 text-slate-600 bg-white hover:bg-slate-50 transition-all hover:shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            Ver mis semilleros
        </a>
    </div>
</div>

{{-- ─── Chart.js Donut ────────────────────────────────── --}}
@if($totalProductos > 0)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('chartProductos');
    if (!ctx) return;
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Pendiente', 'Aprobado', 'Rechazado'],
            datasets: [{
                data: [{{ $pendienteCount }}, {{ $aprobadoCount }}, {{ $rechazadoCount }}],
                backgroundColor: ['#fbbf24', '#22c55e', '#ef4444'],
                borderWidth: 0,
                hoverOffset: 6,
            }]
        },
        options: {
            cutout: '72%',
            plugins: { legend: { display: false }, tooltip: {
                callbacks: {
                    label: ctx => ` ${ctx.label}: ${ctx.raw}`
                }
            }},
            animation: { animateRotate: true, duration: 800 }
        }
    });
});
</script>
@endif

</x-app-layout>
