<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Red Nacional de Investigación Académica y Semilleros</title>
        
        <!-- Fonts: Inter & Outfit for a modern institutional look -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@500;600;700;900&display=swap" rel="stylesheet">

        <!-- TailwindCSS for layout, but custom CSS for the design system -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <style>
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

            /* Animations */
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }

            @keyframes float {
                0% { transform: translateY(0px); }
                50% { transform: translateY(-10px); }
                100% { transform: translateY(0px); }
            }

            .animate-fadeInUp {
                animation: fadeInUp 0.8s ease-out forwards;
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

            /* ─── Roles section: pre-animation state ──────────────────────────────── */
            .role-card-hidden {
                opacity: 0;
                transform: translateY(30px);
            }

            /* ─── Timeline section ────────────────────────────────────────────────── */
            .timeline-line-fill {
                width: 3px;
                background: linear-gradient(to bottom, #39A900, #3b82f6, #39A900, #3b82f6, #39A900);
                transition: height 0.1s linear;
            }

            .timeline-step-hidden {
                opacity: 0;
                transform: translateY(30px);
            }

            .timeline-step-visible {
                animation: fadeInUp 0.6s ease-out forwards;
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
        </style>
    </head>
    <body class="antialiased selection:bg-emerald-100 relative">
        <!-- 
          ========== DOCUMENTACIÓN DEL DISEÑO Y RESTRICCIONES ==========
          - SECCIONES ACTUALES:
            1. Navbar (.glass-nav): Contiene la navegación principal e integración con Livewire/Blade auth. NO MODIFICAR lógicas.
            2. Hero (#hero-section): Sección sticky interactiva y scroll-driven (200vh).
            3. Roles del sistema: Tarjetas de roles con animación fadeInUp.
            4. Cómo funciona: Línea de tiempo vertical con pasos.
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
        <!-- Glow Ornaments -->
        <div class="fixed top-[-10%] left-[-5%] w-[40%] h-[40%] glow-emerald pointer-events-none"></div>
        <div class="fixed bottom-[10%] right-[-5%] w-[40%] h-[40%] glow-blue pointer-events-none"></div>

        <nav class="glass-nav fixed top-0 w-full z-50 py-4">
            <div class="max-w-7xl mx-auto px-6 flex items-center justify-between">
                <div class="flex items-center gap-2 text-decoration-none">
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-blue-500 flex items-center justify-center text-white shadow-lg">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.644.322a6 6 0 01-3.86.517l-2.387-.477a2 2 0 00-1.022.547l1.166 1.166a2 2 0 001.414.586h8.428a2 2 0 001.414-.586l1.166-1.166zM12 14V4a1 1 0 00-1-1H9a1 1 0 00-1 1v10m4 0s.5-2 3-2 3 2 3 2v-4a1 1 0 00-1-1h-2a1 1 0 00-1 1v4" />
                        </svg>
                    </div>
                    <span class="font-heading font-bold text-lg tracking-tight text-slate-900">RED <span class="text-emerald-600">INVESTIGACIÓN</span></span>
                </div>
                
                <div class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-600">
                    <a href="#" class="hover:text-emerald-600 transition-colors">Convocatorias</a>
                    <a href="#" class="hover:text-emerald-600 transition-colors">Semilleros</a>
                    <a href="#" class="hover:text-emerald-600 transition-colors">Repositorio</a>
                    <a href="#" class="hover:text-emerald-600 transition-colors">Nosotros</a>
                </div>

                <div class="flex gap-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primary text-white px-5 py-2 rounded-lg text-sm font-semibold">Panel de Control</a>
                        @else
                            <a href="{{ route('login') }}" class="text-slate-600 hover:text-slate-900 px-4 py-2 text-sm font-medium transition-colors">Iniciar Sesión</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-primary text-white px-5 py-2 rounded-lg text-sm font-semibold shadow-sm">Registrarse</a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </nav>

        <section id="hero-section" style="height: 200vh;" class="relative overflow-visible">
            <!-- Video fijo en background y contenedor pegajoso -->
            <div class="sticky top-0 h-screen overflow-hidden">
                <!-- Fallback aurora: visible en móvil por defecto, JS lo activa en desktop si video no seekable -->
                <div id="hero-aurora-fallback" class="block md:hidden absolute inset-0 w-full h-full hero-aurora-bg"></div>
                
                <!-- Video para Desktop/Tablet con object-cover. Controlado sólo por scroll -->
                <video id="hero-video" class="hidden md:block absolute inset-0 w-full h-full object-cover" 
                       muted playsinline preload="auto">
                    <source src="{{ asset('images/hero-v2.mp4') }}" type="video/mp4">
                    <source src="{{ asset('images/hero-scrubbing.webm') }}" type="video/webm">
                </video>
                
                <!-- Overlay Gradient Oscuro -->
                <div class="absolute inset-0 bg-gradient-to-b from-[#0a1628]/80 via-[#0a1628]/60 to-[#0a1628]/40"></div>
                
                <!-- Contenido -->
                <div class="relative z-10 flex flex-col items-center justify-center h-full text-center px-6">
                    <h1 class="text-5xl md:text-7xl font-heading font-black leading-tight text-white animate-fadeInUp delay-100 max-w-4xl mx-auto drop-shadow-lg">
                        Centraliza la Investigación <span class="text-[#39A900]">SENA</span>
                    </h1>
                    
                    <p class="text-xl md:text-2xl text-white/70 font-light mt-6 max-w-2xl mx-auto animate-fadeInUp delay-200">
                        Gestiona grupos de investigación, semilleros, proyectos y productos académicos desde una sola plataforma institucional.
                    </p>
                    
                    <div class="flex flex-col sm:flex-row gap-4 mt-10 animate-fadeInUp delay-300">
                        <!-- CTA Login como se solicitó, conservando route('login') -->
                        <a href="{{ route('login') }}" class="bg-[#39A900] hover:bg-[#2d8500] text-white px-8 py-4 rounded-full font-bold text-base shadow-lg transition-transform hover:scale-105 flex items-center justify-center gap-2">
                            Iniciar Sesión
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Indicador de scroll down animado -->
                <div id="scroll-indicator" class="absolute bottom-10 left-1/2 transform -translate-x-1/2 flex flex-col items-center gap-2 text-white/70 animate-bounce transition-opacity duration-300">
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
                <div class="text-center mb-16">
                    <span class="inline-block text-[#39A900] uppercase tracking-widest text-xs font-semibold mb-3">ROLES DEL SISTEMA</span>
                    <h2 class="font-heading font-black text-4xl md:text-5xl text-slate-900 mb-4">Un espacio para cada actor</h2>
                    <p class="text-slate-500 text-lg max-w-2xl mx-auto">
                        Desde el administrador hasta el asesor, cada rol tiene acceso exacto a lo que necesita.
                    </p>
                </div>

                <!-- Fila 1: 3 tarjetas -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    
                    <!-- TARJETA 1 — Administrador -->
                    <div class="glass-card rounded-2xl p-8 role-card-hidden transition-transform duration-300 hover:scale-105" data-role-card>
                        <div class="w-14 h-14 rounded-xl bg-[#39A900]/10 flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-[#39A900]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                            </svg>
                        </div>
                        <h3 class="font-heading font-bold text-xl text-slate-900 mb-2">Administrador</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Configura el sistema, gestiona catálogos y crea los usuarios de nivel superior del centro de formación.</p>
                    </div>

                    <!-- TARJETA 2 — Director de Investigación -->
                    <div class="glass-card rounded-2xl p-8 role-card-hidden transition-transform duration-300 hover:scale-105" data-role-card>
                        <div class="w-14 h-14 rounded-xl bg-[#3b82f6]/10 flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-[#3b82f6]" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <h3 class="font-heading font-bold text-xl text-slate-900 mb-2">Director de Investigación</h3>
                        <p class="text-slate-500 text-sm leading-relaxed">Supervisa el grupo, vincula investigadores, revisa productos y genera reportes de producción académica.</p>
                    </div>

                    <!-- TARJETA 3 — Investigador Asociado -->
                    <div class="glass-card rounded-2xl p-8 role-card-hidden transition-transform duration-300 hover:scale-105" data-role-card>
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
                    <div class="glass-card rounded-2xl p-8 role-card-hidden transition-transform duration-300 hover:scale-105" data-role-card>
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
                    <div class="glass-card rounded-2xl p-8 role-card-hidden transition-transform duration-300 hover:scale-105" data-role-card>
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
                    
                    <!-- Columna izquierda — sticky en desktop -->
                    <div class="lg:sticky lg:top-32 lg:self-start">
                        <span class="inline-block text-[#39A900] uppercase tracking-widest text-xs font-semibold mb-3">FLUJO DE TRABAJO</span>
                        <h2 class="font-heading font-black text-4xl text-slate-900 max-w-sm mb-5 leading-tight">Del registro a la validación, todo trazado</h2>
                        <p class="text-slate-500 text-lg mb-8 max-w-md">
                            Cada proyecto y producto sigue un camino claro desde su creación hasta su aprobación institucional.
                        </p>
                        <a href="{{ route('login') }}" class="btn-secondary inline-flex items-center gap-2 px-6 py-3 rounded-lg text-sm font-semibold text-slate-700">
                            Iniciar sesión
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
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
            <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-2">
                    <span class="font-heading font-bold text-sm tracking-tight text-slate-900">RED <span class="text-emerald-600">INVESTIGACIÓN</span></span>
                    <span class="text-slate-400 mx-2">|</span>
                    <span class="text-xs text-slate-500 font-medium uppercase tracking-tight">Public Facing Institutional Portal</span>
                </div>
                
                <div class="text-xs text-slate-400">
                    &copy; {{ date('Y') }} Sistema de Gestión Documental y Red Nacional de Semilleros. Todos los derechos reservados.
                </div>
            </div>
        </footer>
        <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ════════════════════════════════════════════════════════════════════════
            //  SCROLL-DRIVEN VIDEO — BLOB PRELOAD STRATEGY
            //  Fetches the entire MP4 into memory so seeking works regardless of
            //  whether the server supports HTTP Range Requests.
            //  Cambiar a false en producción para silenciar toda la consola.
            // ════════════════════════════════════════════════════════════════════════
            const DEBUG_MODE = true;

            const log   = (...args) => { if (DEBUG_MODE) console.log(...args); };
            const warn  = (...args) => { if (DEBUG_MODE) console.warn(...args); };
            const error = (...args) => { if (DEBUG_MODE) console.error(...args); };

            // ─── ELEMENTOS DOM ───────────────────────────────────────────────────────
            const video           = document.getElementById('hero-video');
            const heroSection     = document.getElementById('hero-section');
            const scrollIndicator = document.getElementById('scroll-indicator');
            const auroraFallback  = document.getElementById('hero-aurora-fallback');

            if (!video || !heroSection) {
                warn('⚠️ Elementos hero-video o hero-section no encontrados. Scrubbing desactivado.');
                return;
            }

            // ─── FALLBACK: mostrar aurora en escritorio y ocultar video ──────────────
            const activateFallback = () => {
                warn('🌌 Activando fallback aurora en TODOS los breakpoints.');
                video.style.display = 'none';
                if (auroraFallback) {
                    auroraFallback.classList.remove('md:hidden');
                    auroraFallback.classList.add('block');
                }
            };

            // ─── THROTTLE CON rAF ───────────────────────────────────────────────────
            let ticking     = false;
            let seekChecks  = 0;
            let seekFailed  = false;
            let scrollLogs  = 0;

            // ─── ACTUALIZAR VIDEO SEGÚN POSICIÓN DE SCROLL ──────────────────────────
            const updateVideoTime = () => {
                if (video.readyState < 1 || seekFailed) return;

                const scrollTop      = window.scrollY;
                const heroOffsetTop  = heroSection.offsetTop;
                const relativeScroll = Math.max(0, scrollTop - heroOffsetTop);
                const maxScroll      = heroSection.offsetHeight - window.innerHeight;
                const scrollFraction = maxScroll > 0
                    ? Math.min(relativeScroll / maxScroll, 1)
                    : 0;

                const targetTime = video.duration * scrollFraction;
                video.currentTime = targetTime;

                // ─── DETECCIÓN AUTOMÁTICA DE VIDEO NO-SEEKABLE ──────────────────────
                if (scrollFraction > 0.05 && seekChecks < 3) {
                    seekChecks++;
                    setTimeout(() => {
                        const diff = Math.abs(video.currentTime - targetTime);
                        if (diff > 0.5) {
                            warn(`⚠️ Seek check ${seekChecks}/3: target=${targetTime.toFixed(2)}, actual=${video.currentTime.toFixed(2)}, diff=${diff.toFixed(2)}`);
                        }
                        if (seekChecks >= 3 && Math.abs(video.currentTime) < 0.01 && targetTime > 0.1) {
                            error('❌ Video no-seekable detectado tras 3 intentos. currentTime no cambió.');
                            seekFailed = true;
                            activateFallback();
                        }
                    }, 300);
                }

                if (scrollLogs < 5) {
                    log(`🎞 Scroll: fraction=${scrollFraction.toFixed(3)}, target=${targetTime.toFixed(3)}, actual=${video.currentTime.toFixed(3)}, readyState=${video.readyState}`);
                    scrollLogs++;
                    if (scrollLogs === 5) log('(logs de scroll pausados para no saturar la consola)');
                }

                if (scrollIndicator) {
                    scrollIndicator.style.opacity       = scrollFraction > 0.05 ? '0' : '1';
                    scrollIndicator.style.pointerEvents = scrollFraction > 0.05 ? 'none' : 'auto';
                }
            };

            // ─── HANDLER DE SCROLL CON rAF-THROTTLE ─────────────────────────────────
            const onScroll = () => {
                if (!ticking) {
                    requestAnimationFrame(() => {
                        updateVideoTime();
                        ticking = false;
                    });
                    ticking = true;
                }
            };

            // ─── SETUP: registrar scroll SOLO cuando metadata disponible ────────────
            const setupScrollVideo = () => {
                video.pause();

                log('✅ Metadata cargada');
                log('⏱ Duración total:', video.duration, 'segundos');
                log('📐 Dimensiones:', video.videoWidth, 'x', video.videoHeight);
                log('🎞 readyState:', video.readyState);

                // ─── Verificar seekable ranges ──────────────────────────────────────
                if (video.seekable.length === 0) {
                    warn('⚠️ Seekable range vacío al cargar metadata. Esperando más datos...');
                    // Dar una segunda oportunidad: esperar a que haya datos suficientes
                    video.addEventListener('progress', function onProgress() {
                        if (video.seekable.length > 0) {
                            video.removeEventListener('progress', onProgress);
                            log('📍 Seekable range (tras progress):', video.seekable.start(0).toFixed(2), '→', video.seekable.end(0).toFixed(2));
                            finishSetup();
                        }
                    });
                    // Timeout: si después de 3s sigue sin seekable, fallback
                    setTimeout(() => {
                        if (video.seekable.length === 0 && !seekFailed) {
                            error('❌ Video sigue sin seekable ranges tras esperar datos adicionales.');
                            seekFailed = true;
                            activateFallback();
                        }
                    }, 3000);
                    return;
                }

                log('📍 Seekable range:', video.seekable.start(0).toFixed(2), '→', video.seekable.end(0).toFixed(2));
                finishSetup();
            };

            const finishSetup = () => {
                // Precarga: forzar decodificación del primer frame
                video.currentTime = 0.001;

                // Test de seek automático al 50%
                setTimeout(() => {
                    const testTarget = video.duration * 0.5;
                    video.currentTime = testTarget;
                    log('🧪 Test seek al 50%: target =', testTarget.toFixed(2));
                    setTimeout(() => {
                        log('🖼 currentTime real tras seek:', video.currentTime.toFixed(2));
                        log('⚡ seekable:', video.seekable.length > 0
                            ? `${video.seekable.start(0).toFixed(2)} → ${video.seekable.end(0).toFixed(2)}`
                            : 'NO SEEKABLE ❌');
                        video.currentTime = 0;
                    }, 500);
                }, 200);

                // Registrar scroll listener
                window.addEventListener('scroll', onScroll, { passive: true });
                updateVideoTime();
            };

            // ════════════════════════════════════════════════════════════════════════
            //  BLOB PRELOAD — descargar el MP4 completo y asignar como blob URL.
            //  Esto garantiza seekability completa sin depender de Range Requests
            //  del servidor (Laragon/Apache puede no servirlos correctamente).
            // ════════════════════════════════════════════════════════════════════════
            const MP4_URL = '{{ asset("images/hero-v2.mp4") }}';
            const WEBM_URL = '{{ asset("images/hero-scrubbing.webm") }}';

            log('🔄 Iniciando descarga blob del video:', MP4_URL);

            fetch(MP4_URL)
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.blob();
                })
                .then(blob => {
                    log('✅ Blob descargado:', (blob.size / 1024 / 1024).toFixed(2), 'MB');
                    const blobUrl = URL.createObjectURL(blob);
                    
                    // Limpiar <source> tags existentes y asignar blob directamente
                    while (video.firstChild) video.removeChild(video.firstChild);
                    video.src = blobUrl;
                    video.load();

                    // Esperar metadata del blob
                    const onMeta = () => {
                        log('✅ Blob video metadata lista');
                        setupScrollVideo();
                    };
                    if (video.readyState >= 1) {
                        onMeta();
                    } else {
                        video.addEventListener('loadedmetadata', onMeta, { once: true });
                    }
                })
                .catch(err => {
                    error('❌ Fetch blob falló:', err.message, '— intentando carga directa con <source>');
                    // Fallback: cargar con los <source> tags originales (puede funcionar
                    // si el servidor está sirviendo Range headers correctamente)
                    video.load();
                    if (video.readyState >= 1) {
                        setupScrollVideo();
                    } else {
                        video.addEventListener('loadedmetadata', setupScrollVideo, { once: true });
                    }
                });

            // ─── TIMEOUT GLOBAL: si nada funciona en 10s → fallback aurora ──────────
            setTimeout(() => {
                if (video.readyState < 1 && !seekFailed) {
                    error('❌ Video no cargó metadata en 10 segundos. Activando fallback.');
                    seekFailed = true;
                    activateFallback();
                }
            }, 10000);

            // ─── FIX SAFARI / WEBKIT ────────────────────────────────────────────────
            document.addEventListener('touchstart', () => {
                video.load();
            }, { once: true });

            // ─── CLEANUP al salir de la página ──────────────────────────────────────
            window.addEventListener('beforeunload', () => {
                window.removeEventListener('scroll', onScroll);
                // Liberar blob URL si existe
                if (video.src && video.src.startsWith('blob:')) {
                    URL.revokeObjectURL(video.src);
                }
            });

            // ════════════════════════════════════════════════════════════════════════
            //  INTERSECTION OBSERVER — ROLES CARDS FADE-IN
            // ════════════════════════════════════════════════════════════════════════
            const roleCards = document.querySelectorAll('[data-role-card]');
            if (roleCards.length) {
                const roleObserver = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            entry.target.classList.remove('role-card-hidden');
                            entry.target.classList.add('animate-fadeInUp');
                            roleObserver.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.15 });

                roleCards.forEach((card, index) => {
                    // Escalonar delays: 100ms, 200ms, 300ms...
                    card.style.animationDelay = `${(index + 1) * 100}ms`;
                    roleObserver.observe(card);
                });
            }

            // ════════════════════════════════════════════════════════════════════════
            //  INTERSECTION OBSERVER — TIMELINE STEPS FADE-IN
            // ════════════════════════════════════════════════════════════════════════
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
                }, { threshold: 0.2 });

                timelineSteps.forEach((step, index) => {
                    step.style.animationDelay = `${(index + 1) * 100}ms`;
                    stepObserver.observe(step);
                });
            }

            // ════════════════════════════════════════════════════════════════════════
            //  TIMELINE LINE FILL — Progressive draw on scroll
            // ════════════════════════════════════════════════════════════════════════
            const timelineFill = document.getElementById('timeline-fill');
            const timelineContainer = document.getElementById('timeline-container');
            if (timelineFill && timelineContainer) {
                const updateTimelineFill = () => {
                    const rect = timelineContainer.getBoundingClientRect();
                    const containerTop = rect.top;
                    const containerHeight = rect.height;
                    const viewportHeight = window.innerHeight;

                    // Calcular cuánto del contenedor ya pasó por el viewport
                    const scrolledPast = viewportHeight - containerTop;
                    const fraction = Math.max(0, Math.min(scrolledPast / containerHeight, 1));
                    timelineFill.style.height = (fraction * containerHeight) + 'px';
                };

                window.addEventListener('scroll', () => {
                    requestAnimationFrame(updateTimelineFill);
                }, { passive: true });

                // Ejecutar una vez al cargar
                updateTimelineFill();
            }
        });
        </script>
    </body>
</html>
