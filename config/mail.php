<?php

return [
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@neophp.local'),
        'name' => env('MAIL_FROM_NAME', 'NeoPhp'),
    ],

    'driver' => env('MAIL_DRIVER', 'mail'),
    
    'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
    
    'port' => env('MAIL_PORT', 2525),
    
    'username' => env('MAIL_USERNAME'),
    
    'password' => env('MAIL_PASSWORD'),
    
    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
];
