<?php

namespace App\Middleware;

use NeoPhp\Http\Middleware;
use NeoPhp\Http\Request;
use NeoPhp\Http\Response;

class CorsMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);

        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->setHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization');

        return $response;
    }
}
