<?php
// config/database.php

$host = getenv('DB_HOST') ?: 'db';
$db   = getenv('DB_NAME') ?: 'lms_cameroun';
$user = getenv('DB_USER') ?: 'lms_user';
$pass = getenv('DB_PASS') ?: 'lms_password';
$port = getenv('DB_PORT') ?: '5432';

$dsn = "pgsql:host=$host;port=$port;dbname=$db";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}
