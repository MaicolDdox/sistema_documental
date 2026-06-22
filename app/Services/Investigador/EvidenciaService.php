<?php

namespace App\Services\Investigador;

use App\Models\GroupProduct;
use App\Models\Product;
use App\Models\ProductEvidence;
use App\Models\Project;
use App\Models\ProjectEvidence;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class EvidenciaService
{
    private const MAX_SIZE_MB = 10;
    private const ALLOWED_TYPES = ['pdf', 'jpg', 'jpeg', 'png', 'docx', 'xlsx'];

    /**
     * Sube una evidencia y la asocia a un GroupProduct (vía Product).
     */
    public function subirParaProducto(UploadedFile $file, GroupProduct $groupProduct, int $userId): ProductEvidence
    {
        $this->validarArchivo($file);

        $product = $groupProduct->product;

        $path = $file->store("evidencias/productos/{$product->id}", 'local');

        return ProductEvidence::create([
            'product_id'  => $product->id,
            'archivo'     => $path,
            'nombre'      => $file->getClientOriginalName(),
            'uploaded_by' => $userId,
            'descripcion' => null,
        ]);
    }

    /**
     * Sube una evidencia y la asocia a un Project.
     */
    public function subirParaProyecto(UploadedFile $file, Project $project, int $userId): ProjectEvidence
    {
        $this->validarArchivo($file);

        $path = $file->store("evidencias/proyectos/{$project->id}", 'local');

        return ProjectEvidence::create([
            'project_id'  => $project->id,
            'archivo'     => $path,
            'nombre'      => $file->getClientOriginalName(),
            'uploaded_by' => $userId,
            'descripcion' => null,
        ]);
    }

    /**
     * Elimina una evidencia de producto (archivo + registro).
     */
    public function eliminarEvidenciaProducto(ProductEvidence $evidencia): void
    {
        Storage::disk('local')->delete($evidencia->archivo);
        $evidencia->delete();
    }

    /**
     * Elimina una evidencia de proyecto (archivo + registro).
     */
    public function eliminarEvidenciaProyecto(ProjectEvidence $evidencia): void
    {
        Storage::disk('local')->delete($evidencia->archivo);
        $evidencia->delete();
    }

    /**
     * Valida tamaño y extensión del archivo.
     */
    private function validarArchivo(UploadedFile $file): void
    {
        $maxBytes = self::MAX_SIZE_MB * 1024 * 1024;

        if ($file->getSize() > $maxBytes) {
            throw new RuntimeException(
                "El archivo no puede superar " . self::MAX_SIZE_MB . " MB."
            );
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (! in_array($extension, self::ALLOWED_TYPES)) {
            throw new RuntimeException(
                "Tipo de archivo no permitido. Usa: " . implode(', ', self::ALLOWED_TYPES)
            );
        }
    }
}
