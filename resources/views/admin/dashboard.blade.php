<x-app-layout>
    <x-slot name="header">Dashboard</x-slot>

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-slate-900">Panel de Administración</h2>
        <p class="text-sm text-slate-500 mt-1">Resumen del sistema y accesos rápidos.</p>
        @if(\App\Support\TrainingCenterAccess::scopedToTrainingCenter(auth()->user()))
            @php
                $centroCtx = auth()->user()->trainingCenter;
            @endphp
            <p class="text-xs text-slate-600 mt-2 rounded-lg border border-emerald-100 bg-emerald-50/60 px-3 py-2 max-w-2xl">
                Estás viendo solo información del centro:
                <span class="font-semibold text-slate-800">{{ $centroCtx?->nombre ?? 'tu centro asignado' }}</span>.
            </p>
        @endif
        @if(!empty($primaryRoleLabel))
            <p class="text-xs text-slate-600 mt-2 max-w-2xl">
                <span class="font-semibold text-slate-800">Rol principal</span> (prioridad al iniciar sesión):
                <span class="text-slate-800">{{ $primaryRoleLabel }}</span>.
            </p>
        @endif
        {{-- BUG-20260813-055: la tarjeta "Accesos a otros módulos" que iba
             aquí navegaba con <a href> directo, sin pasar por roles.switch —
             desde la Fase 2 (aislamiento por rol activo) eso terminaba en
             403. Se eliminó a favor del selector "Mis roles" del header
             (components/app-layout.blade.php), que sí actualiza el rol
             activo en sesión antes de redirigir. --}}
    </div>

    {{-- 4 tarjetas de métricas --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Usuarios Registrados</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalUsuarios }}</p>
            <p class="text-xs text-slate-500 mt-1 border-b-2 border-[#39A900] pb-0.5 w-fit">+{{ $usuariosEsteMes }} este mes</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Proyectos</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalProyectos }}</p>
            <p class="text-xs text-slate-500 mt-1">+{{ $proyectosActivos }} activo(s)</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Semilleros Activos</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalSemilleros }}</p>
            <p class="text-xs text-slate-500 mt-1">+{{ $semillerosEsteSemestre }} este semestre</p>
        </div>
        <div class="sgd-card bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <p class="text-sm font-medium text-slate-500">Centros de Formación</p>
            <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalCentros }}</p>
            <p class="text-xs text-slate-500 mt-1">Activos</p>
        </div>
    </div>

    <div class="space-y-6">
            {{-- Usuarios recientes --}}
            <div class="sgd-table-card bg-white overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50 flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-slate-900">Usuarios Recientes</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Últimas cuentas creadas</p>
                    </div>
                    @can('usuarios.listar')
                    <a href="{{ route('admin.usuarios.index') }}" class="text-sm font-medium text-[#39A900] hover:text-[#2d8500] transition-colors">Ver todos</a>
                    @endcan
                </div>
                <div class="overflow-x-auto">
                    <table class="sgd-table text-sm">
                        <thead>
                            <tr>
                                <th class="text-left">Usuario</th>
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
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500 text-sm">No hay usuarios recientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Proyectos --}}
            <div class="sgd-table-card bg-white overflow-hidden">
                <div class="px-4 py-3 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-[#f0fdf4]/50 flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-slate-900">Proyectos</h3>
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
                            <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500 text-sm">No hay proyectos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
    </div>
</x-app-layout>
