<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'SIGESI') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|outfit:500,600,700,900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Custom Styles -->
    <style>
        :root {
            --verdesena-base: #39A900;
            --azul-oscuro: #0a1628;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #0f172a;
        }
        h1, h2, h3, .font-heading {
            font-family: 'Outfit', sans-serif;
        }
        .bg-azul-sena {
            background-color: var(--azul-oscuro);
        }
        .btn-sgd {
            background-color: var(--verdesena-base);
            transition: all 0.3s ease;
        }
        .btn-sgd:hover {
            background-color: #2d8500;
        }
        .text-verdesena {
            color: var(--verdesena-base);
        }
        /* Custom scrollbar for better look */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="antialiased min-h-screen flex selection:bg-green-100 selection:text-green-900">

    <!-- Panel izquierdo: Branding e imagen lateral -->
    <div class="hidden lg:flex flex-col lg:w-1/2 min-h-screen
                overflow-hidden relative bg-[#0a1628]">

        <!-- Imagen de fondo fullbleed -->
        <img src="{{ asset('images/login.png') }}"
             alt="SIGESI Banner"
             class="absolute inset-0 w-full h-full object-cover object-center">

        <!-- Overlay degradado para legibilidad -->
        <div class="absolute inset-0 bg-gradient-to-t
                    from-[#0a1628] via-[#0a1628]/80 to-[#0a1628]/40">
        </div>

        <!-- Contenido sobre el overlay -->
        <div class="relative z-10 flex flex-col h-full p-10">

            <!-- Logo arriba -->
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/sena-logo.png') }}"
                     alt="SIGESI" class="w-10 h-10">
                <div>
                    <p class="text-white font-semibold text-sm leading-tight">
                        SIGESI
                    </p>
                    <p class="text-[#39A900] font-semibold text-sm leading-tight">
                        Grupos y Semilleros de Investigación
                    </p>
                </div>
            </div>

            <div class="flex-1"></div>

            <!-- Texto abajo -->
            <div>
                <h2 class="text-white font-bold text-2xl leading-snug mb-3">
                    Gestión documental de investigación para el SENA
                </h2>
                <p class="text-white/60 text-sm leading-relaxed mb-6">
                    Centraliza grupos, semilleros, proyectos y productos
                    académicos en una sola plataforma institucional.
                </p>
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full bg-[#39A900]"></div>
                    <span class="text-white/40 text-xs">
                        SENA Colombia — Plataforma oficial
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel derecho: Formulario interactivo (auth) -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-12 relative overflow-y-auto">
        <!-- Logo móvil solo visible en responsive (< lg) -->
        <a href="{{ url('/') }}" class="absolute top-8 left-6 sm:left-12 lg:hidden inline-flex items-center gap-2 decoration-transparent">
            <img src="{{ asset('images/sena-logo.png') }}" alt="SIGESI" class="w-8 h-8 rounded-lg shadow-sm">
            <span class="font-heading font-bold text-lg tracking-tight text-slate-900"><span class="text-[#39A900]">SIGESI</span></span>
        </a>

        <!-- Contenedor del formulario (slot) -->
        <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            {{ $slot }}
        </div>
    </div>
</body>
</html>
