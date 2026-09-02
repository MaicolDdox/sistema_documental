<?php

namespace App\Concerns;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * BUG-20260813-030 — descarga archivos del disco "public" desde el backend
 * en vez de depender de la URL pública (/storage/...), que requiere el
 * symlink `public/storage` y puede romperse en despliegues que no lo
 * soporten. La opción de "ver en el navegador" (inline) se eliminó del
 * sistema completo (decisión del usuario, misma fecha de BUG): solo queda
 * descarga (attachment), que funciona igual en todos los navegadores sin
 * depender de si el formato es renderizable o de configuraciones del
 * visor de PDF del usuario.
 */
trait StreamsPublicStorageFiles
{
    private function descargarArchivoPublico(?string $path, ?string $nombreDescarga = null): StreamedResponse
    {
        if (! $path || ! Storage::disk('public')->exists($path)) {
            abort(404, 'El archivo no existe.');
        }

        return Storage::disk('public')->download($path, $this->nombreArchivoConExtension($path, $nombreDescarga));
    }

    /**
     * $nombreDescarga suele ser un título "humano" (nombre de la evidencia,
     * descripción del archivo) sin extensión. Sin extensión, el archivo
     * descargado no se podría abrir con doble clic. Siempre se preserva la
     * extensión real del archivo guardado.
     */
    private function nombreArchivoConExtension(string $path, ?string $nombreDescarga): string
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (! $nombreDescarga) {
            return basename($path);
        }

        if ($extension === '' || strcasecmp(pathinfo($nombreDescarga, PATHINFO_EXTENSION), $extension) === 0) {
            return $nombreDescarga;
        }

        return $nombreDescarga.'.'.$extension;
    }
}
