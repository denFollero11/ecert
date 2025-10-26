<?php
session_start();

define('DB_HOST','127.0.0.1');
define('DB_NAME','ecert');
define('DB_USER','root');
define('DB_PASS','');

define('NODE_GATEWAY_URL', 'http://localhost:4000/api');

function db() {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host='.DB_HOST.';dbname=' . DB_NAME . ';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }
    return $pdo;
}