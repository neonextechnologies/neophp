<?php

/**
 * Web Routes
 * 
 * Register your application routes here.
 */

/** @var \NeoPhp\Routing\Router $router */
$router = app('router');

// Welcome route
$router->get('/', function ($request) {
    return response(view('home'));
})->name('home');

// Example: Define your routes here
// $router->get('/about', [HomeController::class, 'about']);
// $router->resource('/products', ProductController::class);
