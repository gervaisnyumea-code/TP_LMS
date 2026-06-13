<?php
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
$lecon_id = (int)($_POST['lecon_id'] ?? 0);
$titre = trim($_POST['titre'] ?? '');
$description = trim($_POST['description'] ?? '');
$duree_estimee = !empty($_POST['duree_estimee']) ? (int)$_POST['duree_estimee'] : null;
$ordre = (int)($_POST['ordre'] ?? 1);

$coursModel = new Cours($pdo);
if (!$coursModel->appartientA($cours_id, $_SESSION['user_id'])) {
    set_flash('error', 'Acces refuse.');
    rediriger('enseignant/cours');
}

$leconModel = new Lecon($pdo);
$lecon = $leconModel->trouverParId($lecon_id);

if (!$lecon || $lecon['cours_id'] != $cours_id) {
    set_flash('error', 'Lecon introuvable.');
    rediriger('enseignant/cours/edit', ['id' => $cours_id]);
}

$leconModel->modifier($lecon_id, $titre, $description, $duree_estimee, $ordre);

if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['fichier'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);

    $isValid = false;
    if ($lecon['type_contenu'] === 'pdf' && in_array($ext, EXT_PDF) && in_array($mime, MIME_PDF) && $file['size'] <= MAX_FILE_SIZE_PDF) {
        $isValid = true;
    } elseif ($lecon['type_contenu'] === 'video' && in_array($ext, EXT_VIDEO) && in_array($mime, MIME_VIDEO) && $file['size'] <= MAX_FILE_SIZE_VIDEO) {
        $isValid = true;
    }

    if ($isValid) {
        if (!empty($lecon['cloudinary_id'])) {
            $resource_type = ($lecon['type_contenu'] === 'pdf') ? 'raw' : 'video';
            CloudinaryHelper::destroy($lecon['cloudinary_id'], $resource_type);
        }
        $cloudConfig = CloudinaryHelper::getConfigForMime($mime);
        $public_id = $lecon['type_contenu'] . '_' . $cours_id . '_' . time();
        $result = CloudinaryHelper::upload($file['tmp_name'], $cloudConfig['folder'], $public_id, $cloudConfig['resource_type']);
        if ($result) {
            $stmt = $pdo->prepare("UPDATE lecons SET url_contenu = ?, cloudinary_id = ? WHERE id = ?");
            $stmt->execute([$result['url'], $result['public_id'], $lecon_id]);
        }
    } else {
        set_flash('error', 'Type ou taille de fichier invalide.');
        rediriger('enseignant/cours/edit', ['id' => $cours_id]);
    }
}

set_flash('success', 'Lecon mise a jour.');
rediriger('enseignant/cours/edit', ['id' => $cours_id]);
