<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SIGESI — Sistema de Gestión de Grupos y Semilleros de Investigación</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700|outfit:700,900&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    <style>
        [x-cloak] { display: none !important; }
        body { font-family: 'Inter', sans-serif; }
        h1, h2, h3, .font-heading { font-family: 'Outfit', sans-serif; }
        .btn-sgd {
            background: #39A900;
            transition: background 0.2s ease;
        }
        .btn-sgd:hover { background: #2d8500; }
        input:focus, select:focus {
            outline: none;
            border-color: #39A900 !important;
            box-shadow: 0 0 0 3px rgba(57, 169, 0, 0.12);
        }
    </style>
</head>
<body class="bg-[#f8fafc] min-h-screen flex items-center justify-center p-4">

    <!-- Panel izquierdo: branding (visible solo en desktop) -->
    <div class="hidden lg:flex flex-col justify-between w-[420px] min-h-[600px]
                bg-[#0a1628] rounded-2xl p-10 mr-0 rounded-r-none">
        <!-- Logo -->
        <div class="flex items-center gap-3">
            <img src="{{ asset('images/sena-logo.png') }}" alt="SIGESI"
                 class="w-10 h-10 rounded-xl">
            <div>
                <p class="text-white font-semibold text-sm leading-tight">
                    SIGESI
                </p>
                <p class="text-[#39A900] font-semibold text-sm leading-tight">
                    Grupos y Semilleros de Investigación
                </p>
            </div>
        </div>

        <!-- Tagline central -->
        <div>
            <h2 class="text-white font-heading text-3xl font-bold leading-snug mb-4">
                Gestión documental de investigación para el SENA
            </h2>
            <p class="text-white/50 text-sm leading-relaxed">
                Centraliza grupos, semilleros, proyectos y productos
                académicos en una sola plataforma institucional.
            </p>
        </div>

        <!-- Badge institucional -->
        <div class="flex items-center gap-2">
            <div class="w-2 h-2 rounded-full bg-[#39A900]"></div>
            <span class="text-white/40 text-xs">SENA Colombia — Plataforma oficial</span>
        </div>
    </div>

    <!-- Panel derecho: formulario -->
    <div class="w-full max-w-md bg-white rounded-2xl lg:rounded-l-none
                shadow-xl p-8 lg:p-10 min-h-[600px] flex flex-col justify-center">
        <!-- Logo móvil (solo en mobile) -->
        <div class="flex lg:hidden items-center gap-2 mb-8">
            <img src="{{ asset('images/sena-logo.png') }}" alt="SIGESI"
                 class="w-9 h-9 rounded-lg">
            <span class="font-semibold text-slate-800 text-sm">
                SIGESI
            </span>
        </div>

        {{ $slot }}
    </div>

    @livewireScripts
</body>
</html>
