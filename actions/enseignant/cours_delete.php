<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Cours.php';

exiger_role(ROLE_ENSEIGNANT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('enseignant/cours');
verifier_csrf();

$id = (int)($_POST['id'] ?? 0);
$coursModel = new Cours($pdo);

if ($id > 0 && $coursModel->appartientA($id, $_SESSION['user_id'])) {
    $coursModel->supprimer($id);
    set_flash('success', 'Cours supprimé avec succès.');
} else {
    set_flash('error', 'Accès refusé.');
}

rediriger('enseignant/cours');
