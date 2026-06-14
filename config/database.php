<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

// config/database.php

// 1. Priorité à DATABASE_URL (pour Neon en production)
$dbUrl = getenv('DATABASE_URL');

if ($dbUrl) {
    $parts = parse_url($dbUrl);
    
    // Construction du DSN pour PDO pgsql à partir de l'URL
    $host = $parts['host'] ?? 'localhost';
    $port = $parts['port'] ?? '5432';
    $db   = ltrim($parts['path'] ?? '', '/');
    
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    if (isset($parts['query'])) {
        $dsn .= ";" . str_replace('&', ';', $parts['query']);
    }
    
    $user = $parts['user'] ?? null;
    $pass = $parts['pass'] ?? null;

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

} else {
    // 2. Fallback pour le développement local
    $host = getenv('DB_HOST') ?: 'db';
    $db   = getenv('DB_NAME') ?: 'lms_cameroun';
    $user = getenv('DB_USER') ?: 'lms_user';
    $pass = getenv('DB_PASS') ?: 'lms_password';
    $port = getenv('DB_PORT') ?: '5432';

    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
}

$max_retries = 5;
$retry_delay = 2; // seconds

for ($i = 0; $i < $max_retries; $i++) {
    try {
        $pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        break; // Connection successful
    } catch (PDOException $e) {
        if ($i === $max_retries - 1) {
            die("Erreur de connexion après $max_retries tentatives : " . $e->getMessage());
        }
        sleep($retry_delay);
    }
}
