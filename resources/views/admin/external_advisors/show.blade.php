<x-app-layout>
    <x-slot name="header">Asesor Externo</x-slot>

    <nav class="flex mb-4 text-sm text-slate-500" aria-label="Breadcrumb">
        <a href="{{ route('dashboard') }}" class="hover:text-slate-700">Administración</a>
        <span class="mx-2">/</span>
        <a href="{{ route('admin.external-advisors.index') }}" class="hover:text-slate-700">Asesores Externos</a>
        <span class="mx-2">/</span>
        <span class="text-slate-900 font-medium">{{ $externalAdvisor->nombre_completo }}</span>
    </nav>

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-slate-900">{{ $externalAdvisor->nombre_completo }}</h2>
            <p class="text-sm text-slate-500 mt-1">
                @if($externalAdvisor->user_id)
                    <span class="text-green-600 font-medium">Tiene cuenta en el sistema</span>
                @else
                    Sin cuenta en el sistema
                @endif
            </p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.external-advisors.edit', $externalAdvisor) }}" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Editar</a>
            <a href="{{ route('admin.external-advisors.index') }}" class="px-4 py-2.5 rounded-lg border border-slate-200 bg-white text-slate-700 text-sm font-medium hover:bg-slate-50">Volver</a>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 overflow-hidden max-w-2xl">
        <div class="px-5 py-4 border-b border-slate-100 bg-slate-50/50">
            <h3 class="text-sm font-semibold text-slate-900">Datos del asesor</h3>
        </div>
        <dl class="p-5 space-y-4">
            <div>
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Nombre completo</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $externalAdvisor->nombre_completo }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Email</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $externalAdvisor->email ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Teléfono</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $externalAdvisor->telefono ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Institución</dt>
                <dd class="mt-1 text-sm text-slate-900">{{ $externalAdvisor->institucion ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium text-slate-500 uppercase tracking-wide">Cuenta SGD</dt>
                <dd class="mt-1 text-sm">
                    @if($externalAdvisor->user_id)
                        <span class="text-green-600 font-medium">Tiene cuenta</span>
                        @if($externalAdvisor->user)
                            — {{ $externalAdvisor->user->email }}
                        @endif
                    @else
                        Sin cuenta
                    @endif
                </dd>
            </div>
        </dl>
    </div>
</x-app-layout>
