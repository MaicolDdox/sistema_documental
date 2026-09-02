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
            <p class="text-sm font-medium text-slate-500">Líderes de Proyecto</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalLideresProyecto }}</p>
            <p class="text-xs text-slate-500 mt-1">{{ $proyectosActivos }} proyecto(s) activo(s)</p>
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

    <div class="space-y-6">
            <div class="sgd-table-card bg-white overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-amber-50/40 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Usuarios recientes</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Últimos registros en cualquier centro</p>
                    </div>
                    <a href="{{ route('super-admin.administradores.index') }}" class="text-sm font-medium text-[#39A900] hover:text-[#2d8500] transition-colors">Gestionar usuarios</a>
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
                    <h3 class="text-sm font-semibold text-slate-900">Proyectos recientes</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Nombre</th>
                                <th class="text-left">Semillero</th>
                                <th class="text-left">Centro</th>
                                <th class="text-left">Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentProjects as $project)
                            <tr>
                                <td class="px-4 py-2.5 font-medium text-slate-900">{{ $project->nombre }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $project->seedling?->nombre ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-slate-600">{{ $project->seedling?->trainingCenter?->nombre ?? '—' }}</td>
                                <td class="px-4 py-2.5">
                                    @if($project->estado && $project->estado->value === 'activo')
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-700"><span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Activo</span>
                                    @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-slate-600"><span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Inactivo</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500 text-sm">No hay proyectos registrados.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</x-app-layout>
