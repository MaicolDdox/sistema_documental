<x-layouts::app :title="__('Dashboard de Administración')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Panel de Administración</h1>
        </div>
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <h3 class="font-semibold text-lg mb-2">Usuarios</h3>
                <p class="text-sm text-slate-500">Gestión de usuarios y roles del sistema</p>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <h3 class="font-semibold text-lg mb-2">Configuración</h3>
                <p class="text-sm text-slate-500">Catálogos y parámetros del sistema</p>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <h3 class="font-semibold text-lg mb-2">Reportes</h3>
                <p class="text-sm text-slate-500">Estadísticas y métricas generales</p>
            </div>
        </div>
    </div>
</x-layouts::app>
