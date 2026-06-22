<?php

namespace Tests\Feature\Regression;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Regresión: BUG-20260610-001
 * Descripción: Sistema con carga lenta por CDN externos, queries duplicadas y forgetCachedPermissions en cada GET
 * Corregido: 2026-06-10
 */
class BUG20260610001Test extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────
    // P4 — forgetCachedPermissions removido de UsuarioController
    // ─────────────────────────────────────────────────

    public function test_usuario_controller_no_llama_forget_cached_permissions(): void
    {
        $contenido = file_get_contents(app_path('Http/Controllers/Admin/UsuarioController.php'));

        $this->assertStringNotContainsString(
            'forgetCachedPermissions',
            $contenido,
            'UsuarioController no debe llamar forgetCachedPermissions — introduce latencia en cada GET'
        );
    }

    // ─────────────────────────────────────────────────
    // P5 — Chart.js CDN removido de vistas de dashboard
    // ─────────────────────────────────────────────────

    public function test_dashboards_no_cargan_chartjs_desde_cdn(): void
    {
        $vistas = [
            resource_path('views/asesor_semillero/dashboard.blade.php'),
            resource_path('views/director_investigacion/dashboard.blade.php'),
            resource_path('views/director_investigacion/reportes/index.blade.php'),
            resource_path('views/director_semilleros/dashboard.blade.php'),
            resource_path('views/investigador/dashboard.blade.php'),
        ];

        foreach ($vistas as $ruta) {
            $contenido = file_get_contents($ruta);
            $this->assertStringNotContainsString(
                'cdn.jsdelivr.net/npm/chart.js',
                $contenido,
                "La vista {$ruta} no debe cargar Chart.js desde CDN externa"
            );
        }
    }

    // ─────────────────────────────────────────────────
    // P7 — Sidebar usa cache()->remember() para training center
    // ─────────────────────────────────────────────────

    public function test_app_layout_usa_cache_para_training_center(): void
    {
        $contenido = file_get_contents(resource_path('views/components/app-layout.blade.php'));

        $this->assertStringContainsString(
            'cache()->remember',
            $contenido,
            'app-layout debe cachear el nombre del centro de formación para no consultar BD en cada render'
        );

        $this->assertStringContainsString(
            'sidebar_centro_',
            $contenido,
            'La clave del cache debe incluir el ID del usuario (sidebar_centro_{id})'
        );

        $this->assertStringNotContainsString(
            'loadMissing(\'trainingCenter\')',
            $contenido,
            'loadMissing no debe usarse — reemplazado por cache()->remember()'
        );
    }

    // ─────────────────────────────────────────────────
    // P6 — AsesorSemillero dashboard no duplica query de productos
    // ─────────────────────────────────────────────────

    public function test_asesor_dashboard_controller_no_duplica_query_productos(): void
    {
        $contenido = file_get_contents(app_path('Http/Controllers/AsesorSemillero/DashboardController.php'));

        // Solo debe haber una llamada a Product:: (la que eager-load 'project')
        $ocurrencias = substr_count($contenido, 'Product::');
        $this->assertSame(
            1,
            $ocurrencias,
            'DashboardController de AsesorSemillero debe hacer una sola query de productos, no dos'
        );

        // La colección $productos debe reutilizarse para los rechazados
        $this->assertStringContainsString(
            'sortByDesc',
            $contenido,
            'Los productos rechazados deben filtrarse de la colección en memoria (sortByDesc), no con una segunda query'
        );
    }

    // ─────────────────────────────────────────────────
    // P8 — DirectorSemilleros dashboard no duplica query de semilleros
    // ─────────────────────────────────────────────────

    public function test_director_semilleros_dashboard_no_duplica_query_semilleros(): void
    {
        $contenido = file_get_contents(app_path('Http/Controllers/DirectorSemilleros/DashboardController.php'));

        // La línea de misSemilleros debe usar la colección en memoria, no un clone del query
        $this->assertStringContainsString(
            '$misSemilleros = $semilleros->sortBy',
            $contenido,
            'misSemilleros debe filtrarse de la colección $semilleros ya cargada, no con un clone de la query'
        );
    }

}
