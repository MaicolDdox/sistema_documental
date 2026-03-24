<x-app-layout>
    <x-slot name="header">Super administrador</x-slot>

    <div class="mb-6 rounded-xl border border-amber-200/80 bg-gradient-to-r from-amber-50 via-white to-slate-50 px-4 py-3 sm:px-5 shadow-sm">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-amber-800/90">Vista global de la instancia</p>
                <h2 class="text-xl font-semibold text-slate-900 mt-0.5">Panel super administrador</h2>
                <p class="text-sm text-slate-600 mt-1 max-w-2xl">
                    Métricas y actividad de <span class="font-medium text-slate-800">todos los centros de formación</span>.
                    Para dar de alta un <strong>administrador del sistema</strong>, úsalo en <em>Gestión de usuarios</em> (<code class="text-xs bg-slate-100 px-1 rounded">/admin/usuarios</code>) con rol
                    <code class="text-xs bg-slate-100 px-1 rounded">administrador_sistema</code>.
                    Puedes asignar el <strong>centro</strong> al crearlo o dejarlo sin centro y vincularlo en <strong>Centro ↔ administrador</strong>;
                    con centro asignado solo verá datos de ese centro.
                </p>
            </div>
            <span class="inline-flex items-center gap-1.5 self-start rounded-lg bg-slate-900 px-3 py-1.5 text-xs font-medium text-white shrink-0">
                <svg class="h-4 w-4 text-amber-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z"/>
                </svg>
                Rol: super_administrador
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4 mb-8">
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Usuarios (todos los centros)</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalUsuarios }}</p>
            <p class="text-xs text-slate-500 mt-1 border-b-2 border-amber-500/70 pb-0.5 w-fit">+{{ $usuariosEsteMes }} este mes</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Centros de formación</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalCentrosActivos }}<span class="text-base font-normal text-slate-400">/{{ $totalCentros }}</span></p>
            <p class="text-xs text-slate-500 mt-1">Activos / total registrados</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Grupos de investigación</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalGrupos }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $gruposActivos }} activo(s)</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Semilleros</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalSemilleros }}</p>
            <p class="text-xs text-slate-500 mt-1">+{{ $semillerosEsteSemestre }} este semestre</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Proyectos</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalProyectos }}</p>
            <p class="text-xs text-slate-500 mt-1">Registrados en el sistema</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="sgd-table-card bg-white overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-amber-50/40 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Usuarios recientes</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Últimos registros en cualquier centro</p>
                    </div>
                    <a href="{{ route('admin.usuarios.index') }}" wire:navigate class="text-sm font-medium text-[#39A900] hover:text-[#2d8500] transition-colors">Gestionar usuarios</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Usuario</th>
                                <th class="text-left">Centro</th>
                                <th class="text-left">Documento</th>
                                <th class="text-left">Rol</th>
                                <th class="text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentUsers as $user)
                            <tr>
                                <td class="px-4 py-2.5">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-200 text-xs font-medium text-slate-700">{{ $user->initials() }}</span>
                                        <span class="font-medium text-slate-900">{{ $user->person?->nombre_completo ?? $user->email }}</span>
                                    </div>
                                </td>
                                <td class="px-4 py-2.5 text-slate-600 max-w-[10rem] truncate" title="{{ $user->trainingCenter?->nombre }}">{{ $user->trainingCenter?->nombre ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">
                                    @php
                                        $docVal = $user->tipo_documento?->value ?? '';
                                        $docPrefix = 'DOC';
                                        if (str_contains($docVal, 'cedula') && str_contains($docVal, 'ciudadana')) { $docPrefix = 'CC'; }
                                        elseif (str_contains($docVal, 'pasaporte')) { $docPrefix = 'PA'; }
                                        elseif (str_contains($docVal, 'extrangera')) { $docPrefix = 'CE'; }
                                        elseif (str_contains($docVal, 'identidad')) { $docPrefix = 'DI'; }
                                    @endphp
                                    {{ $docPrefix }} {{ $user->numero_documento }}
                                </td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $user->roles->first()?->name ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    @if($user->estado && $user->estado->value === 'activo')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo</span>
                                    @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500 text-sm">No hay usuarios recientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="sgd-table-card bg-white overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-amber-50/40 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Grupos de investigación recientes</h3>
                    @if(\Illuminate\Support\Facades\Route::has('admin.research-groups.index'))
                    <a href="{{ route('admin.research-groups.index') }}" class="text-sm font-medium text-[#39A900] hover:text-[#2d8500]">Ver catálogo</a>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Nombre</th>
                                <th class="text-left">Código</th>
                                <th class="text-left">Centro</th>
                                <th class="text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentResearchGroups as $group)
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-slate-900">{{ $group->nombre }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $group->codigo ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $group->trainingCenter?->nombre ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    @if($group->estado && $group->estado->value === 'activo')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo</span>
                                    @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500 text-sm">No hay grupos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="sgd-card bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-amber-50/40">
                    <h3 class="text-sm font-semibold text-slate-900">Accesos rápidos</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Administración y datos paramétricos</p>
                </div>
                <div class="p-4 grid grid-cols-1 gap-2">
                    <a href="{{ route('super-admin.centros-administradores') }}" class="flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50/60 px-3 py-2.5 text-sm font-medium text-amber-950 hover:bg-amber-100/80 transition-colors">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-amber-200/80 text-amber-900">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244"/></svg>
                        </span>
                        Vincular centro con administrador
                    </a>
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-medium text-slate-700 hover:border-amber-300 hover:bg-amber-50/50 transition-colors">
                        <span class="flex h-9 w-9 items-center justify-center rounded-lg bg-slate-100 text-slate-600">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z"/></svg>
                        </span>
                        Panel admin (tu centro)
                    </a>
                    <a href="{{ route('admin.usuarios.index') }}"
                       wire:navigate
                       class="sgd-btn-primary inline-flex w-full items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-white text-sm font-medium cursor-pointer no-underline hover:text-white">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                        Gestión de usuarios
                    </a>
                    @if(\Illuminate\Support\Facades\Route::has('admin.training-centers.index'))
                    <a href="{{ route('admin.training-centers.index') }}" class="sgd-btn-primary flex items-center justify-center gap-2 px-3 py-2.5 rounded-xl text-white text-sm font-medium">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008zm0 3h.008v.008h-.008v-.008zm0 3h.008v.008h-.008V21z"/></svg>
                        Centros de formación
                    </a>
                    @endif
                    @if(\Illuminate\Support\Facades\Route::has('admin.departments.index'))
                    <a href="{{ route('admin.departments.index') }}" class="flex items-center gap-3 rounded-lg border border-slate-200 px-3 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors">
                        Datos paramétricos
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
