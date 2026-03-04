<x-layouts::app :title="__('Dashboard de Investigación')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">Módulo de Investigación</h1>
        </div>
        <div class="grid auto-rows-min gap-4 md:grid-cols-3">
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <h3 class="font-semibold text-lg mb-2">Grupos</h3>
                <p class="text-sm text-slate-500">Grupos de investigación del centro</p>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <h3 class="font-semibold text-lg mb-2">Proyectos</h3>
                <p class="text-sm text-slate-500">Proyectos de investigación activos</p>
            </div>
            <div class="relative aspect-video overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <h3 class="font-semibold text-lg mb-2">Productos</h3>
                <p class="text-sm text-slate-500">Producción académica registrada</p>
            </div>
        </div>
    </div>
</x-layouts::app>
