@if(auth()->check())
    {{-- Usuario autenticado: usa el layout de la app --}}
    <x-app-layout>
        <div class="py-12">
            <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="p-8 text-center">
                        <div class="mb-6">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                                </svg>
                            </div>
                            <h1 class="text-4xl font-bold text-slate-900 mb-2">404</h1>
                            <p class="text-lg text-slate-600">Página no encontrada</p>
                        </div>

                        @if(auth()->user() && auth()->user()->hasRole('lider_proyecto'))
                            {{-- Mensaje específico para Líder de Proyecto --}}
                            <div class="mb-8 p-4 bg-amber-50 border border-amber-200 rounded-lg">
                                <p class="text-sm text-amber-800">
                                    <strong>No tienes un proyecto asignado como Líder de Proyecto.</strong><br>
                                    Contacta a tu Director de Semilleros para que te asigne uno.
                                </p>
                            </div>
                        @else
                            {{-- Mensaje genérico para otros casos de 404 --}}
                            <div class="mb-8 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                                <p class="text-sm text-slate-600">
                                    La página que buscas no está disponible o requiere permisos específicos.
                                </p>
                            </div>
                        @endif

                        <div class="space-y-3">
                            @php
                                $backUrl = route('dashboard');
                                $activeRole = \App\Support\ActiveRoleContext::current();
                                if ($activeRole) {
                                    $roleUrl = \App\Support\RoleModuleLinks::urlForRoleName($activeRole);
                                    if ($roleUrl) {
                                        $backUrl = $roleUrl;
                                    }
                                }
                            @endphp

                            <a href="{{ $backUrl }}"
                               class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#39A900] text-white font-semibold rounded-lg hover:bg-[#2d8500] transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18"/>
                                </svg>
                                Volver al dashboard
                            </a>

                            @if(auth()->user() && auth()->user()->roles->count() > 1)
                                <p class="text-xs text-slate-500 pt-2">
                                    O selecciona otro rol desde el menú principal
                                </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </x-app-layout>
@else
    {{-- Usuario no autenticado: usa un layout simple sin la app --}}
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>404 — SIGESI</title>
        <link rel="icon" href="/favicon.ico?v=2" sizes="any">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css'])
        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>
    </head>
    <body class="bg-gradient-to-br from-slate-50 to-slate-100 min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full mx-4">
            <div class="bg-white rounded-xl border border-slate-200 shadow-lg overflow-hidden">
                <div class="p-8 text-center">
                    <div class="mb-6">
                        <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                            </svg>
                        </div>
                        <h1 class="text-4xl font-bold text-slate-900 mb-2">404</h1>
                        <p class="text-lg text-slate-600">Página no encontrada</p>
                    </div>

                    <p class="text-sm text-slate-600 mb-8">
                        La página que buscas no está disponible. Por favor, inicia sesión nuevamente.
                    </p>

                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 px-6 py-2.5 bg-[#39A900] text-white font-semibold rounded-lg hover:bg-[#2d8500] transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H15m-4 0V3m0 10h4m-4 0a4 4 0 11-8 0"/>
                        </svg>
                        Ir al login
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
@endif
