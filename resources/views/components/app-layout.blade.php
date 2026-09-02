<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIGESI — {{ $header ?? 'Sistema de Gestión de Grupos y Semilleros de Investigación' }}</title>

    <link rel="icon" href="/favicon.ico?v=2" sizes="any">
    <link rel="icon" href="/favicon-32x32.png?v=2" type="image/png" sizes="32x32">
    <link rel="icon" href="/favicon-16x16.png?v=2" type="image/png" sizes="16x16">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|outfit:700,900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
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

        /* ═══ PRINT / PDF ═══ */
        @media print {
            aside, header, .no-print { display: none !important; }
            body { background: white !important; margin: 0 !important; }
            .flex-1.lg\:ml-64 { margin-left: 0 !important; }
            main { padding: 1rem !important; }
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .shadow-sm, .shadow, .shadow-lg, .shadow-md { box-shadow: none !important; }
        }
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
            $sidebarUser?->loadMissing('roles');
            $sidebarPrimaryRole = $sidebarUser ? \App\Support\RoleModuleLinks::primaryRole($sidebarUser) : null;
            $roleLabel = $sidebarPrimaryRole
                ? \App\Support\RoleModuleLinks::labelForRoleName($sidebarPrimaryRole->name)
                : 'Usuario';
            // BUG-20260813-056: antes usaba siempre el rol PRINCIPAL
            // (dashboardUrlForUser), sin importar el rol ACTIVO de sesión —
            // un usuario multi-rol que cambiaba de rol y luego hacía clic en
            // el logo (o en "Dashboard" del menú) volvía siempre al módulo
            // de su rol principal sin pasar por roles.switch, y el
            // aislamiento de la Fase 2 lo bloqueaba con 403. Ahora "inicio"
            // significa el dashboard del rol activo actual — nunca puede
            // dar 403 porque siempre navega dentro de tu propio contexto.
            $dashboardUrl = $sidebarUser
                ? (\App\Support\RoleModuleLinks::urlForRoleName(\App\Support\ActiveRoleContext::current())
                    ?? \App\Support\RoleModuleLinks::dashboardUrlForUser($sidebarUser))
                : route('dashboard');

            $sidebarRoleModuleLinks = ($sidebarUser && $sidebarUser->roles->count() > 1)
                ? \App\Support\RoleModuleLinks::moduleLinksWithPrimary($sidebarUser)
                : [];
            $sidebarOtherModuleLinks = [];
            foreach ($sidebarRoleModuleLinks as $_rl) {
                if (! empty($_rl['url']) && empty($_rl['is_primary'])) {
                    $sidebarOtherModuleLinks[] = $_rl;
                }
            }

            $routeMenuContext = null;
            if (request()->routeIs('super-admin.*')) {
                $routeMenuContext = 'super_administrador';
            } elseif (request()->routeIs('admin.*')) {
                $routeMenuContext = 'administrador_sistema';
            } elseif (request()->routeIs('dir-sem.*')) {
                $routeMenuContext = 'director_semilleros';
            } elseif (request()->routeIs('lider-sem.*')) {
                $routeMenuContext = 'lider_semillero';
            } elseif (request()->routeIs('lider-proyecto.*')) {
                $routeMenuContext = 'lider_proyecto';
            } elseif (request()->routeIs('co-investigador.*')) {
                $routeMenuContext = 'co_investigador';
            }

            // FEAT-20260830-001 (multi-rol): el rol activo en sesión manda
            // sobre la ruta actual, así el sidebar refleja el panel que el
            // usuario eligió desde el selector de roles — no solo la URL en
            // la que está (relevante en páginas compartidas fuera de los
            // prefijos por rol, como /settings/profile). Si el usuario no
            // tiene ningún rol activo resuelto, se cae al criterio anterior.
            $activeRoleName = $sidebarUser ? \App\Support\ActiveRoleContext::current() : null;
            $menuContext = $activeRoleName ?: ($routeMenuContext ?: ($sidebarPrimaryRole?->name));

            $showSuperAdmin = $sidebarUser && $sidebarUser->hasRole('super_administrador') && $menuContext === 'super_administrador';
            $showAdmin = $sidebarUser && $sidebarUser->hasAnyRole('administrador_sistema', 'admin')
                && in_array($menuContext, ['administrador_sistema', 'admin'], true);
            $showDirectorSem = $sidebarUser && $sidebarUser->hasRole('director_semilleros') && $menuContext === 'director_semilleros';
            $showLiderSem = $sidebarUser && $sidebarUser->hasRole('lider_semillero') && $menuContext === 'lider_semillero';
            $showLiderProyecto = $sidebarUser && $sidebarUser->hasRole('lider_proyecto') && $menuContext === 'lider_proyecto';
            $showCoinvestigador = $sidebarUser && $sidebarUser->hasRole('co_investigador') && $menuContext === 'co_investigador';

            // Punto rojo del Dashboard genérico: el líder de proyecto tiene una
            // revisión de formulación, ejecución o producto final sin ver
            // (aprobación o rechazo del líder de semillero o del director).
            $lpTieneNotificacion = $showLiderProyecto
                && \App\Models\ProjectEvidence::whereIn('tipo', [
                        \App\Enums\TipoEvidenciaEnum::Formulacion,
                        \App\Enums\TipoEvidenciaEnum::Ejecucion,
                        \App\Enums\TipoEvidenciaEnum::ProductoFinal,
                    ])
                    ->whereNull('visto_por_lider_proyecto_at')
                    ->whereHas('project', fn ($q) => $q->where('lider_proyecto_user_id', $sidebarUser->id))
                    ->exists();

            $currentContextLabel = $menuContext 
                ? \App\Support\RoleModuleLinks::labelForRoleName($menuContext) 
                : $roleLabel;

            $isDashboardActive = request()->routeIs('dashboard')
                || request()->routeIs('super-admin.dashboard')
                || request()->routeIs('admin.dashboard')
                || request()->routeIs('dir-sem.dashboard')
                || request()->routeIs('lider-sem.dashboard')
                || request()->routeIs('lider-proyecto.dashboard')
                || request()->routeIs('co-investigador.dashboard')
                || request()->routeIs('director.dashboard')
                || request()->routeIs('investigador.dashboard')
                || request()->routeIs('asesor.dashboard');

            $sidebarCentro = $sidebarUser
                ? cache()->remember(
                    "sidebar_centro_{$sidebarUser->id}",
                    now()->addHour(),
                    fn () => optional($sidebarUser->trainingCenter)->nombre ?? 'SENA'
                )
                : 'SENA';
        @endphp
        <!-- Logo -->
        <div class="h-16 flex items-center px-4 border-b border-slate-100">
            <a href="{{ $dashboardUrl }}" class="flex-1 min-w-0 flex items-center gap-2.5 justify-center lg:justify-start" title="SIGESI">
                <img src="{{ asset('images/sena-logo.png') }}" alt="SIGESI" class="h-8 w-auto flex-shrink-0">
                <div class="flex flex-col leading-tight min-w-0 overflow-hidden">
                    <span class="text-xs font-bold text-slate-800 tracking-tight">SIGESI</span>
                    <span class="text-[10px] text-slate-400 leading-snug line-clamp-2">{{ $sidebarCentro }}</span>
                </div>
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
                <span class="relative shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3.75 6v2.25a2.25 2.25 0 002.25 2.25h2.25a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 008.25 3.75H6A2.25 2.25 0 003.75 6zM3.75 15.75v2.25A2.25 2.25 0 006 20.25h2.25a2.25 2.25 0 002.25-2.25v-2.25a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25zM13.5 6v2.25a2.25 2.25 0 002.25 2.25H18a2.25 2.25 0 002.25-2.25V6A2.25 2.25 0 0018 3.75h-2.25A2.25 2.25 0 0013.5 6zM13.5 15.75v2.25a2.25 2.25 0 002.25 2.25H18a2.25 2.25 0 002.25-2.25v-2.25a2.25 2.25 0 00-2.25-2.25h-2.25a2.25 2.25 0 00-2.25 2.25z"/>
                    </svg>
                    @if($lpTieneNotificacion)<span class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white" title="Hay novedades en tu producto final"></span>@endif
                </span>
                Dashboard
            </a>



            @if($showSuperAdmin)
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">
                    Super administración
                </p>
                <a href="{{ route('super-admin.centros-administradores') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('super-admin.centros-administradores*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-600/90" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/>
                    </svg>
                    Centro ↔ administrador
                </a>
                <a href="{{ route('admin.training-centers.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('admin.training-centers.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-600/90" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008V21z"/>
                    </svg>
                    Centros de Formación
                </a>
                <a href="{{ route('super-admin.administradores.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('super-admin.administradores*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-600/90" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/>
                    </svg>
                    Usuarios Administradores
                </a>
                <a href="{{ route('super-admin.usuarios-sistema.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('super-admin.usuarios-sistema*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0 text-amber-600/90" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                    Usuarios del Sistema
                </a>
            @endif

            @if($showAdmin)
                {{-- Vistas desplegables para el rol Administrador (como en el mockup SGD) --}}

                {{-- GESTIÓN USUARIOS --}}
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">
                    Gestión Usuarios
                </p>

                <div x-data="{ openAdminUsers: {{ request()->routeIs('admin.usuarios.*', 'admin.director-semilleros.*', 'admin.co-investigadores.*') ? 'true' : 'false' }} }" class="mb-1">
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
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.usuarios.index', 'admin.usuarios.edit') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            Usuarios
                        </a>
                        <a href="{{ route('admin.director-semilleros.create') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.director-semilleros.*') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM3 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 019.374 21c-2.331 0-4.512-.645-6.374-1.766z"/></svg>
                            Director de Semilleros
                        </a>
                        <a href="{{ route('admin.co-investigadores.create') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.co-investigadores.*') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                            Co-investigadores
                        </a>
                    </div>
                </div>

                <a href="{{ route('admin.semilleros.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('admin.semilleros.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                    Semilleros
                </a>

                @can('minciencias.listar')
                @php
                    $adminMincienciasPendientes = $sidebarUser
                        ? \App\Models\MincienciasProduct::where('training_center_id', $sidebarUser->training_center_id)
                            ->where('estado_revision', 'pendiente')
                            ->count()
                        : 0;
                @endphp
                <a href="{{ route('admin.minciencias.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('admin.minciencias.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    Productos Minciencias
                    @if($adminMincienciasPendientes > 0)<span class="ml-auto bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $adminMincienciasPendientes }}</span>@endif
                </a>
                @endcan

                {{-- CATÁLOGOS --}}
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">
                    Catálogos
                </p>

                <div x-data="{ openCatalogs: {{ request()->routeIs('admin.training-centers.*', 'admin.training-programs.*', 'admin.minciencias-typologies.*', 'admin.catalogos.*') ? 'true' : 'false' }} }" class="mb-1">
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
                        <a href="{{ route('admin.catalogos.simples') }}"
                           class="flex items-center gap-2 p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.catalogos.simples') ? 'bg-slate-50 text-slate-900' : '' }}">
                            <svg class="w-4 h-4 flex-shrink-0 text-slate-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6"/></svg>
                            Catálogos Simples
                        </a>
                    </div>
                </div>

                {{-- REPORTES --}}
                @can('reportes.usuarios_por_rol')
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">Reportes</p>
                <a href="{{ route('admin.reportes.index') }}"
                   class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('admin.reportes.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Reportes
                </a>
                @endcan

            @elseif($showDirectorSem)
                {{-- Menú Director de Semilleros --}}
                <a href="{{ route('dir-sem.semilleros.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('dir-sem.semilleros.index', 'dir-sem.semilleros.create', 'dir-sem.semilleros.edit') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                    Administrar Semilleros
                </a>
                @php
                    $sidebarTodosSemilleros = $sidebarUser
                        ? \App\Models\Seedling::where('training_center_id', $sidebarUser->training_center_id)->orderBy('nombre')->get(['id', 'nombre', 'estado'])
                        : collect();
                @endphp
                <div x-data="{ openSemilleros: {{ request()->routeIs('dir-sem.semilleros.show') ? 'true' : 'false' }} }" class="mb-1">
                    <button type="button" @click="openSemilleros = !openSemilleros" class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                            <span>Semilleros</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform" :class="openSemilleros ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openSemilleros" x-cloak x-collapse class="pl-11 pr-3 py-2 space-y-1 max-h-64 overflow-y-auto">
                        @forelse($sidebarTodosSemilleros as $sidebarSemillero)
                        <a href="{{ route('dir-sem.semilleros.show', $sidebarSemillero) }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->route('semillero')?->id === $sidebarSemillero->id ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $sidebarSemillero->estado?->value === 'activo' ? 'bg-green-500' : 'bg-slate-300' }}"></span>
                            <span class="truncate">{{ $sidebarSemillero->nombre }}</span>
                        </a>
                        @empty
                        <p class="text-xs text-slate-400 py-1">Sin semilleros creados.</p>
                        @endforelse
                    </div>
                </div>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">USUARIOS</p>
                <div x-data="{ openUsuarios: {{ request()->routeIs('dir-sem.lideres.*', 'dir-sem.vinculaciones.*') ? 'true' : 'false' }} }" class="mb-1">
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
                        <a href="{{ route('dir-sem.vinculaciones.index') }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('dir-sem.vinculaciones.*') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75v10.5m-10.5-10.5v10.5M3.75 12h16.5"/></svg>
                            Vincular Semillero
                        </a>
                    </div>
                </div>

                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">PRODUCTOS</p>
                <a href="{{ route('dir-sem.productos.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('dir-sem.productos.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Aprobación definitiva
                </a>

            @elseif($showLiderSem)
                {{-- Menú Líder de Semillero --}}
                @php
                    $lidSemillero = $sidebarUser->ledSeedlings()->first();
                    // Contador para badge del menú del líder
                    $lidPendingCount = 0;
                    // Punto rojo: el director tomó una acción (aprobó/rechazó) sobre
                    // un producto que este líder ya había revisado, y aún no la ha visto.
                    $lidTieneNotificacionDirector = false;
                    if ($lidSemillero) {
                        // Evidencias de formulación/ejecución/producto final pendientes de revisión del líder
                        $lidPendingCount = \App\Models\ProjectEvidence::whereIn('tipo', [
                                \App\Enums\TipoEvidenciaEnum::Formulacion,
                                \App\Enums\TipoEvidenciaEnum::Ejecucion,
                                \App\Enums\TipoEvidenciaEnum::ProductoFinal,
                            ])
                            ->where('estado_revision_lider', \App\Enums\EstadoRevisionEnum::Pendiente)
                            ->whereHas('project', fn ($q) => $q->where('seedling_id', $lidSemillero->id))
                            ->count();
                        $lidTieneNotificacionDirector = \App\Models\ProjectEvidence::where('tipo', \App\Enums\TipoEvidenciaEnum::ProductoFinal)
                            ->whereNull('visto_por_lider_semillero_at')
                            // Excluye evidencias nuevas que este líder aún no ha
                            // revisado por primera vez (esas ya las cubre el badge
                            // de $lidPendingCount, no son "acción del director").
                            ->whereNotNull('revisado_lider_at')
                            ->whereHas('project', fn ($q) => $q->where('seedling_id', $lidSemillero->id))
                            ->exists();
                    }
                @endphp
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">MI SEMILLERO</p>
                <a href="{{ route('lider-sem.info-semillero') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.info-semillero') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z"/></svg>
                    Info del Semillero
                </a>
                <a href="{{ route('lider-sem.lider-proyecto.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.lider-proyecto.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    Líderes de Proyecto
                </a>
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">GESTIÓN</p>
                @php
                    $sidebarProyectosSemillero = $lidSemillero
                        ? \App\Models\Project::where('seedling_id', $lidSemillero->id)->orderBy('nombre')->get(['id', 'nombre', 'estado'])
                        : collect();
                @endphp
                <div x-data="{ openProyectos: {{ request()->routeIs('lider-sem.proyectos.show') ? 'true' : 'false' }} }" class="mb-1">
                    <button type="button" @click="openProyectos = !openProyectos" class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                            <span>Proyectos</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform" :class="openProyectos ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openProyectos" x-cloak x-collapse class="pl-11 pr-3 py-2 space-y-1 max-h-64 overflow-y-auto">
                        <a href="{{ route('lider-sem.proyectos') }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->routeIs('lider-sem.proyectos') ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            Ver todos
                        </a>
                        @forelse($sidebarProyectosSemillero as $sidebarProyecto)
                        <a href="{{ route('lider-sem.proyectos.show', $sidebarProyecto) }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->route('proyecto')?->id === $sidebarProyecto->id ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $sidebarProyecto->estado?->value === 'activo' ? 'bg-green-500' : 'bg-slate-300' }}"></span>
                            <span class="truncate">{{ $sidebarProyecto->nombre }}</span>
                        </a>
                        @empty
                        <p class="text-xs text-slate-400 py-1">Sin proyectos creados.</p>
                        @endforelse
                    </div>
                </div>
                <a href="{{ route('lider-sem.productos') }}" class="nav-item relative flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.productos') ? 'nav-item-active' : '' }}">
                    <span class="relative shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z"/></svg>
                        @if($lidTieneNotificacionDirector)<span class="absolute -top-0.5 -right-0.5 w-2 h-2 rounded-full bg-red-500 ring-2 ring-white" title="El Director de Semilleros tomó una acción sobre un producto"></span>@endif
                    </span>
                    Productos
                    @if($lidPendingCount > 0)<span class="ml-auto bg-red-100 text-red-800 text-xs font-semibold px-2 py-0.5 rounded-full">{{ $lidPendingCount }}</span>@endif
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
                <a href="{{ route('lider-sem.reportes.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-sem.reportes.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z"/></svg>
                    Reportes
                </a>

            @elseif($showLiderProyecto)
                {{-- Menú Líder de Proyecto --}}
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">MI PROYECTO</p>
                <a href="{{ route('lider-proyecto.evidencias.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-proyecto.evidencias.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M18.375 12.739l-7.693 7.693a4.5 4.5 0 01-6.364-6.364l10.94-10.94A3 3 0 1119.5 7.372L8.552 20.552m5.108-11.479l-2.28-2.28"/></svg>
                    Evidencias
                </a>
                <a href="{{ route('lider-proyecto.aprendices.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-proyecto.aprendices.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.627 48.627 0 0112 20.904a48.627 48.627 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 0110.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0112 13.489a50.702 50.702 0 017.74-3.342M6.75 15a.75.75 0 100-1.5.75.75 0 000 1.5zm0 0v-3.675A55.378 55.378 0 0112 8.443"/></svg>
                    Aprendices
                </a>
                <a href="{{ route('lider-proyecto.coinvestigadores.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('lider-proyecto.coinvestigadores.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/></svg>
                    Co-investigadores
                </a>

            @elseif($showCoinvestigador)
                {{-- Menú Co-investigador --}}
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">MIS PROYECTOS</p>
                @php
                    $sidebarProyectosCoinvestigador = $sidebarUser
                        ? \App\Models\Project::whereHas('projectAuthors', fn ($q) => $q->where('user_id', $sidebarUser->id)->where('activo', true))
                            ->orderBy('nombre')
                            ->get(['id', 'nombre', 'estado'])
                        : collect();
                @endphp
                <div x-data="{ openProyectos: {{ request()->routeIs('co-investigador.proyectos.show') ? 'true' : 'false' }} }" class="mb-1">
                    <button type="button" @click="openProyectos = !openProyectos" class="nav-item w-full flex items-center justify-between px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all text-left">
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z"/></svg>
                            <span>Proyectos Vinculados</span>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 shrink-0 transition-transform" :class="openProyectos ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="openProyectos" x-cloak x-collapse class="pl-11 pr-3 py-2 space-y-1 max-h-64 overflow-y-auto">
                        @forelse($sidebarProyectosCoinvestigador as $sidebarProyecto)
                        <a href="{{ route('co-investigador.proyectos.show', $sidebarProyecto) }}" class="flex items-center gap-2 py-2 rounded-lg text-xs font-medium {{ request()->route('proyecto')?->id === $sidebarProyecto->id ? 'bg-slate-100 text-slate-900' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                            <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $sidebarProyecto->estado?->value === 'activo' ? 'bg-green-500' : 'bg-slate-300' }}"></span>
                            <span class="truncate">{{ $sidebarProyecto->nombre }}</span>
                        </a>
                        @empty
                        <p class="text-xs text-slate-400 py-1">Sin proyectos vinculados.</p>
                        @endforelse
                    </div>
                </div>

                <a href="{{ route('co-investigador.productos.index') }}" class="nav-item flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all {{ request()->routeIs('co-investigador.productos.*') ? 'nav-item-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/></svg>
                    Producto Minciencias
                </a>

            @else
                {{-- Otros roles (permisos por capacidad) — el super_administrador tiene su propio bloque arriba.
                     BUG-20260813-056: estos 3 enlaces navegan a módulos con
                     active_role: propio (dir-sem.dashboard, admin.usuarios.index,
                     admin.catalogos.index). @can()/@hasrole() aquí miran los
                     permisos de TODOS los roles asignados, no solo el activo —
                     un usuario con, por ejemplo, director_semilleros como rol
                     ADICIONAL (no activo) veía este enlace igual, y el clic
                     daba 403. Se cambian a formularios roles.switch, igual
                     que el selector "Mis roles". --}}
                @if(!$showSuperAdmin)
                @can('semilleros.listar')
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">Gestión Semilleros</p>
                <form action="{{ route('roles.switch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="director_semilleros">
                    <button type="submit" class="nav-item w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('dir-sem.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0012 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75z"/></svg>
                        Director Semilleros
                    </button>
                </form>
                @endcan
                @can('usuarios.listar')
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-widest px-3 mb-2 mt-4">Administración</p>
                <form action="{{ route('roles.switch') }}" method="POST">
                    @csrf
                    <input type="hidden" name="role" value="administrador_sistema">
                    <button type="submit" class="nav-item w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('admin.usuarios.*') ? 'nav-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        Usuarios
                    </button>
                </form>
                @endcan
                @hasrole('super_administrador|administrador_sistema|admin')
                    @can('catalogos.leer')
                    <form action="{{ route('roles.switch') }}" method="POST">
                        @csrf
                        <input type="hidden" name="role" value="administrador_sistema">
                        <button type="submit" class="nav-item w-full text-left flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-600 transition-all cursor-pointer {{ request()->routeIs('admin.catalogos.*') ? 'nav-item-active' : '' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z"/></svg>
                            Catálogos
                        </button>
                    </form>
                    @endcan
                @endhasrole
                @hasrole('super_administrador|administrador_sistema|admin')
                <div x-data="{ paramOpen: {{ request()->routeIs('admin.departments.*', 'admin.cities.*', 'admin.training-centers.*', 'admin.entity-positions.*', 'admin.linkage-types.*', 'admin.training-program-types.*', 'admin.training-programs.*', 'admin.research-lines.*', 'admin.technological-lines.*', 'admin.thematic-areas.*', 'admin.project-modalities.*', 'admin.investigation-types.*', 'admin.minciencias-typologies.*') ? 'true' : 'false' }} }" class="mt-2">
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
                        @if($sidebarUser && $sidebarUser->hasRole('super_administrador'))
                            <a href="{{ route('admin.training-centers.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.training-centers.*') ? 'bg-slate-50 text-slate-900' : '' }}">Centros de Form.</a>
                        @endif
                        <a href="{{ route('admin.entity-positions.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.entity-positions.*') ? 'bg-slate-50 text-slate-900' : '' }}">Cargo / Posición</a>
                        <a href="{{ route('admin.linkage-types.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.linkage-types.*') ? 'bg-slate-50 text-slate-900' : '' }}">Tipos de Vinculación</a>
                        <a href="{{ route('admin.training-program-types.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.training-program-types.*') ? 'bg-slate-50 text-slate-900' : '' }}">Tipos Programas</a>
                        <a href="{{ route('admin.training-programs.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.training-programs.*') ? 'bg-slate-50 text-slate-900' : '' }}">Programas Form.</a>
                        <a href="{{ route('admin.research-lines.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.research-lines.*') ? 'bg-slate-50 text-slate-900' : '' }}">Líneas de Invest.</a>
                        <a href="{{ route('admin.technological-lines.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.technological-lines.*') ? 'bg-slate-50 text-slate-900' : '' }}">Líneas Tecnológicas</a>
                        <a href="{{ route('admin.thematic-areas.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.thematic-areas.*') ? 'bg-slate-50 text-slate-900' : '' }}">Áreas Temáticas</a>
                        <a href="{{ route('admin.project-modalities.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.project-modalities.*') ? 'bg-slate-50 text-slate-900' : '' }}">Modalidades Proy.</a>
                        <a href="{{ route('admin.investigation-types.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.investigation-types.*') ? 'bg-slate-50 text-slate-900' : '' }}">Tipos Invest.</a>
                        <a href="{{ route('admin.minciencias-typologies.index') }}" class="block p-2 rounded-lg text-xs font-medium text-slate-500 hover:text-slate-800 hover:bg-slate-50 {{ request()->routeIs('admin.minciencias-typologies.*') ? 'bg-slate-50 text-slate-900' : '' }}">Tipologías Mincien.</a>
                    </div>
                </div>
                @endhasrole
                @endif {{-- !$showSuperAdmin --}}

            @endif
        </nav>

        <!-- Footer del sidebar: usuario (fijo abajo, no se mueve) -->
        <div class="border-t border-slate-100 p-3 shrink-0 mt-auto bg-white" x-data="{ open: false }">
            <button @click="open = !open"
                    class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg
                           hover:bg-slate-50 transition-all text-left">
                <livewire:shared.user-name-display />
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

                <a href="{{ route('profile.edit') }}"
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
        @unless(request()->has('embedded'))
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 sticky top-0 z-30">
            <div></div>
            <div class="flex items-center gap-3 sm:gap-4 shrink-0">
                
                <!-- SELECTOR DE ROLES (DROPDOWN) -->
                @if(!empty($sidebarRoleModuleLinks))
                <div x-data="{ openRoles: false }" class="relative">
                    <button @click="openRoles = !openRoles" @click.away="openRoles = false"
                            class="flex items-center gap-2 px-3 py-1.5 border border-slate-200 rounded-lg hover:bg-slate-50 transition-colors text-xs font-medium text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#39A900]/50"
                            title="Mis roles — cambiar de panel">
                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/></svg>
                        <span class="text-[10px] text-slate-400 shrink-0">Mis roles:</span>
                        <span class="truncate max-w-[150px]">{{ $currentContextLabel }}</span>
                        <svg class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="openRoles ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    
                    <div x-show="openRoles" x-cloak
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="opacity-100 scale-100"
                         x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-72 bg-white border border-slate-200 rounded-xl shadow-[0_4px_20px_-4px_rgba(0,0,0,0.1)] z-50 overflow-hidden">
                        
                        <div class="p-3 bg-slate-50 border-b border-slate-100">
                            <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest mb-1">Mis roles</p>
                            <p class="text-[10px] text-slate-500 leading-snug">
                                Rol principal: <span class="font-medium text-slate-700">{{ $roleLabel }}</span>.
                            </p>
                        </div>

                        <div class="py-1 max-h-64 overflow-y-auto">
                            <!-- ROL PRINCIPAL -->
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Panel Principal</div>
                            @foreach($sidebarRoleModuleLinks as $rl)
                                @if(!empty($rl['url']) && !empty($rl['is_primary']))
                                    <form action="{{ route('roles.switch') }}" method="POST" class="mx-2 mb-2">
                                        @csrf
                                        <input type="hidden" name="role" value="{{ $rl['role'] }}">
                                        <button type="submit"
                                                class="group w-full flex items-center justify-between gap-2.5 rounded-lg px-3 py-2 text-xs font-semibold text-[#1f6b00] bg-[#39A900]/5 border border-[#39A900]/30 hover:bg-[#39A900]/10 transition-colors">
                                            <span class="truncate">{{ $rl['label'] }}</span>
                                            <span class="shrink-0 text-[9px] uppercase tracking-wide text-[#39A900]">{{ $rl['role'] === $activeRoleName ? 'Activo ahora' : 'Volver a este rol' }}</span>
                                        </button>
                                    </form>
                                @endif
                            @endforeach

                            <!-- PANELES ALTERNOS (PERMISOS) -->
                            <div class="px-3 pt-2 pb-1 text-[10px] font-bold text-slate-400 uppercase tracking-widest border-t border-slate-100">Paneles / Permisos Alternos</div>
                            @foreach($sidebarRoleModuleLinks as $rl)
                                @if(!empty($rl['url']) && empty($rl['is_primary']))
                                    <form action="{{ route('roles.switch') }}" method="POST" class="mx-2 mb-1">
                                        @csrf
                                        <input type="hidden" name="role" value="{{ $rl['role'] }}">
                                        <button type="submit"
                                                class="group w-full flex items-center justify-between gap-2.5 rounded-lg px-3 py-2 text-xs font-medium text-slate-600 border border-transparent hover:bg-slate-50 hover:border-slate-200 transition-colors">
                                            <span class="truncate">{{ $rl['label'] }}</span>
                                            @if($rl['role'] === $activeRoleName)
                                            <span class="shrink-0 text-[9px] uppercase tracking-wide text-[#39A900]">Activo ahora</span>
                                            @else
                                            <span class="flex items-center gap-1 shrink-0 text-slate-400 group-hover:text-slate-500">
                                                <span class="text-[10px]">Cambiar</span>
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25"/></svg>
                                            </span>
                                            @endif
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif
                <!-- FIN SELECTOR DE ROLES -->

                <div class="hidden sm:flex items-center gap-2 min-w-0">
                    <div class="w-2 h-2 rounded-full bg-[#39A900] shrink-0"></div>
                    <livewire:shared.user-name-display mode="topbar" />
                </div>
            </div>
        </header>
        @endunless

        <!-- Área de contenido (min-w-0 evita que el título y textos se corten en flex) -->
        <main class="sgd-main flex-1 p-6 min-w-0">
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

            {{-- Banner de verificación de correo (opcional, descartable) --}}
            @auth
                @if(!Auth::user()->hasVerifiedEmail())
                <div x-data="{ show: true }" x-show="show" x-cloak
                     class="mb-6 bg-amber-50 border border-amber-200 rounded-lg p-4 flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75"/>
                    </svg>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-amber-800">Verifica tu correo electrónico</p>
                        <p class="text-xs text-amber-700 mt-0.5">
                            Revisa tu bandeja de entrada o
                            <form method="POST" action="{{ route('verification.send') }}" class="inline">
                                @csrf
                                <button type="submit" class="underline font-medium hover:text-amber-900 transition-colors">
                                    reenvía el enlace aquí
                                </button>
                            </form>.
                            Una vez verificado, este aviso desaparecerá.
                        </p>
                    </div>
                    <button @click="show = false" class="text-amber-400 hover:text-amber-600 shrink-0 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                @endif
            @endauth

            {{ $slot }}
        </main>
    </div>

    @stack('scripts')
    @livewireScripts
</body>
</html>
