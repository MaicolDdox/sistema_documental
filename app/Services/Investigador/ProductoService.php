<?php

namespace App\Services\Investigador;

use App\Enums\EstadoEnum;
use App\Enums\EstadoRevisionEnum;
use App\Models\GroupProduct;
use App\Models\Product;
use App\Models\ProductAuthor;
use App\Models\Project;
use App\Models\ProjectAuthor;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductoService
{
    /**
     * Registra un producto del semillero en el grupo de investigación.
     *
     * Reglas de negocio:
     * 1. El proyecto debe pertenecer al investigador.
     * 2. Cada autor propuesto debe ser autor del proyecto.
     * 3. Se crea Product + GroupProduct en estado "pendiente".
     */
    public function registrar(array $data, int $userId, int $grupoId): GroupProduct
    {
        // 1. Validar que el proyecto existe y el usuario está vinculado
        $proyecto = Project::where('id', $data['project_id'])->firstOrFail();

        // Se permite registrar productos si el usuario:
        //  - es autor del proyecto (project_authors), O
        //  - es el creador del proyecto (project_creator_id)
        $esAutor = ProjectAuthor::where('project_id', $proyecto->id)
            ->where('user_id', $userId)
            ->exists();
        $esCreador = $proyecto->project_creator_id === $userId;

        if (!($esAutor || $esCreador)) {
            throw new RuntimeException('No puedes registrar productos en este proyecto porque no estás vinculado como autor.');
        }

        // 2. Validar que los autores propuestos existan entre los autores del proyecto
        $autoresProyecto = ProjectAuthor::where('project_id', $proyecto->id)
            ->pluck('user_id')
            ->toArray();

        $autoresProducto = $data['autores'] ?? [];
        $invalidos = array_diff($autoresProducto, $autoresProyecto);

        if (! empty($invalidos)) {
            throw new RuntimeException(
                'Uno o más autores del producto no son autores del proyecto seleccionado.'
            );
        }

        return DB::transaction(function () use ($data, $userId, $grupoId, $proyecto) {
            // Reutilizar o crear el Product base
            if (!empty($data['product_base_id'])) {
                $product = Product::findOrFail($data['product_base_id']);
                // Se mantiene el archivo/url originarios que aprobó el líder, y el estado_revision del product.
                $product->update([
                    'nombre' => $data['titulo'], // Actualiza título por si el investigador lo cambió
                ]);
            } else {
                $product = Product::create([
                    'project_id'        => $proyecto->id,
                    'nombre'            => $data['titulo'],
                    'estado'            => EstadoEnum::Activo,
                    'estado_revision'   => 'pendiente',
                    'url_repositorio'   => $data['url_repositorio'] ?? null,
                ]);
            }

            // Crear el GroupProduct (registro formal en el grupo)
            $groupProduct = GroupProduct::create([
                'author_id'                      => $userId,
                'product_id'                     => $product->id,
                'tipo_proyecto_origen'           => $data['tipo_proyecto_origen'] ?? null,
                'campo_otro'                     => $data['campo_otro'] ?? null,
                'codigo_proyecto_origen'         => $data['codigo_proyecto_origen'] ?? null,
                'titulo'                         => $data['titulo'],
                'descripccion'                   => $data['descripccion'] ?? null,
                'anio_publicacion'               => $data['anio_publicacion'],
                'nombre_programa_formacion_impacto' => $data['nombre_programa_formacion_impacto'] ?? null,
                'minciencias_typology_id'        => $data['minciencias_typology_id'] ?? null,
                'minciencias_subcategory_id'     => $data['minciencias_subcategory_id'] ?? null,
                'knowledge_grand_area_id'        => $data['knowledge_grand_area_id'] ?? null,
                'knowledge_area_id'              => $data['knowledge_area_id'] ?? null,
                'tiene_repositorio'              => $data['tiene_repositorio'] ?? false,
                'url_repositorio'                => $data['url_repositorio'] ?? null,
                'autoriza_datos'                 => $data['autoriza_datos'] ?? false,
                'estado_revision'                => EstadoRevisionEnum::Pendiente,
                'observaciones_revision'         => null,
            ]);

            // Registrar autores del producto
            $autoresProducto = $data['autores'] ?? [];
            foreach ($autoresProducto as $autorId) {
                ProductAuthor::firstOrCreate([
                    'product_id' => $product->id,
                    'user_id'    => $autorId,
                ]);
            }

            return $groupProduct;
        });
    }

    /**
     * Corrige un producto rechazado. Solo aplica si está en estado "rechazado".
     * Actualiza los datos y lo vuelve a "pendiente".
     */
    public function corregir(GroupProduct $groupProduct, array $data): GroupProduct
    {
        if ($groupProduct->estado_revision !== EstadoRevisionEnum::Rechazado) {
            throw new RuntimeException(
                'Solo se pueden corregir productos en estado rechazado.'
            );
        }

        DB::transaction(function () use ($groupProduct, $data) {
            $groupProduct->update([
                'tipo_proyecto_origen'      => $data['tipo_proyecto_origen'] ?? $groupProduct->tipo_proyecto_origen,
                'campo_otro'                => $data['campo_otro'] ?? $groupProduct->campo_otro,
                'codigo_proyecto_origen'    => $data['codigo_proyecto_origen'] ?? $groupProduct->codigo_proyecto_origen,
                'titulo'                    => $data['titulo'] ?? $groupProduct->titulo,
                'descripccion'              => $data['descripccion'] ?? $groupProduct->descripccion,
                'anio_publicacion'          => $data['anio_publicacion'] ?? $groupProduct->anio_publicacion,
                'nombre_programa_formacion_impacto' => $data['nombre_programa_formacion_impacto'] ?? $groupProduct->nombre_programa_formacion_impacto,
                'minciencias_typology_id'   => $data['minciencias_typology_id'] ?? $groupProduct->minciencias_typology_id,
                'minciencias_subcategory_id' => $data['minciencias_subcategory_id'] ?? $groupProduct->minciencias_subcategory_id,
                'knowledge_grand_area_id'   => $data['knowledge_grand_area_id'] ?? $groupProduct->knowledge_grand_area_id,
                'knowledge_area_id'         => $data['knowledge_area_id'] ?? $groupProduct->knowledge_area_id,
                'tiene_repositorio'         => $data['tiene_repositorio'] ?? $groupProduct->tiene_repositorio,
                'url_repositorio'           => $data['url_repositorio'] ?? $groupProduct->url_repositorio,
                'autoriza_datos'            => $data['autoriza_datos'] ?? $groupProduct->autoriza_datos,
                'estado_revision'           => EstadoRevisionEnum::Pendiente,
                'observaciones_revision'    => null, // Limpiar observaciones previas
            ]);

            // Actualizar también el Product base
            $groupProduct->product->update([
                'nombre'         => $data['titulo'] ?? $groupProduct->titulo,
                'url_repositorio' => $data['url_repositorio'] ?? $groupProduct->url_repositorio,
            ]);
        });

        return $groupProduct->fresh();
    }
}
