<?php
// config/database.php
return [
    'host' => $_ENV['DB_HOST'] ?? 'database',
    'dbname' => $_ENV['DB_NAME'] ?? 'parrainage_db',
    'username' => $_ENV['DB_USER'] ?? 'parrainage_user',
    'password' => $_ENV['DB_PASSWORD'] ?? 'parrainage_pass',
    'charset' => 'utf8mb4',
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
];
