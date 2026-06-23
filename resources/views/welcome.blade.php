<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Red Nacional de Investigación Académica y Semilleros</title>

        <link rel="icon" href="/favicon.ico?v=2" sizes="any">
        <link rel="icon" href="/favicon-32x32.png?v=2" type="image/png" sizes="32x32">
        <link rel="icon" href="/favicon-16x16.png?v=2" type="image/png" sizes="16x16">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png?v=2">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|outfit:500,600,700,900&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.3/dist/cdn.min.js"></script>

        <style>
            /* Alpine.js: hide elements with x-cloak until Alpine initializes */
            [x-cloak] { display: none !important; }

            :root {
                --emerald-50: #ecfdf5;
                --emerald-500: #10b981;
                --emerald-600: #059669;
                --emerald-700: #047857;
                --electric-blue: #3b82f6;
                --slate-50: #f8fafc;
                --slate-200: #e2e8f0;
                --slate-900: #0f172a;
            }

            body {
                font-family: 'Inter', sans-serif;
                background-color: var(--slate-50);
                color: var(--slate-900);
                overflow-x: hidden;
            }

            h1, h2, h3, .font-heading {
                font-family: 'Outfit', sans-serif;
            }

            /* Custom Grid Background */
            .grid-bg {
                background-image: 
                    linear-gradient(to right, rgba(226, 232, 240, 0.5) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(226, 232, 240, 0.5) 1px, transparent 1px);
                background-size: 40px 40px;
            }

            /* Glassmorphism Styles */
            .glass-card {
                background: rgba(255, 255, 255, 0.7);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border: 1px solid rgba(255, 255, 255, 0.4);
                box-shadow: 0 8px 32px 0 rgba(15, 23, 42, 0.05);
            }

            .glass-nav {
                background: rgba(248, 250, 252, 0.8);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
                border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            }

            /* Accent Glows */
            .glow-emerald {
                filter: blur(80px);
                background: radial-gradient(circle, var(--emerald-500), transparent 70%);
                opacity: 0.15;
            }

            .glow-blue {
                filter: blur(80px);
                background: radial-gradient(circle, var(--electric-blue), transparent 70%);
                opacity: 0.15;
            }

            /* P4 — Hide glow ornaments on mobile to avoid constant repaints */
            @media (max-width: 767px) {
                .glow-emerald, .glow-blue { display: none; }
            }

            /* Buttons */
            .btn-primary {
                background: linear-gradient(135deg, var(--emerald-500), var(--emerald-600));
                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 14px 0 rgba(16, 185, 129, 0.3);
            }
            .btn-primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(16, 185, 129, 0.4);
            }

            .btn-secondary {
                border: 1px solid var(--slate-200);
                background: white;
                transition: all 0.3s ease;
            }
            .btn-secondary:hover {
                background: var(--slate-50);
                border-color: var(--emerald-500);
            }

            /* ─── P3 — Refined Animations ──────────────────────────────────────────── */
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(12px); }
                to   { opacity: 1; transform: translateY(0); }
            }

            @keyframes softReveal {
                from { opacity: 0; transform: translateY(8px) scale(0.99); }
                to   { opacity: 1; transform: translateY(0) scale(1); }
            }

            @keyframes slideInLeft {
                from { opacity: 0; transform: translateX(-8px); }
                to   { opacity: 1; transform: translateX(0); }
            }

            @keyframes float {
                0%   { transform: translateY(0px); }
                50%  { transform: translateY(-10px); }
                100% { transform: translateY(0px); }
            }

            .animate-fadeInUp {
                animation: fadeInUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            }

            .animate-softReveal {
                animation: softReveal 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            }

            .animate-slideInLeft {
                animation: slideInLeft 0.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            }

            .animate-float {
                animation: float 6s ease-in-out infinite;
            }

            .delay-100 { animation-delay: 0.1s; }
            .delay-200 { animation-delay: 0.2s; }
            .delay-300 { animation-delay: 0.3s; }

            /* ─── Fallback móvil: aurora animada CSS puro ─────────────────────────── */
            @keyframes aurora {
                0%   { background-position: 0% 50%; }
                50%  { background-position: 100% 50%; }
                100% { background-position: 0% 50%; }
            }

            .hero-aurora-bg {
                background: linear-gradient(135deg, #0a1628, #0d2c1a, #0a1628, #091e42, #1a3a2a);
                background-size: 400% 400%;
                animation: aurora 12s ease infinite;
            }

            /* ─── P3 — Roles section: subtler pre-animation state ─────────────────── */
            .role-card-hidden {
                opacity: 0;
                transform: translateY(8px) scale(0.99);
            }

            /* ─── Timeline section ────────────────────────────────────────────────── */
            .timeline-line-fill {
                width: 3px;
                background: linear-gradient(to bottom, #39A900, #3b82f6, #39A900, #3b82f6, #39A900);
                transition: height 0.1s linear;
            }

            /* P3 — Timeline: subtler pre-animation */
            .timeline-step-hidden {
                opacity: 0;
                transform: translateX(-8px);
            }

            .timeline-step-visible {
                animation: slideInLeft 0.4s cubic-bezier(0.22, 1, 0.36, 1) forwards;
            }

            .timeline-circle {
                width: 44px;
                height: 44px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: 700;
                font-size: 0.875rem;
                color: #fff;
                flex-shrink: 0;
                position: relative;
                z-index: 2;
                box-shadow: 0 4px 14px rgba(0,0,0,0.1);
            }

            /* ─── Grid background subtle for white sections ───────────────────────── */
            .grid-bg-subtle {
                background-image: 
                    linear-gradient(to right, rgba(226, 232, 240, 0.25) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(226, 232, 240, 0.25) 1px, transparent 1px);
                background-size: 40px 40px;
            }

            /* ─── P1 — GPU compositing hints for video ────────────────────────────── */
            #hero-video {
                will-change: contents;
                transform: translateZ(0);
                -webkit-transform: translateZ(0);
            }
        </style>
    </head>
    <body class="antialiased selection:bg-emerald-100 relative">
        <!-- 
          ========== DOCUMENTACIÓN DEL DISEÑO Y RESTRICCIONES ==========
          - SECCIONES ACTUALES:
            1. Navbar (.glass-nav): Contiene la navegación principal e integración con Livewire/Blade auth. NO MODIFICAR lógicas.
            2. Hero (#hero-section): Sección sticky interactiva y scroll-driven (200vh).
            3. Roles del sistema: Tarjetas de roles con animación softReveal.
            4. Cómo funciona: Línea de tiempo vertical con pasos slideInLeft.
            5. Footer: Información institucional y copyright.
          - CLASES Y ESTILOS: TailwindCSS standard en combinación con colores base:
            · Verde SENA: #39A900
            · Azul Oscuro: #0a1628
            · Blanco: #ffffff
            · Azul Eléctrico: #1e90ff
          - ELEMENTOS PROTEGIDOS (NO ELIMINAR NI CAMBIAR IDS/NOMBRES):
            · Formularios u opciones de Auth de app() original (`route('login')`).
            · Utilidades que Alpine o Livewire puedan requerir en frontend general.
          ==============================================================
        -->
        <!-- Glow Ornaments (hidden on mobile via CSS media query) -->
        <div class="fixed top-[-10%] left-[-5%] w-[40%] h-[40%] glow-emerald pointer-events-none"></div>
        <div class="fixed bottom-[10%] right-[-5%] w-[40%] h-[40%] glow-blue pointer-events-none"></div>

        <!-- ═══ P2 — Navbar with mobile hamburger (Alpine.js x-data) ═══ -->
        <nav class="glass-nav fixed top-0 w-full z-50 py-4" x-data="{ open: false }">
            <div class="max-w-7xl mx-auto px-6 flex items-center justify-between relative">
                <div class="flex items-center gap-2 text-decoration-none">
                    <img src="{{ asset('images/sena-logo.png') }}" alt="SIGESI" class="w-8 h-8 rounded-lg shadow-sm">
                    <span class="font-heading font-bold text-lg tracking-tight text-slate-900"><span class="text-[#39A900]">SIGESI</span></span>
                </div>
                
                <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="#roles-section" class="hover:text-emerald-600 transition-colors">Roles</a>
                    <a href="#workflow-section" class="hover:text-emerald-600 transition-colors">Cómo funciona</a>
                    <a href="#" class="hover:text-emerald-600 transition-colors">Repositorio</a>
                    <a href="#" class="hover:text-emerald-600 transition-colors">Nosotros</a>
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="btn-primary text-white px-5 py-2 rounded-lg text-sm font-semibold">Panel de Control</a>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline-flex btn-primary text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm cursor-pointer">Ingresar al Sistema</a>
                    @endauth

                    <!-- P2 — Hamburger button (mobile only) -->
                    <button @click="open = !open" class="md:hidden p-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 transition-colors" aria-label="Menú">
                        <svg x-show="!open" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                        <svg x-show="open" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- P2 — Mobile dropdown menu -->
            <div x-show="open" x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-1"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-1"
                 x-cloak
                 class="md:hidden absolute top-full left-0 right-0 glass-nav border-t border-slate-200 py-4 px-6 flex flex-col gap-4">
                <a href="#roles-section" @click="open = false" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-1">Roles del sistema</a>
                <a href="#workflow-section" @click="open = false" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-1">Cómo funciona</a>
                <a href="#" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-1">Repositorio</a>
                <a href="#" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors py-1">Nosotros</a>
                @guest
                    <hr class="border-slate-200">
                    <a href="{{ route('login') }}" @click="open = false" class="text-left text-sm font-semibold text-[#39A900] hover:text-[#2d8500] transition-colors py-1 cursor-pointer">Ingresar al Sistema</a>
                @endguest
            </div>
        </nav>

        <!-- ═══ Hero Section ═══ -->
        <section id="hero-section" style="height: 200vh;" class="relative overflow-visible hidden md:block">
            <!-- Video fijo en background y contenedor pegajoso -->
            <div class="sticky top-0 h-screen overflow-hidden">
                <!-- Fallback aurora: visible en móvil por defecto, JS lo activa en desktop si video no seekable -->
                <div id="hero-aurora-fallback" class="block md:hidden absolute inset-0 w-full h-full hero-aurora-bg z-[1]"></div>
                
                <!-- Video para Desktop/Tablet con object-cover. Controlado sólo por scroll -->
                <video id="hero-video" class="hidden md:block absolute inset-0 w-full h-full object-cover" 
                       muted playsinline preload="auto">
                    <source src="{{ asset('images/hero-v2.mp4') }}" type="video/mp4">
                    <source src="{{ asset('images/hero-scrubbing.webm') }}" type="video/webm">
                </video>
                
                <!-- P2 — Overlay: lighter on mobile so aurora shows through -->
                <div class="absolute inset-0 z-[2] bg-gradient-to-b from-[#0a1628]/60 via-[#0a1628]/40 to-[#0a1628]/30 md:from-[#0a1628]/80 md:via-[#0a1628]/60 md:to-[#0a1628]/40"></div>
                
                <!-- P2 — Content: responsive sizing + pt-24 for navbar -->
                <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-6 pt-20 md:pt-0">
                    <h1 class="text-3xl sm:text-5xl md:text-7xl font-heading font-black leading-tight text-white animate-fadeInUp delay-100 max-w-4xl mx-auto drop-shadow-lg">
                        Centraliza la Investigación <span class="text-[#39A900]">SENA</span>
                    </h1>
                    
                    <p class="text-base sm:text-xl md:text-2xl text-white/70 font-light mt-6 max-w-2xl mx-auto animate-fadeInUp delay-200">
                        Gestiona grupos de investigación, semilleros, proyectos y productos académicos desde una sola plataforma institucional.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 mt-10 animate-fadeInUp delay-300 w-full sm:w-auto">
                        <!-- CTA Login: abre modal -->
                        @guest
                        <a href="{{ route('login') }}" class="w-full sm:w-auto bg-[#39A900] hover:bg-[#2d8500] text-white px-8 py-4 rounded-full font-bold text-base shadow-lg transition-transform hover:scale-105 flex items-center justify-center gap-2 cursor-pointer">
                            Ingresar al Sistema
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </a>
                        @else
                        <a href="{{ url('/dashboard') }}" class="w-full sm:w-auto bg-[#39A900] hover:bg-[#2d8500] text-white px-8 py-4 rounded-full font-bold text-base shadow-lg transition-transform hover:scale-105 flex items-center justify-center gap-2">
                            Panel de Control
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        @endguest
                    </div>
                </div>

                <!-- Indicador de scroll down animado -->
                <div id="scroll-indicator" class="absolute bottom-10 left-1/2 transform -translate-x-1/2 flex flex-col items-center gap-2 text-white/70 animate-bounce transition-opacity duration-300 z-10">
                    <span class="text-sm font-medium tracking-widest uppercase">Desliza para explorar</span>
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                    </svg>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════════════════════════════════════ -->
        <!--  SECCIÓN: ROLES DEL SISTEMA                                             -->
        <!-- ════════════════════════════════════════════════════════════════════════ -->
        <section id="roles-section" class="relative bg-white grid-bg-subtle py-16 md:py-24">
            <div class="max-w-7xl mx-auto px-6">

                <!-- Encabezado de sección -->
                <div class="text-center mb-12 md:mb-16">
                    <span class="inline-block text-[#39A900] uppercase tracking-widest text-xs font-semibold mb-3">ROLES DEL SISTEMA</span>
                    <h2 class="font-heading font-black text-3xl sm:text-4xl md:text-5xl text-slate-900 mb-4">Un espacio para cada actor</h2>
                    <p class="text-slate-500 text-base md:text-lg max-w-2xl mx-auto">
                        Desde el administrador hasta el asesor, cada rol tiene acceso exacto a lo que necesita.
                    </p>
                </div>

                <!-- P2+P3 — Fila 1: 3 tarjetas, p-6 móvil / p-8 md -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    
                    <!-- TARJETA 1 — Administrador -->
                    <div class="glass-card rounded-2xl p-6 md:p-8 role-card-hidden transition-transform duration-300 hover:scale-[1.03]" data-role-card>
                        <div class="w-14 h-14 rounded-xl bg-[#39A900]/10 flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        <h3 class="font-heading font-bold text-xl text-slate-900 mb-2">Administrador</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Configura el sistema, gestiona catálogos y crea los usuarios de nivel superior del centro de formación.</p>
                    </div>

                    <!-- TARJETA 2 — Director de Investigación -->
                    <div class="glass-card rounded-2xl p-6 md:p-8 role-card-hidden transition-transform duration-300 hover:scale-[1.03]" data-role-card>
                        <div class="w-14 h-14 rounded-xl bg-[#3b82f6]/10 flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <h3 class="font-heading font-bold text-xl text-slate-900 mb-2">Director de Investigación</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Supervisa el grupo, vincula investigadores, revisa productos y genera reportes de producción académica.</p>
                    </div>

                    <!-- TARJETA 3 — Investigador Asociado -->
                    <div class="glass-card rounded-2xl p-6 md:p-8 role-card-hidden transition-transform duration-300 hover:scale-[1.03]" data-role-card>
                        <div class="w-14 h-14 rounded-xl bg-[#39A900]/10 flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                        </div>
                        <h3 class="font-heading font-bold text-xl text-slate-900 mb-2">Investigador Asociado</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Crea proyectos de investigación, registra productos académicos y gestiona evidencias para validación.</p>
                    </div>
                </div>

                <!-- Fila 2: 2 tarjetas centradas -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-3xl mx-auto">
                    
                    <!-- TARJETA 4 — Director de Semilleros -->
                    <div class="glass-card rounded-2xl p-6 md:p-8 role-card-hidden transition-transform duration-300 hover:scale-[1.03]" data-role-card>
                        <div class="w-14 h-14 rounded-xl bg-[#3b82f6]/10 flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253M12 3a8.997 8.997 0 00-7.843 4.582" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 13.5V21m0-7.5l-3 3m3-3l3 3" />
                            </svg>
                        </div>
                        <h3 class="font-heading font-bold text-xl text-slate-900 mb-2">Director de Semilleros</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Crea y supervisa todos los semilleros del centro, asigna líderes y monitorea el avance de cada grupo.</p>
                    </div>

                    <!-- TARJETA 5 — Asesor de Semillero -->
                    <div class="glass-card rounded-2xl p-6 md:p-8 role-card-hidden transition-transform duration-300 hover:scale-[1.03]" data-role-card>
                        <div class="w-14 h-14 rounded-xl bg-[#39A900]/10 flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                            </svg>
                        </div>
                        <h3 class="font-heading font-bold text-xl text-slate-900 mb-2">Asesor de Semillero</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Registra aprendices, crea proyectos del semillero, vincula autores y documenta los productos generados.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ════════════════════════════════════════════════════════════════════════ -->
        <!--  SECCIÓN: CÓMO FUNCIONA (LÍNEA DE TIEMPO)                              -->
        <!-- ════════════════════════════════════════════════════════════════════════ -->
        <section id="workflow-section" class="relative bg-[#f8fafc] py-16 md:py-24 overflow-hidden">
            <!-- Glow decorativo sutil -->
            <div class="absolute top-0 right-0 w-[30%] h-[30%] glow-emerald pointer-events-none opacity-50"></div>

            <div class="max-w-7xl mx-auto px-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20">
                    
                    <!-- P2 — Columna izquierda: sticky solo en desktop, pb-8 en móvil -->
                    <div class="pb-8 lg:pb-0 lg:sticky lg:top-32 lg:self-start">
                        <span class="inline-block text-[#39A900] uppercase tracking-widest text-xs font-semibold mb-3">FLUJO DE TRABAJO</span>
                        <h2 class="font-heading font-black text-3xl md:text-4xl text-slate-900 max-w-sm mb-5 leading-tight">Del registro a la validación, todo trazado</h2>
                        <p class="text-slate-500 text-base md:text-lg mb-8 max-w-md">
                            Cada proyecto y producto sigue un camino claro desde su creación hasta su aprobación institucional.
                        </p>
                        @guest
                        <a href="{{ route('login') }}" class="btn-secondary inline-flex items-center gap-2 px-6 py-3 rounded-lg text-sm font-semibold text-slate-700 cursor-pointer">
                            Ingresar al Sistema
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        @else
                        <a href="{{ url('/dashboard') }}" class="btn-secondary inline-flex items-center gap-2 px-6 py-3 rounded-lg text-sm font-semibold text-slate-700">
                            Panel de Control
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        @endguest
                    </div>

                    <!-- Columna derecha — Línea de tiempo -->
                    <div class="relative" id="timeline-container">
                        <!-- Línea vertical de fondo (estática) -->
                        <div class="absolute left-[21px] top-0 bottom-0 w-[3px] bg-slate-200 rounded-full"></div>
                        <!-- Línea vertical animada (crece con scroll) -->
                        <div id="timeline-fill" class="absolute left-[21px] top-0 timeline-line-fill rounded-full" style="height: 0;"></div>

                        <!-- PASO 1 -->
                        <div class="relative flex gap-6 mb-12 timeline-step-hidden" data-timeline-step>
                            <div class="timeline-circle" style="background: #39A900;">1</div>
                            <div class="pt-2">
                                <h3 class="font-heading font-bold text-lg text-slate-900 mb-1">El Admin configura el sistema</h3>
                                <p class="text-slate-500 text-sm leading-relaxed">Carga catálogos, crea centros de formación y registra a los directores del centro.</p>
                            </div>
                        </div>

                        <!-- PASO 2 -->
                        <div class="relative flex gap-6 mb-12 timeline-step-hidden" data-timeline-step>
                            <div class="timeline-circle" style="background: #3b82f6;">2</div>
                            <div class="pt-2">
                                <h3 class="font-heading font-bold text-lg text-slate-900 mb-1">Se organizan grupos y semilleros</h3>
                                <p class="text-slate-500 text-sm leading-relaxed">Los directores crean sus grupos, vinculan investigadores y conforman los semilleros activos.</p>
                            </div>
                        </div>

                        <!-- PASO 3 -->
                        <div class="relative flex gap-6 mb-12 timeline-step-hidden" data-timeline-step>
                            <div class="timeline-circle" style="background: #39A900;">3</div>
                            <div class="pt-2">
                                <h3 class="font-heading font-bold text-lg text-slate-900 mb-1">Se crean proyectos de investigación</h3>
                                <p class="text-slate-500 text-sm leading-relaxed">Investigadores y asesores registran proyectos, vinculan autores y documentan el trabajo académico.</p>
                            </div>
                        </div>

                        <!-- PASO 4 -->
                        <div class="relative flex gap-6 mb-12 timeline-step-hidden" data-timeline-step>
                            <div class="timeline-circle" style="background: #3b82f6;">4</div>
                            <div class="pt-2">
                                <h3 class="font-heading font-bold text-lg text-slate-900 mb-1">Se registran productos académicos</h3>
                                <p class="text-slate-500 text-sm leading-relaxed">Cada proyecto genera productos con tipología Minciencias, área de conocimiento y evidencias adjuntas.</p>
                            </div>
                        </div>

                        <!-- PASO 5 -->
                        <div class="relative flex gap-6 timeline-step-hidden" data-timeline-step>
                            <div class="timeline-circle" style="background: #39A900;">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                </svg>
                            </div>
                            <div class="pt-2">
                                <h3 class="font-heading font-bold text-lg text-slate-900 mb-1">Revisión y aprobación</h3>
                                <p class="text-slate-500 text-sm leading-relaxed">El director revisa cada producto y lo aprueba o rechaza con observaciones. Todo queda trazado.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="py-12 border-t border-slate-200">
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6 text-center md:text-left">
                <div class="flex items-center gap-2">
                    <img src="{{ asset('images/sena-logo.png') }}" alt="SIGESI" class="w-6 h-6 rounded shadow-sm opacity-80 mix-blend-multiply">
                    <span class="font-heading font-bold text-sm tracking-tight text-slate-900"><span class="text-[#39A900]">SIGESI</span></span>
                    <span class="text-slate-400 mx-2">|</span>
                    <span class="text-xs text-slate-500 font-medium uppercase tracking-tight">Public Facing Institutional Portal</span>
                </div>
                
                <div class="text-xs text-slate-400">
                    &copy; {{ date('Y') }} SIGESI — Sistema de Gestión de Grupos y Semilleros de Investigación. Todos los derechos reservados.
                </div>
            </div>
        </footer>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const DEBUG_MODE = false;
            const IS_MOBILE  = window.innerWidth < 768;

            const log   = (...args) => { if (DEBUG_MODE) console.log(...args); };
            const warn  = (...args) => { if (DEBUG_MODE) console.warn(...args); };
            const error = (...args) => { if (DEBUG_MODE) console.error(...args); };

            const video           = document.getElementById('hero-video');
            const heroSection     = document.getElementById('hero-section');
            const scrollIndicator = document.getElementById('scroll-indicator');
            const auroraFallback  = document.getElementById('hero-aurora-fallback');

            if (IS_MOBILE || !video || !heroSection) {
                if (scrollIndicator && heroSection) {
                    window.addEventListener('scroll', () => {
                        const fraction = heroSection.offsetHeight > 0
                            ? Math.min(window.scrollY / (heroSection.offsetHeight - window.innerHeight), 1)
                            : 0;
                        scrollIndicator.style.opacity = fraction > 0.05 ? '0' : '1';
                        scrollIndicator.style.pointerEvents = fraction > 0.05 ? 'none' : 'auto';
                    }, { passive: true });
                }
            } else {
                let seekFailed    = false;
                let seekChecks    = 0;
                let scrollLogs    = 0;
                let currentTarget = 0;
                let lerpAnimId    = null;
                let lastSeekTime  = 0;
                let seekDurations = [];
                let minInterval   = 32;
                let rawTarget     = 0;

                const activateFallback = () => {
                    video.style.display = 'none';
                    if (auroraFallback) {
                        auroraFallback.classList.remove('md:hidden');
                        auroraFallback.classList.add('block');
                    }
                };

                const lerpLoop = () => {
                    if (seekFailed || video.readyState < 1) {
                        lerpAnimId = requestAnimationFrame(lerpLoop);
                        return;
                    }
                    const diff = rawTarget - currentTarget;
                    if (Math.abs(diff) > 0.001) {
                        currentTarget += diff * 0.15;
                        const now = performance.now();
                        if (now - lastSeekTime >= minInterval) {
                            const seekStart = now;
                            video.currentTime = currentTarget;
                            lastSeekTime = now;
                            if (seekDurations.length < 20) {
                                requestAnimationFrame(() => {
                                    const seekEnd = performance.now();
                                    seekDurations.push(seekEnd - seekStart);
                                    if (seekDurations.length === 10) {
                                        const avg = seekDurations.reduce((a, b) => a + b, 0) / seekDurations.length;
                                        if (avg > 50) minInterval = 64;
                                    }
                                });
                            }
                        }
                    }
                    lerpAnimId = requestAnimationFrame(lerpLoop);
                };

                const updateVideoTime = () => {
                    if (video.readyState < 1 || seekFailed) return;
                    const scrollTop      = window.scrollY;
                    const heroOffsetTop  = heroSection.offsetTop;
                    const relativeScroll = Math.max(0, scrollTop - heroOffsetTop);
                    const maxScroll      = heroSection.offsetHeight - window.innerHeight;
                    const scrollFraction = maxScroll > 0 ? Math.min(relativeScroll / maxScroll, 1) : 0;
                    rawTarget = video.duration * scrollFraction;
                    if (scrollFraction > 0.05 && seekChecks < 3) {
                        seekChecks++;
                        setTimeout(() => {
                            if (seekChecks >= 3 && Math.abs(video.currentTime) < 0.01 && rawTarget > 0.1) {
                                seekFailed = true;
                                if (lerpAnimId) cancelAnimationFrame(lerpAnimId);
                                activateFallback();
                            }
                        }, 300);
                    }
                    if (scrollIndicator) {
                        scrollIndicator.style.opacity       = scrollFraction > 0.05 ? '0' : '1';
                        scrollIndicator.style.pointerEvents = scrollFraction > 0.05 ? 'none' : 'auto';
                    }
                };

                const onScroll = () => { updateVideoTime(); };

                const setupScrollVideo = () => {
                    video.pause();
                    if (video.seekable.length === 0) {
                        video.addEventListener('progress', function onProgress() {
                            if (video.seekable.length > 0) {
                                video.removeEventListener('progress', onProgress);
                                finishSetup();
                            }
                        });
                        setTimeout(() => {
                            if (video.seekable.length === 0 && !seekFailed) {
                                seekFailed = true;
                                activateFallback();
                            }
                        }, 3000);
                        return;
                    }
                    finishSetup();
                };

                const finishSetup = () => {
                    video.currentTime = 0.001;
                    window.addEventListener('scroll', onScroll, { passive: true });
                    lerpAnimId = requestAnimationFrame(lerpLoop);
                    updateVideoTime();
                };

                const MP4_URL = '{{ asset("images/hero-v2.mp4") }}';

                fetch(MP4_URL)
                    .then(response => {
                        if (!response.ok) throw new Error(`HTTP ${response.status}`);
                        return response.blob();
                    })
                    .then(blob => {
                        const blobUrl = URL.createObjectURL(blob);
                        while (video.firstChild) video.removeChild(video.firstChild);
                        video.src = blobUrl;
                        video.load();
                        const onMeta = () => { setupScrollVideo(); };
                        if (video.readyState >= 1) onMeta();
                        else video.addEventListener('loadedmetadata', onMeta, { once: true });
                    })
                    .catch(() => {
                        video.load();
                        if (video.readyState >= 1) setupScrollVideo();
                        else video.addEventListener('loadedmetadata', setupScrollVideo, { once: true });
                    });

                setTimeout(() => {
                    if (video.readyState < 1 && !seekFailed) {
                        seekFailed = true;
                        activateFallback();
                    }
                }, 10000);

                document.addEventListener('touchstart', () => { video.load(); }, { once: true });
                window.addEventListener('beforeunload', () => {
                    window.removeEventListener('scroll', onScroll);
                    if (lerpAnimId) cancelAnimationFrame(lerpAnimId);
                    if (video.src && video.src.startsWith('blob:')) URL.revokeObjectURL(video.src);
                });
            }

            // ROLES CARDS
            const roleCards = document.querySelectorAll('[data-role-card]');
            if (roleCards.length) {
                const roleObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.remove('role-card-hidden');
                            entry.target.classList.add('animate-softReveal');
                            roleObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.05 });
                roleCards.forEach((card, index) => {
                    card.style.animationDelay = `${(index + 1) * 60}ms`;
                    roleObserver.observe(card);
                });
            }

            // TIMELINE STEPS
            const timelineSteps = document.querySelectorAll('[data-timeline-step]');
            if (timelineSteps.length) {
                const stepObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.remove('timeline-step-hidden');
                            entry.target.classList.add('timeline-step-visible');
                            stepObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.08 });
                timelineSteps.forEach((step, index) => {
                    step.style.animationDelay = `${(index + 1) * 60}ms`;
                    stepObserver.observe(step);
                });
            }

            // TIMELINE LINE FILL
            const timelineFill = document.getElementById('timeline-fill');
            const timelineContainer = document.getElementById('timeline-container');
            if (timelineFill && timelineContainer) {
                let tlTicking = false;
                const updateTimelineFill = () => {
                    const rect = timelineContainer.getBoundingClientRect();
                    const scrolledPast = window.innerHeight - rect.top;
                    const fraction = Math.max(0, Math.min(scrolledPast / rect.height, 1));
                    timelineFill.style.height = (fraction * rect.height) + 'px';
                };
                window.addEventListener('scroll', () => {
                    if (!tlTicking) {
                        requestAnimationFrame(() => {
                            updateTimelineFill();
                            tlTicking = false;
                        });
                        tlTicking = true;
                    }
                }, { passive: true });
                updateTimelineFill();
            }
        });
        </script>
    </body>
</html>