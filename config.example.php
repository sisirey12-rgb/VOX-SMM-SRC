<?php
declare(strict_types=1);

return [
    'db' => [
        'dsn' => 'mysql:host=127.0.0.1;dbname=vox;charset=utf8mb4',
        'username' => 'CHANGE_ME',
        'password' => 'CHANGE_ME',
        'options' => [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ],
    ],
    // Keep this file outside the public document root.
];
