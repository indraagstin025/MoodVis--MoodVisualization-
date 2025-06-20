<?php

// namespace App\Http\Middleware;

// use Closure;
// use Illuminate\Http\Request;
// use Illuminate\Http\Response;

// class CorsMiddleware
// {
//     /**
//      * Handle an incoming request.
//      *
//      * @param  \Illuminate\Http\Request  $request
//      * @param  \Closure  $next
//      * @return mixed
//      */
//     public function handle(Request $request, Closure $next)
//     {
//         $origin = $request->header('Origin');
//         $allowedOrigins = ['http://127.0.0.1:5501', 'https://m00thzqr-5501.asse.devtunnels.ms'];

//         if ($request->isMethod('OPTIONS')) {
//             $response = new Response();

//             if (in_array($origin, $allowedOrigins)) {
//                 $response->header('Access-Control-Allow-Origin', $origin);
//             } else {

//                 return $response->setStatusCode(204);
//             }

//             $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
//             $response->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
//             $response->header('Access-Control-Max-Age', 3600);
//             $response->header('Access-Control-Allow-Credentials', 'true');
//             return $response;
//         }

//         $response = $next($request);


//         if ($response instanceof Response && in_array($origin, $allowedOrigins)) {
//             $response->header('Access-Control-Allow-Origin', $origin);
//             $response->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
//             $response->header('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With');
//             $response->header('Access-Control-Allow-Credentials', 'true');
//         }

//         return $response;
//     }
// }