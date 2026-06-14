<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';

exiger_connexion();

$lecon_id = (int)($_GET['id'] ?? 0);
if ($lecon_id <= 0) {
    http_response_code(400);
    exit('Parametre invalide');
}

// Recuperer la lecon
$stmt = $pdo->prepare("
    SELECT l.*, c.enseignant_id, c.id as cours_id
    FROM lecons l
    JOIN cours c ON c.id = l.cours_id
    WHERE l.id = ?
");
$stmt->execute([$lecon_id]);
$lecon = $stmt->fetch();

if (!$lecon) {
    http_response_code(404);
    exit('Lecon non trouvee');
}

// Verification autorisation
$authorized = false;

if ($_SESSION['role'] === 'enseignant') {
    $authorized = ($lecon['enseignant_id'] == $_SESSION['user_id']);
} elseif ($_SESSION['role'] === 'etudiant') {
    $stmt = $pdo->prepare("SELECT id FROM inscriptions WHERE etudiant_id = ? AND cours_id = ?");
    $stmt->execute([$_SESSION['user_id'], $lecon['cours_id']]);
    $authorized = (bool) $stmt->fetch();
} elseif ($_SESSION['role'] === 'promoteur') {
    $authorized = true;
}

if (!$authorized) {
    http_response_code(403);
    exit('Acces refuse');
}

// Si URL Cloudinary, redirection directe pour les vidéos, mais pas pour les PDF (forceront le téléchargement)
if (!empty($lecon['url_contenu']) && strpos($lecon['url_contenu'], 'cloudinary.com') !== false) {
    if ($lecon['type_contenu'] === 'video') {
        header('Location: ' . $lecon['url_contenu']);
        exit;
    }
    // Pour les PDF, on ne redirige pas, on laisse le player gérer via un viewer externe
}

// Fallback : fichier local (dev only)
$safe_file = basename($lecon['url_contenu']);
$full_path = $lecon['url_contenu'];

if (!file_exists($full_path)) {
    http_response_code(404);
    exit('Fichier non trouve');
}

$mime = ($lecon['type_contenu'] === 'pdf') ? 'application/pdf' : 'video/mp4';
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($full_path));
header('Content-Disposition: inline; filename="' . $safe_file . '"');
header('Cache-Control: private, max-age=3600');

readfile($full_path);
exit;
