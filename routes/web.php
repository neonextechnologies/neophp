<?php

/** @var \NeoPhp\Routing\Router $router */
$router = app('router');

// Welcome route
$router->get('/', function ($request) {
    return response(view('home'));
})->name('home');

// API example
$router->get('/api/hello', function ($request) {
    return json([
        'message' => 'Hello from NeoPhp!',
        'version' => '1.0.0',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
})->name('api.hello');

// Route with parameter
$router->get('/user/{id}', function ($request, $id) {
    return json([
        'user_id' => $id,
        'name' => 'User ' . $id
    ]);
})->name('user.show');

// Group example
$router->group(['prefix' => 'api/v1'], function ($router) {
    $router->get('/status', function ($request) {
        return json([
            'status' => 'ok',
            'service' => 'NeoPhp API'
        ]);
    });

    $router->post('/data', function ($request) {
        return json([
            'received' => $request->all()
        ]);
    });
});
