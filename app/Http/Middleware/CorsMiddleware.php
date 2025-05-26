<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $origin = $request->header('Origin');
        // PASTIKAN INI SESUAI DENGAN ORIGIN FRONTEND ANDA!
        // Tambahkan 'http://127.0.0.1:5501' dan hapus yang tidak perlu (https://localhost:5501 jika tidak dipakai)
        $allowedOrigins = ['http://127.0.0.1:5501', 'http://localhost:5501']; // Tambahkan 'http://localhost:5501' jika Anda juga mengakses dengan localhost

        if ($request->isMethod('OPTIONS')) {
            $response = new Response();
            // Jika origin ada di allowedOrigins, gunakan origin tersebut; jika tidak, kembalikan 204 No Content
            if (in_array($origin, $allowedOrigins)) {
                $response->header('Access-Control-Allow-Origin', $origin);
            } else {
                // Jika origin tidak diizinkan, kirim respons 204 tanpa header CORS yang mengizinkan
                return $response->setStatusCode(204);
            }

            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
            $response->header('Access-Control-Max-Age', 3600); // Cache preflight for 1 hour
            $response->header('Access-Control-Allow-Credentials', 'true'); // Set true jika menggunakan kredensial (cookie, auth header)
            return $response;
        }

        $response = $next($request);

        // Tambahkan header CORS pada respons sebenarnya
        if ($response instanceof Response && in_array($origin, $allowedOrigins)) {
            $response->header('Access-Control-Allow-Origin', $origin);
            $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
            $response->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
            $response->header('Access-Control-Allow-Credentials', 'true'); // Set true jika menggunakan kredensial
        }

        return $response;
    }
}
