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
        .nav-item-active {
            background: #f0fdf4;
            color: #39A900;
            border-left: 3px solid #39A900;
        }
        .nav-item-active svg { color: #39A900; }
        .nav-item:hover:not(.nav-item-active) {
            background: #f8fafc;
            color: #1e293b;
        }
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 2px; }
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
         class="fixed inset-0 z-40 bg-black/30 lg:hidden"></div>

    <!-- ═══ SIDEBAR ═══ -->
    <aside class="w-64 min-h-screen bg-white border-r border-slate-200
                  flex flex-col fixed left-0 top-0 z-50
                  transition-transform duration-200
                  lg:translate-x-0"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <!-- Logo + nombre del sistema -->
        <div class="h-16 flex items-center px-5 border-b border-slate-100">
            <a href="{{ route('dashboard') }}" class="flex items-center">
                <img src="{{ asset('images/logo-sgd-horizontal.svg') }}" alt="Sistema de Gestión Documental" class="h-8 w-auto">
            </a>
            <!-- Cerrar sidebar (solo móvil) -->
            <button @click="sidebarOpen = false"
                    class="lg:hidden ml-auto text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <!-- Navegación principal -->
        <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">

            <p class="text-xs font-semibold text-slate-400 uppercase
                      tracking-widest px-3 mb-2 mt-1">Principal</p>

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
               class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg
                      text-sm font-medium text-slate-600 transition-all cursor-pointer
                      {{ request()->routeIs('dashboard') ? 'nav-item-active' : '' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none"
                     stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/>
                </svg>
                Dashboard
            </a>

            {{-- ZONA DE EXPANSIÓN FUTURA: agregar módulos aquí --}}

        </nav>

        <!-- Footer del sidebar: usuario -->
        <div class="border-t border-slate-100 p-3" x-data="{ open: false }">
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
                    <span class="text-xs text-slate-500">
                        {{ Auth::user()->name ?? '' }}
                    </span>
                </div>
            </div>
        </header>

        <!-- Área de contenido -->
        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>

    @livewireScripts
</body>
</html>
