<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SGD — {{ $header ?? 'Sistema de Gestión Documental' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @livewireStyles
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
        .nav-item-active { background: #f0fdf4; color: #39A900; border-left: 3px solid #39A900; }
        .nav-item-active svg { color: #39A900; }
        .nav-item { transition: background 0.2s ease, color 0.2s ease, transform 0.15s ease; }
        .nav-item:hover:not(.nav-item-active) { background: #f0fdf4; color: #2d8500; transform: translateX(2px); }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        ::-webkit-scrollbar-thumb { background: #39A900; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #2d8500; }
        .sgd-table-card { border-radius: 1rem; border: 1px solid #e2e8f0; box-shadow: 0 1px 3px rgba(0,0,0,0.06); transition: box-shadow 0.25s ease, transform 0.2s ease; overflow: hidden; }
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
        .sgd-card { transition: box-shadow 0.25s ease, transform 0.2s ease; }
        .sgd-card:hover { box-shadow: 0 8px 24px rgba(57,169,0,0.06); transform: translateY(-2px); }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen flex" x-data="{ sidebarOpen: false }" @keydown.escape.window="sidebarOpen = false">

    <!-- ═══ OVERLAY MÓVIL ═══ -->
    <div x-show="sidebarOpen" x-cloak
         @click="sidebarOpen = false"
         x-transition:enter="transition-opacity ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-in duration-150"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm lg:hidden"></div>

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="w-64 h-screen bg-white border-r border-slate-200
                  flex flex-col fixed left-0 top-0 z-50
                  transition-transform duration-300 ease-out shadow-xl lg:shadow-none
                  lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        @php
            $sidebarUser = Auth::user();
            $dashboardUrl = $sidebarUser && ($sidebarUser->hasAnyRole('administrador_sistema', 'admin')) ? route('admin.dashboard')
                : ($sidebarUser && $sidebarUser->hasRole('director_semilleros') ? route('dir-sem.dashboard')
                : ($sidebarUser && $sidebarUser->hasRole('lider_semillero') ? route('lider-sem.dashboard')
                : route('dashboard')));
            $roleLabel = $sidebarUser && ($sidebarUser->hasAnyRole('administrador_sistema', 'admin')) ? 'Administrador'
                : ($sidebarUser && $sidebarUser->hasRole('director_semilleros') ? 'Director de Semilleros'
                : ($sidebarUser && $sidebarUser->hasRole('lider_semillero') ? 'Líder de Semillero'
                : 'Usuario'));
            $isDashboardActive = request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') || request()->routeIs('dir-sem.dashboard') || request()->routeIs('lider-sem.dashboard');
        @endphp
        <!-- Logo -->
        <div class="h-16 flex items-center px-4 border-b border-slate-100">
            <a href="{{ $dashboardUrl }}" class="flex-1 flex items-center justify-center lg:flex-initial lg:justify-start" title="Sistema de Gestión Documental">
                <img src="{{ asset('images/logo-sgd-icon.svg') }}" alt="SGD" class="h-9 w-auto">
            </a>
            <!-- Cerrar sidebar (solo móvil) -->
            <button @click="sidebarOpen = false"
                    class="lg:hidden flex-shrink-0 text-slate-400 hover:text-slate-600 p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Navegación principal -->
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            <p class="text-xs font-semibold text-slate-400 uppercase
                      tracking-widest px-3 mb-2 mt-1">Principal</p>

            <!-- Dashboard (enlace según rol) -->
            <a href="{{ $dashboardUrl }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg
                      text-sm font-medium text-slate-600 transition-all cursor-pointer
                      {{ $isDashboardActive ? 'nav-item-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3.75 6v2.25a2.25 2.25 0 002.25 2.25h2.25a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 008.25 3.75H6A2.25 2.25 0 003.75 6zM3.75 15.75v2.25A2.25 2.25 0 006 20.25h2.25a2.25 2.25 0 002.25-2.25v-2.25a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25zM13.5 6v2.25a2.25 2.25 0 002.25 2.25H18a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 0018 3.75h-2.25A2.25 2.25 0 0013.5 6zM13.5 15.75v2.25a2.25 2.25 0 002.25 2.25H18a2.25 2.25 0 002.25-2.25v-2.25a2.25 2.25 0 00-2.25-2.25h-2.25a2.25 2.25 0 00-2.25 2.25z"/>
                </svg>
                Dashboard
            </a>

            @if($sidebarUser && $sidebarUser->hasAnyRole('administrador_sistema', 'admin'))
                {{-- Vistas desplegables para el rol Administrador (como en el mockup SGD) --}}

                {{-- GESTIÓN USUARIOS --}}
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">
                    Gestión Usuarios
                </p>

                <div x-data="{ openAdminUsers: {{ request()->routeIs('admin.usuarios.*', 'admin.users.manage*', 'admin.external-advisors.*') ? 'true' : 'false' }} }" class="mb-1">
                    <button @click="openAdminUsers = !openAdminUsers"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                            </svg>
                            <span>Usuarios y Roles</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 transition-transform"
                             :class="openAdminUsers ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openAdminUsers" x-collapse class="pl-11 pr-3 py-2 space-y-1">
                        <a href="{{ route('admin.usuarios.index') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.usuarios.index', 'admin.usuarios.create', 'admin.usuarios.edit') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            Usuarios
                        </a>
                        <a href="{{ route('admin.usuarios.asignar_roles') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.usuarios.asignar_roles') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0121.75 12.75a3 3 0 01-3 3z"/></svg>
                            Asignar Roles
                        </a>
                        <a href="{{ route('admin.usuarios.usuarios_con_rol') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.usuarios.usuarios_con_rol') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            Usuarios con rol
                        </a>
                        <a href="{{ route('admin.external-advisors.index') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.external-advisors.*') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            Asesores Externos
                        </a>
                    </div>
                </div>

                {{-- CATÁLOGOS --}}
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">
                    Catálogos
                </p>

                <div x-data="{ openCatalogs: {{ request()->routeIs('admin.training-centers.*', 'admin.training-programs.*', 'admin.minciencias-typologies.*', 'admin.knowledge-areas.*', 'admin.catalogos.*') ? 'true' : 'false' }} }" class="mb-1">
                    <button @click="openCatalogs = !openCatalogs"
                            class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/>
                            </svg>
                            <span>Gestión Catálogos</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 transition-transform"
                             :class="openCatalogs ? 'rotate-180' : ''"
                             fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="openCatalogs" x-collapse class="pl-11 pr-3 py-2 space-y-1">
                        <a href="{{ route('admin.training-centers.index') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.training-centers.*') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008V21z"/></svg>
                            Centros de Formación
                        </a>
                        <a href="{{ route('admin.training-programs.index') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.training-programs.*') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            Programas de Formación
                        </a>
                        <a href="{{ route('admin.minciencias-typologies.index') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.minciencias-typologies.*') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z"/><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z"/></svg>
                            Tipologías Minciencias
                        </a>
                        <a href="{{ route('admin.knowledge-areas.index') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.knowledge-areas.*') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25"/></svg>
                            Áreas del Conocimiento
                        </a>
                        <a href="{{ route('admin.catalogos.simples') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.catalogos.simples') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                            Catálogos Simples
                        </a>
                    </div>
                </div>

            @elseif($sidebarUser && $sidebarUser->hasRole('director_semilleros'))
                {{-- Menú Director de Semilleros --}}
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">GESTIÓN SEMILLEROS</p>
                <div x-data="{ openSemilleros: {{ request()->routeIs('dir-sem.semilleros.*', 'dir-sem.documentos.*', 'dir-sem.reportes.*') ? 'true' : 'false' }} }" class="mb-1">
                    <button type="button" @click="openSemilleros = !openSemilleros" class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                            <span>Mis Semilleros</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform" :class="openSemilleros ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSemilleros" x-cloak x-collapse class="pl-11 pr-3 py-2 space-y-1">
                        <a href="{{ route('dir-sem.semilleros.index') }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('dir-sem.semilleros.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                            Semilleros
                        </a>
                        <a href="{{ route('dir-sem.documentos.index') }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('dir-sem.documentos.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                            Documentos
                        </a>
                        <a href="{{ route('dir-sem.reportes.index') }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('dir-sem.reportes.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                            Reportes
                        </a>
                    </div>
                </div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">USUARIOS</p>
                <div x-data="{ openUsuarios: {{ request()->routeIs('dir-sem.lideres.*', 'dir-sem.asignar-roles.*') ? 'true' : 'false' }} }" class="mb-1">
                    <button type="button" @click="openUsuarios = !openUsuarios" class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M12 4.5v15m7.5-7.5h-15"/></svg>
                            <span>Usuarios</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform" :class="openUsuarios ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openUsuarios" x-cloak x-collapse class="pl-11 pr-3 py-2 space-y-1">
                        <a href="{{ route('dir-sem.lideres.index') }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('dir-sem.lideres.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            Líderes de Semillero
                        </a>
                        <a href="{{ route('dir-sem.asignar-roles.index') }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('dir-sem.asignar-roles.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 0121.75 12.75a3 3 0 01-3 3z"/></svg>
                            Asignar Roles
                        </a>
                    </div>
                </div>

            @elseif($sidebarUser && $sidebarUser->hasRole('lider_semillero'))
                {{-- Menú Líder de Semillero --}}
                @php
                    $lidSemillero = $sidebarUser->ledSeedlings()->first();
                    $lidIntegrantesCount = $lidSemillero ? $lidSemillero->members()->count() : 0;
                    $lidPendingCount = 0;
                    if ($lidSemillero) {
                        $pIds = \Illuminate\Support\Facades\DB::table('project_seedlings')->where('seedling_id', $lidSemillero->id)->pluck('project_id');
                        $prodIds = \Illuminate\Support\Facades\DB::table('products')->whereIn('project_id', $pIds)->pluck('id');
                        $lidPendingCount = \App\Models\GroupProduct::whereIn('product_id', $prodIds)->whereIn('estado_revision', ['pendiente', 'en_revision'])->count();
                    }
                @endphp
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">MI SEMILLERO</p>
                <a href="{{ route('lider-sem.info-semillero') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.info-semillero') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                    Info del Semillero
                </a>
                <a href="{{ route('lider-sem.integrantes') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.integrantes') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M12 4.5v15m7.5-7.5h-15"/></svg>
                    Integrantes
                    @if($lidIntegrantesCount > 0)<span class="ml-auto bg-amber-100 text-amber-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $lidIntegrantesCount }}</span>@endif
                </a>
                <a href="{{ route('lider-sem.asesores') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.asesores') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    Asesores
                </a>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">GESTIÓN</p>
                <a href="{{ route('lider-sem.proyectos') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.proyectos') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                    Proyectos
                </a>
                <a href="{{ route('lider-sem.productos') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.productos') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                    Productos
                    @if($lidPendingCount > 0)<span class="ml-auto bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $lidPendingCount }}</span>@endif
                </a>
                <a href="{{ route('lider-sem.aprendices') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.aprendices') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342 50.645 50.645 0 010 2.676A50.697 50.697 0 0112 13.489z"/></svg>
                    Aprendices
                </a>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">DOCUMENTACIÓN</p>
                <a href="{{ route('lider-sem.archivos') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.archivos') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 20.552m5.108-11.479l-2.28-2.28"/></svg>
                    Archivos Semillero
                </a>
                <a href="{{ route('lider-sem.doc-interna') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.doc-interna*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/></svg>
                    Doc. Interna
                </a>

            @else
                {{-- Otros roles (permisos por capacidad) --}}
                @can('semilleros.listar')
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">Gestión Semilleros</p>
                <a href="{{ route('dir-sem.dashboard') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('dir-sem.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                    Director Semilleros
                </a>
                @endcan
                @can('usuarios.listar')
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">Administración</p>
                <a href="{{ route('admin.usuarios.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('admin.usuarios.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                    Usuarios
                </a>
                @endcan
                @can('catalogos.leer')
                <a href="{{ route('admin.catalogos.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('admin.catalogos.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                    Catálogos
                </a>
                @endcan
                @hasrole('administrador_sistema|admin')
                <div x-data="{ paramOpen: {{ request()->routeIs('admin.departments.*', 'admin.cities.*', 'admin.training-centers.*', 'admin.entity-positions.*', 'admin.linkage-types.*', 'admin.training-records.*', 'admin.training-program-types.*', 'admin.training-programs.*', 'admin.research-lines.*', 'admin.technological-lines.*', 'admin.thematic-areas.*', 'admin.project-modalities.*', 'admin.investigation-types.*', 'admin.minciencias-typologies.*', 'admin.knowledge-grand-areas.*', 'admin.knowledge-areas.*') ? 'true' : 'false' }} }" class="mt-2">
                    <button @click="paramOpen = !paramOpen" class="w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 transition-all">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75"/></svg>
                            Datos Paramétricos
                        </div>
                        <svg class="w-4 h-4 text-slate-400 transition-transform" :class="paramOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="paramOpen" x-collapse class="pl-11 pr-3 py-2 space-y-1">
                        <a href="{{ route('admin.departments.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.departments.*') ? 'bg-slate-50 text-slate-900' : '' }}">Departamentos</a>
                        <a href="{{ route('admin.cities.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.cities.*') ? 'bg-slate-50 text-slate-900' : '' }}">Municipios</a>
                        <a href="{{ route('admin.training-centers.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.training-centers.*') ? 'bg-slate-50 text-slate-900' : '' }}">Centros de Form.</a>
                        <a href="{{ route('admin.entity-positions.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.entity-positions.*') ? 'bg-slate-50 text-slate-900' : '' }}">Cargos Entidades</a>
                        <a href="{{ route('admin.linkage-types.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.linkage-types.*') ? 'bg-slate-50 text-slate-900' : '' }}">Tipos de Vinculación</a>
                        <a href="{{ route('admin.training-records.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.training-records.*') ? 'bg-slate-50 text-slate-900' : '' }}">Fichas de Form.</a>
                        <a href="{{ route('admin.training-program-types.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.training-program-types.*') ? 'bg-slate-50 text-slate-900' : '' }}">Tipos Programas</a>
                        <a href="{{ route('admin.training-programs.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.training-programs.*') ? 'bg-slate-50 text-slate-900' : '' }}">Programas Form.</a>
                        <a href="{{ route('admin.research-lines.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.research-lines.*') ? 'bg-slate-50 text-slate-900' : '' }}">Líneas de Invest.</a>
                        <a href="{{ route('admin.technological-lines.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.technological-lines.*') ? 'bg-slate-50 text-slate-900' : '' }}">Líneas Tecnológicas</a>
                        <a href="{{ route('admin.thematic-areas.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.thematic-areas.*') ? 'bg-slate-50 text-slate-900' : '' }}">Áreas Temáticas</a>
                        <a href="{{ route('admin.project-modalities.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.project-modalities.*') ? 'bg-slate-50 text-slate-900' : '' }}">Modalidades Proy.</a>
                        <a href="{{ route('admin.investigation-types.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.investigation-types.*') ? 'bg-slate-50 text-slate-900' : '' }}">Tipos Invest.</a>
                        <a href="{{ route('admin.minciencias-typologies.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.minciencias-typologies.*') ? 'bg-slate-50 text-slate-900' : '' }}">Tipologías Mincien.</a>
                        <a href="{{ route('admin.knowledge-grand-areas.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.knowledge-grand-areas.*') ? 'bg-slate-50 text-slate-900' : '' }}">G. Áreas Conoc.</a>
                        <a href="{{ route('admin.knowledge-areas.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.knowledge-areas.*') ? 'bg-slate-50 text-slate-900' : '' }}">Áreas Conoc.</a>
                    </div>
                </div>
                @endhasrole

            @endif
        </nav>

        <!-- Footer del sidebar: usuario (fijo abajo, no se mueve) -->
        <div class="border-t border-slate-100 p-3 shrink-0 mt-auto bg-white" x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg
                           hover:bg-slate-50 transition-all text-left">
                <div class="w-8 h-8 rounded-full bg-[#0a1628] flex items-center
                            justify-center flex-shrink-0">
                    <span class="text-white text-xs font-bold">
                        {{ strtoupper(substr(Auth::user()->email ?? 'U', 0, 2)) }}
                    </span>
                </div>
                <div class="flex-1 overflow-hidden">
                    <p class="text-sm font-medium text-slate-800 truncate">
                        {{ Auth::user()->name ?? 'Usuario' }}
                    </p>
                    <p class="text-xs text-slate-400 truncate">
                        {{ Auth::user()->email ?? '' }}
                    </p>
                </div>
                <svg class="w-4 h-4 text-slate-400 transition-transform flex-shrink-0"
                     :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          stroke-width="2" d="M5 15l7-7 7 7"/>
                </svg>
            </button>

            <div x-show="open" x-cloak
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="mb-2 bg-white border border-slate-200 rounded-xl
                        shadow-lg overflow-hidden">

                <a href="{{ route('settings.profile') }}"
                   class="flex items-center gap-3 px-4 py-3 text-sm
                          text-slate-600 hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                         viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z"/>
                        <path stroke-linecap="round" stroke-linejoin="round"
                              d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Configuración
                </a>

                <div class="border-t border-slate-100"></div>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-3 px-4 py-3 text-sm
                                   text-red-500 hover:bg-red-50 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                             viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75"/>
                        </svg>
                        Cerrar sesión
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- ═══ CONTENIDO PRINCIPAL ═══ -->
    <div class="flex-1 lg:ml-64 flex flex-col min-h-screen">

        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center
                       justify-between px-6 sticky top-0 z-30">
            <!-- Hamburger (solo móvil) -->
            <button @click="sidebarOpen = true"
                    class="lg:hidden mr-3 text-slate-500 hover:text-slate-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                </svg>
            </button>
            <div>
                <h1 class="text-base font-semibold text-slate-900">
                    {{ $header ?? 'Dashboard' }}
                </h1>
                <p class="text-xs text-slate-400">
                    SGD — Sistema de Gestión Documental
                </p>
            </div>
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-[#39A900]"></div>
                    <span class="text-xs font-medium text-slate-600">{{ $roleLabel ?? 'Usuario' }}</span>
                    <span class="text-xs text-slate-400 hidden md:inline">—</span>
                    <span class="text-xs text-slate-500 truncate max-w-[120px] md:max-w-none">
                        {{ Auth::user()->name ?? Auth::user()->email ?? '' }}
                    </span>
                </div>
            </div>
        </header>

        <!-- Área de contenido -->
        <main class="sgd-main flex-1 p-6">
            <!-- Flash Messages -->
            @if(session('success'))
            <div x-data="{ show: true }" x-show="show" class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3">
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-green-500 hover:text-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endif

            @if(session('error'))
            <div x-data="{ show: true }" x-show="show" class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endif

            @if(session('warning'))
            <div x-data="{ show: true }" x-show="show" class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-start gap-3">
                <div class="flex-1">
                    <p class="text-sm font-medium text-amber-800">{{ session('warning') }}</p>
                </div>
                <button @click="show = false" class="text-amber-500 hover:text-amber-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            @endif

            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
    @livewireScripts
</body>
</html>
