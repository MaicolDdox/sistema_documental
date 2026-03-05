<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SGD — @yield('title', 'Líder de Semillero')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Outfit', sans-serif; }
        @keyframes sgd-fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes sgd-fadeIn { from { opacity: 0; } to { opacity: 1; } }
        .sgd-main { animation: sgd-fadeInUp 0.4s ease-out; }
        .sgd-card { animation: sgd-fadeIn 0.35s ease-out; }
        .lid-nav-active { background: #f0fdf4; color: #39A900; border-left: 3px solid #39A900; }
        .lid-nav-active svg { color: #39A900 !important; }
        .lid-nav-item { transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease; }
        .lid-nav-item:hover:not(.lid-nav-active) { background: #f0fdf4; color: #2d8500; transform: translateX(2px); }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        ::-webkit-scrollbar-thumb { background: #39A900; border-radius: 3px; }
        .sgd-table-card { border-radius: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: box-shadow 0.25s ease; overflow: hidden; }
        .sgd-table-card:hover { box-shadow: 0 4px 12px rgba(57,169,0,0.08); }
        .sgd-table { width: 100%; border-collapse: collapse; }
        .sgd-table thead { background: linear-gradient(180deg, #f0fdf4 0%, #ecfdf5 100%); }
        .sgd-table thead th { padding: 0.875rem 1rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #166534; border-bottom: 2px solid rgba(57,169,0,0.2); }
        .sgd-table tbody tr { transition: background 0.2s ease; border-bottom: 1px solid #f1f5f9; }
        .sgd-table tbody tr:hover { background: rgba(57,169,0,0.06); }
        .sgd-table tbody td { padding: 0.875rem 1rem; font-size: 0.875rem; }
        .sgd-btn-primary { background: #39A900; color: white; font-weight: 600; transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease; }
        .sgd-btn-primary:hover { background: #2d8500; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(57,169,0,0.35); }
        .sgd-btn-primary:active { transform: translateY(0) scale(0.98); }
        .sgd-btn-secondary { transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.15s ease; }
        .sgd-btn-secondary:hover { border-color: #39A900; color: #39A900; background: #f0fdf4; transform: translateY(-1px); }
        .sgd-card { transition: box-shadow 0.25s ease, transform 0.2s ease; }
        .sgd-card:hover { box-shadow: 0 8px 24px rgba(57,169,0,0.06); transform: translateY(-2px); }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen flex" x-data="{ sidebarOpen: false, openMiSemillero: true, openGestion: true, openDoc: false, openUser: false }">

    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm lg:hidden"></div>

    <aside class="w-64 min-h-screen bg-white border-r border-slate-200 flex flex-col fixed left-0 top-0 z-50 transition-transform duration-300 ease-out lg:translate-x-0 shadow-xl lg:shadow-none" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="h-16 flex items-center px-5 border-b border-slate-100 shrink-0">
            <a href="{{ route('lider-sem.dashboard') }}" class="flex items-center gap-3">
                <span class="w-9 h-9 rounded flex items-center justify-center bg-[#0a1628] font-heading text-sm font-bold text-white shrink-0">SGD</span>
                <div class="flex flex-col leading-tight">
                    <span class="text-xs font-medium text-slate-700">Sistema de Gestión</span>
                    <span class="text-xs font-semibold text-[#39A900]">Documental</span>
                </div>
            </a>
            <button type="button" @click="sidebarOpen = false" class="lg:hidden ml-auto p-2 text-slate-400 hover:text-slate-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg></button>
        </div>

        <nav class="flex-1 px-3 py-4 overflow-y-auto space-y-0.5">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-1">PRINCIPAL</p>
            <a href="{{ route('lider-sem.dashboard') }}" class="lid-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 {{ request()->routeIs('lider-sem.dashboard') ? 'lid-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                Dashboard
            </a>

            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">MI SEMILLERO</p>
            <a href="{{ route('lider-sem.info-semillero') }}" class="lid-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 {{ request()->routeIs('lider-sem.info-semillero') ? 'lid-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                Info del Semillero
            </a>
            <a href="{{ route('lider-sem.integrantes') }}" class="lid-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 {{ request()->routeIs('lider-sem.integrantes') ? 'lid-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M12 4.5v15m7.5-7.5h-15"/></svg>
                Integrantes
                @php
                    $lidSemillero = Auth::user()->ledSeedlings()->first();
                    $lidIntegrantesCount = $lidSemillero ? $lidSemillero->members()->count() : 0;
                @endphp
                @if($lidIntegrantesCount > 0)
                <span class="ml-auto bg-amber-100 text-amber-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $lidIntegrantesCount }}</span>
                @endif
            </a>
            <a href="{{ route('lider-sem.asesores') }}" class="lid-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 {{ request()->routeIs('lider-sem.asesores') ? 'lid-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                Asesores
            </a>

            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">GESTIÓN</p>
            <a href="{{ route('lider-sem.proyectos') }}" class="lid-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 {{ request()->routeIs('lider-sem.proyectos') ? 'lid-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                Proyectos
            </a>
            <a href="{{ route('lider-sem.productos') }}" class="lid-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 {{ request()->routeIs('lider-sem.productos') ? 'lid-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                Productos
                @php
                    $lidPendingCount = 0;
                    if ($lidSemillero ?? null) {
                        $pIds = \Illuminate\Support\Facades\DB::table('project_seedlings')->where('seedling_id', $lidSemillero->id)->pluck('project_id');
                        $prodIds = \Illuminate\Support\Facades\DB::table('products')->whereIn('project_id', $pIds)->pluck('id');
                        $lidPendingCount = \App\Models\GroupProduct::whereIn('product_id', $prodIds)->whereIn('estado_revision', ['pendiente', 'en_revision'])->count();
                    }
                @endphp
                @if($lidPendingCount > 0)
                <span class="ml-auto bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $lidPendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('lider-sem.aprendices') }}" class="lid-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 {{ request()->routeIs('lider-sem.aprendices') ? 'lid-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342 50.645 50.645 0 010 2.676A50.697 50.697 0 0112 13.489z"/></svg>
                Aprendices
            </a>

            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">DOCUMENTACIÓN</p>
            <a href="{{ route('lider-sem.archivos') }}" class="lid-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 {{ request()->routeIs('lider-sem.archivos') ? 'lid-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 20.552m5.108-11.479l-2.28-2.28"/></svg>
                Archivos Semillero
            </a>
            <a href="{{ route('lider-sem.doc-interna') }}" class="lid-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 {{ request()->routeIs('lider-sem.doc-interna*') ? 'lid-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                Doc. Interna
            </a>
        </nav>

        <div class="border-t border-slate-100 p-3 shrink-0" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition-all text-left">
                <div class="w-8 h-8 rounded-full bg-[#39A900] flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-xs font-bold">{{ Auth::user()->initials() }}</span>
                </div>
                <div class="flex-1 overflow-hidden min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate">{{ Auth::user()->person?->nombre_completo ?? 'Líder' }}</p>
                    <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-transform flex-shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
            <div x-show="open" x-cloak x-transition class="mb-2 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-500 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/></svg>
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-30 shrink-0">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <button type="button" @click="sidebarOpen = true" class="lg:hidden shrink-0 p-2 -ml-2 rounded-lg text-slate-500 hover:text-[#39A900] hover:bg-[#39A900]/10 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
                </button>
                @if(trim((string) view()->yieldContent('header', '')) !== '')
                <h1 class="text-base font-semibold text-slate-900 truncate">@yield('header', '')</h1>
                @endif
            </div>
            <div class="flex items-center gap-3 shrink-0 ml-4">
                <span class="w-2 h-2 rounded-full bg-[#39A900]"></span>
                <span class="text-sm font-medium text-slate-700">Líder de Semillero</span>
                <span class="text-sm text-slate-500 hidden sm:inline">{{ Auth::user()->email }}</span>
            </div>
        </header>

        @if(session('success'))
        <div class="mx-6 mt-4 bg-green-50 border-l-4 border-[#39A900] p-4 rounded-r-lg"><p class="text-sm text-green-700">{{ session('success') }}</p></div>
        @endif
        @if(session('error'))
        <div class="mx-6 mt-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg"><p class="text-sm text-red-700">{{ session('error') }}</p></div>
        @endif

        <main class="sgd-main flex-1 p-6">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
