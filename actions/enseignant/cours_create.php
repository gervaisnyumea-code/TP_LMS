<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Cours.php';

exiger_role(ROLE_ENSEIGNANT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('enseignant/cours');
verifier_csrf();

$titre = trim($_POST['titre'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($titre)) {
    set_flash('error', 'Le titre est obligatoire.');
    rediriger('enseignant/cours');
}

$coursModel = new Cours($pdo);
$id = $coursModel->creer($titre, $description, $_SESSION['user_id']);

set_flash('success', 'Cours créé. Vous pouvez maintenant ajouter des leçons.');
rediriger('enseignant/cours/edit', ['id' => $id]);
