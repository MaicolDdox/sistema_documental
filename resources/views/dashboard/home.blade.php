@php
    // Redirección de seguridad: si el rol ACTIVO tiene módulo propio, enviar
    // ahí (por si falló middleware/caché). BUG-20260813-056: antes usaba
    // hasRole() (cualquier rol asignado), el mismo patrón que ya causó 403
    // en otros puntos del sistema para usuarios multi-rol — se alinea al
    // rol activo de sesión.
    if (Auth::check()) {
        $u = Auth::user();
        $activo = \App\Support\ActiveRoleContext::current();
        if ($activo === 'lider_semillero') {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(redirect()->to('/lider-semillero', 302));
        }
        if ($activo === 'director_semilleros') {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(redirect()->to('/director-semilleros', 302));
        }
    }
@endphp
<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">
            Bienvenido, {{ Auth::user()->person?->nombre_completo ?? Auth::user()->email ?? 'Usuario' }}
        </h2>
        <p class="text-sm text-slate-500 mt-0.5">
            Use el menú lateral para acceder a los módulos disponibles.
        </p>
    </div>
</x-app-layout>
