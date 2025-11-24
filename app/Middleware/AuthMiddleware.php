<?php

namespace App\Middleware;

use NeoPhp\Http\Middleware;
use NeoPhp\Http\Request;
use NeoPhp\Http\Response;

class AuthMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login');
        }

        return $next($request);
    }
}
