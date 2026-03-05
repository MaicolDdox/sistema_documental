<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SGD — @yield('title', 'Director de Semilleros')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Outfit', sans-serif; }
        @keyframes sgd-fadeInUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes sgd-fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .sgd-main { animation: sgd-fadeInUp 0.4s ease-out; }
        .sgd-card { animation: sgd-fadeIn 0.35s ease-out; }
        .dir-nav-active { background: #f0fdf4; color: #39A900; border-left: 3px solid #39A900; }
        .dir-nav-active svg { color: #39A900 !important; }
        .dir-nav-item { transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease; }
        .dir-nav-item:hover:not(.dir-nav-active) { background: #f0fdf4; color: #2d8500; transform: translateX(2px); }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        ::-webkit-scrollbar-thumb { background: #39A900; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #2d8500; }
        .sgd-table-card { border-radius: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: box-shadow 0.25s ease, transform 0.2s ease; overflow: hidden; }
        .sgd-table-card:hover { box-shadow: 0 4px 12px rgba(57,169,0,0.08); }
        .sgd-table { width: 100%; border-collapse: collapse; }
        .sgd-table thead { background: linear-gradient(180deg, #f0fdf4 0%, #ecfdf5 100%); }
        .sgd-table thead th { padding: 0.875rem 1rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #166534; border-bottom: 2px solid rgba(57,169,0,0.2); }
        .sgd-table tbody tr { transition: background 0.2s ease, transform 0.15s ease; border-bottom: 1px solid #f1f5f9; }
        .sgd-table tbody tr:hover { background: rgba(57,169,0,0.06); }
        .sgd-table tbody td { padding: 0.875rem 1rem; font-size: 0.875rem; }
        .sgd-btn-primary { background: #39A900; color: white; font-weight: 600; transition: background 0.2s ease, transform 0.15s ease, box-shadow 0.2s ease; }
        .sgd-btn-primary:hover { background: #2d8500; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(57,169,0,0.35); }
        .sgd-btn-primary:active { transform: translateY(0) scale(0.98); }
        .sgd-btn-secondary { transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease, transform 0.15s ease; }
        .sgd-btn-secondary:hover { border-color: #39A900; color: #39A900; background: #f0fdf4; transform: translateY(-1px); }
        .sgd-btn-secondary:active { transform: translateY(0) scale(0.98); }
        .sgd-input-focus { transition: border-color 0.2s ease, box-shadow 0.2s ease; }
        .sgd-input-focus:focus { border-color: #39A900; box-shadow: 0 0 0 3px rgba(57,169,0,0.15); }
        .sgd-tab { transition: color 0.2s ease, border-color 0.2s ease, transform 0.15s ease; }
        .sgd-tab:hover { color: #2d8500; transform: translateY(-1px); }
        .sgd-card { transition: box-shadow 0.25s ease, transform 0.2s ease; }
        .sgd-card:hover { box-shadow: 0 8px 24px rgba(57,169,0,0.06); transform: translateY(-2px); }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen flex" x-data="{ sidebarOpen: false, openSemilleros: {{ request()->routeIs('dir-sem.semilleros.*', 'dir-sem.lideres.*') ? 'true' : 'false' }}, openUser: false }">

    <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
         x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm lg:hidden"></div>

    <aside class="w-64 min-h-screen bg-white border-r border-slate-200 flex flex-col fixed left-0 top-0 z-50 transition-transform duration-300 ease-out lg:translate-x-0 shadow-xl lg:shadow-none" :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        <div class="h-16 flex items-center px-5 border-b border-slate-100 shrink-0">
            <a href="{{ route('dir-sem.dashboard') }}" class="flex items-center gap-3">
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
            <a href="{{ route('dir-sem.dashboard') }}" class="dir-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-colors {{ request()->routeIs('dir-sem.dashboard') ? 'dir-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                Dashboard
            </a>

            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">GESTIÓN SEMILLEROS</p>
            <div class="mb-1">
                <button type="button" @click="openSemilleros = !openSemilleros" class="dir-nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-colors text-left">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                        <span>Mis Semilleros</span>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform" :class="openSemilleros ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div x-show="openSemilleros" x-cloak class="pl-11 pr-3 py-2 space-y-1" x-transition>
                    <a href="{{ route('dir-sem.semilleros.index') }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('dir-sem.semilleros.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                        Semilleros
                    </a>
                    <a href="{{ route('dir-sem.lideres.index') }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('dir-sem.lideres.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                        <svg class="w-4 h-4 shrink-0 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                        Líderes de Semillero
                    </a>
                </div>
            </div>

            <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">USUARIOS</p>
            <a href="{{ route('dir-sem.asignar-roles.index') }}" class="dir-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-colors {{ request()->routeIs('dir-sem.asignar-roles.*') ? 'dir-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0121.75 12.75a3 3 0 01-3 3z"/></svg>
                Asignar Roles
            </a>

            <a href="{{ route('dir-sem.documentos.index') }}" class="dir-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-colors {{ request()->routeIs('dir-sem.documentos.*') ? 'dir-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                Documentos
            </a>
            <a href="{{ route('dir-sem.reportes.index') }}" class="dir-nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-colors {{ request()->routeIs('dir-sem.reportes.*') ? 'dir-nav-active' : '' }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                Reportes
            </a>
        </nav>

        <!-- Footer del sidebar: usuario (igual que en Administrador) -->
        <div class="border-t border-slate-100 p-3 shrink-0" x-data="{ open: false }">
            <button type="button" @click="open = !open"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg hover:bg-slate-50 transition-all text-left">
                <div class="w-8 h-8 rounded-full bg-[#0a1628] flex items-center justify-center flex-shrink-0">
                    <span class="text-white text-xs font-bold">{{ strtoupper(substr(Auth::user()->email ?? 'U', 0, 2)) }}</span>
                </div>
                <div class="flex-1 overflow-hidden min-w-0">
                    <p class="text-sm font-medium text-slate-800 truncate">Usuario</p>
                    <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email ?? '' }}</p>
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-transform flex-shrink-0" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mb-2 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden">
                @if(Route::has('settings.profile'))
                <a href="{{ route('settings.profile') }}" class="flex items-center gap-3 px-4 py-3 text-sm text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Configuración
                </a>
                <div class="border-t border-slate-100"></div>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-red-500 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                        </svg>
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
                @if(trim((string) view()->yieldContent('header', 'Panel')) !== '')
                <h1 class="text-base font-semibold text-slate-900 truncate">@yield('header', 'Panel')</h1>
                @endif
            </div>
            <div class="flex items-center gap-3 shrink-0 ml-4">
                <span class="w-2 h-2 rounded-full bg-[#39A900]"></span>
                <span class="text-sm font-medium text-slate-700">Director de Semilleros</span>
                <div class="w-8 h-8 rounded-full bg-[#39A900]/20 flex items-center justify-center">
                    <span class="text-xs font-bold text-[#39A900]">{{ strtoupper(substr(Auth::user()->email ?? 'U', 0, 2)) }}</span>
                </div>
            </div>
        </header>

        @if(session('success'))
        <div class="mx-6 mt-4 bg-green-50 border-l-4 border-[#39A900] p-4 rounded-r-lg"><p class="text-sm text-green-700">{{ session('success') }}</p></div>
        @endif
        @if(session('error'))
        <div class="mx-6 mt-4 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg"><p class="text-sm text-red-700">{{ session('error') }}</p></div>
        @endif
        @if(session('warning'))
        <div class="mx-6 mt-4 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg"><p class="text-sm text-amber-700">{{ session('warning') }}</p></div>
        @endif

        <main class="sgd-main flex-1 p-6">
            @yield('content')
        </main>
    </div>
    @stack('scripts')
</body>
</html>
