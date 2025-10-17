<?php
// app/Http/Middleware/Cors.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $headers = [
            // Izinkan akses dari semua origin.
            'Access-Control-Allow-Origin'      => '*', 
            // Izinkan semua metode yang digunakan
            'Access-Control-Allow-Methods'     => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            // Header yang diizinkan dari frontend (wajib ada Content-Type & Authorization)
            'Access-Control-Allow-Headers'     => 'Content-Type, Authorization, X-Requested-With, Accept',
            // Cache preflight request selama 24 jam
            'Access-Control-Max-Age'           => '86400', 
        ];
        
        // Tangani OPTIONS Request (Preflight)
        if ($request->isMethod('OPTIONS'))
        {
            return response('', 200, $headers);
        }
        
        // Proses Request Utama
        $response = $next($request);
        
        // Tambahkan Header ke Response. Memastikan $response adalah objek Response yang valid.
        if ($response instanceof Response) {
             foreach($headers as $key => $value)
             {
                 $response->header($key, $value);
             }
        }

        return $response;
    }
}