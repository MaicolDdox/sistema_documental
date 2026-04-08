<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Previene que el navegador almacene en caché páginas protegidas.
 * Evita que al presionar "Atrás" se muestre la vista sin revalidar sesión.
 */
class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'        => 'no-cache',
            'Expires'       => 'Fri, 01 Jan 1990 00:00:00 GMT',
        ];

        // Binary/stream download responses (StreamedResponse, BinaryFileResponse)
        // do not implement withHeaders(), but they still expose a headers bag.
        if (method_exists($response, 'withHeaders')) {
            return $response->withHeaders($headers);
        }

        foreach ($headers as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
