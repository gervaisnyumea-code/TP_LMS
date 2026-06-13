<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Module.php';

exiger_role(ROLE_PROMOTEUR);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('promoteur/modules');
verifier_csrf();

$id = (int)($_POST['id'] ?? 0);
$titre = trim($_POST['titre'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($titre) || $id === 0) {
    set_flash('error', 'Données invalides.');
} else {
    $moduleModel = new Module($pdo);
    $moduleModel->modifier($id, $titre, $description);
    set_flash('success', 'Module mis à jour.');
}

rediriger('promoteur/module/edit', ['id' => $id]);
