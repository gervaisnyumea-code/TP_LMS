<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Cours.php';
require_once __DIR__ . '/../../models/Lecon.php';
require_once __DIR__ . '/../../models/CloudinaryHelper.php';

exiger_role(ROLE_ENSEIGNANT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('enseignant/cours');
verifier_csrf();

$cours_id = (int)($_POST['cours_id'] ?? 0);
$titre = trim($_POST['titre'] ?? '');
$type_contenu = $_POST['type_contenu'] ?? '';
$duree_estimee = !empty($_POST['duree_estimee']) ? (int)$_POST['duree_estimee'] : null;

$coursModel = new Cours($pdo);
if (!$coursModel->appartientA($cours_id, $_SESSION['user_id'])) {
    set_flash('error', 'Acces refuse.');
    rediriger('enseignant/cours');
}

if (!isset($_FILES['fichier']) || $_FILES['fichier']['error'] !== UPLOAD_ERR_OK) {
    set_flash('error', 'Veuillez uploader un fichier valide.');
    rediriger('enseignant/cours/edit', ['id' => $cours_id]);
}

$file = $_FILES['fichier'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$mime = mime_content_type($file['tmp_name']);

// Validation MIME et taille
$isValid = false;
if ($type_contenu === 'pdf' && in_array($ext, EXT_PDF) && in_array($mime, MIME_PDF) && $file['size'] <= MAX_FILE_SIZE_PDF) {
    $isValid = true;
} elseif ($type_contenu === 'video' && in_array($ext, EXT_VIDEO) && in_array($mime, MIME_VIDEO) && $file['size'] <= MAX_FILE_SIZE_VIDEO) {

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

    $isValid = true;
}

if (!$isValid) {
    set_flash('error', 'Type ou taille de fichier invalide.');
    rediriger('enseignant/cours/edit', ['id' => $cours_id]);
}

// Calcul de l'ordre
$leconModel = new Lecon($pdo);
$ordre = $leconModel->prochainOrdre($cours_id);

// Upload vers Cloudinary
$cloudConfig = CloudinaryHelper::getConfigForMime($mime);
$public_id = $type_contenu . '_' . $cours_id . '_' . time();

$result = CloudinaryHelper::upload(
    $file['tmp_name'],
    $cloudConfig['folder'],
    $public_id,
    $cloudConfig['resource_type']
);

if ($result) {
    // Inserer la lecon avec l'URL Cloudinary
    $stmt = $pdo->prepare("
        INSERT INTO lecons (cours_id, titre, description, type_contenu, url_contenu, cloudinary_id, duree_estimee, ordre)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $cours_id,
        $titre,
        '',
        $type_contenu,
        $result['url'],
        $result['public_id'],
        $duree_estimee,
        $ordre,
    ]);
    set_flash('success', 'Lecon ajoutee avec succes.');
} else {
    set_flash('error', 'Erreur lors de l\'upload vers Cloudinary.');
}

rediriger('enseignant/cours/edit', ['id' => $cours_id]);
