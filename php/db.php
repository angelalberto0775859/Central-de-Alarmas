<?php
require_once __DIR__ . '/marketing_config.php';

function cdaDb() {
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = 'mysql:host=' . CDA_DB_HOST . ';dbname=' . CDA_DB_NAME . ';charset=utf8mb4';
    $pdo = new PDO($dsn, CDA_DB_USER, CDA_DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    $pdo->exec("SET time_zone = '-06:00'");

    return $pdo;
}
