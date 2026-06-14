<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */


// Chargement manuel du .env
if (file_exists(__DIR__ . '/../.env')) {
    $lines = file(__DIR__ . '/../.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        putenv(trim($name) . '=' . trim($value));
    }
}

// -- Identite de l'application
define('APP_NAME', getenv('APP_NAME') ?: 'LMS Cameroun');
define('APP_VERSION', '1.0.0');
define('APP_BASE_URL', getenv('APP_BASE_URL') ?: '');
define('APP_ENV', getenv('APP_ENV') ?: 'development');

// -- Cloudinary (stockage fichiers PDF et video)
$cloudinaryUrl = getenv('URL_CLOUDINARY') ?: '';
$cloudinaryParts = $cloudinaryUrl ? parse_url($cloudinaryUrl) : [];


/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

define('CLOUDINARY_CLOUD_NAME', getenv('CLOUDINARY_CLOUD_NAME') ?: ($cloudinaryParts['host'] ?? ''));
define('CLOUDINARY_API_KEY', getenv('CLOUDINARY_API_KEY') ?: (isset($cloudinaryParts['user']) ? urldecode($cloudinaryParts['user']) : ''));
define('CLOUDINARY_API_SECRET', getenv('CLOUDINARY_API_SECRET') ?: (isset($cloudinaryParts['pass']) ? urldecode($cloudinaryParts['pass']) : ''));
define('CLOUDINARY_BASE_URL', 'https://res.cloudinary.com/' . CLOUDINARY_CLOUD_NAME);

// -- Limites d'upload
define('MAX_FILE_SIZE_PDF', 50 * 1024 * 1024);        // 50 Mo
define('MAX_FILE_SIZE_VIDEO', 500 * 1024 * 1024);      // 500 Mo

// -- Parametres pedagogiques
define('DEFAULT_NOTE_PASSAGE', 70);
define('DEFAULT_TENTATIVES_MAX', 3);

// -- Roles du systeme
define('ROLE_ETUDIANT', 'etudiant');
define('ROLE_ENSEIGNANT', 'enseignant');
define('ROLE_PROMOTEUR', 'promoteur');
define('ROLES', [ROLE_ETUDIANT, ROLE_ENSEIGNANT, ROLE_PROMOTEUR]);

// -- Types MIME autorises
define('MIME_PDF', ['application/pdf']);
define('MIME_VIDEO', ['video/mp4', 'video/webm']);
define('MIME_IMAGE', ['image/jpeg', 'image/png', 'image/webp']);
define('EXT_PDF', ['pdf']);
define('EXT_VIDEO', ['mp4', 'webm']);

// -- Session
define('SESSION_TIMEOUT', (int)(getenv('SESSION_TIMEOUT') ?: 3600));
define('SESSION_NAME', 'LMS_SESSION');
