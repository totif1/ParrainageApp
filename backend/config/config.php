<?php
return [
    'jwt' => [
        'secret' => $_ENV['JWT_SECRET'] ?? 'parrainage_but_secret_key_2024',
        'algorithm' => 'HS256',
        'expiration' => 3600 * 24 // 24 heures
    ],
    'cors' => [
        'allowed_origins' => [
            'http://localhost:4200',
            'http://localhost:3000'
        ],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With']
    ],
    'pagination' => [
        'default_limit' => 50,
        'max_limit' => 100
    ]
];