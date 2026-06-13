<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Module.php';

exiger_role(ROLE_PROMOTEUR);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('promoteur/modules');
verifier_csrf();

$id = (int)($_POST['id'] ?? 0);

if ($id > 0) {
    $moduleModel = new Module($pdo);
    $moduleModel->supprimer($id);
    set_flash('success', 'Module supprimé avec succès.');
}

rediriger('promoteur/modules');
