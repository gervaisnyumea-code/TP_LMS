<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Module.php';

exiger_role(ROLE_PROMOTEUR);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('promoteur/modules');
verifier_csrf();

$module_id = (int)($_POST['module_id'] ?? 0);
$cours_id = (int)($_POST['cours_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($module_id > 0 && $cours_id > 0) {
    $moduleModel = new Module($pdo);
    if ($action === 'associer') {
        $moduleModel->associerCours($module_id, $cours_id);
        set_flash('success', 'Cours associé avec succès.');
    } elseif ($action === 'dissocier') {
        $moduleModel->dissocierCours($cours_id);
        set_flash('success', 'Cours dissocié avec succès.');
    }
}

rediriger('promoteur/module/edit', ['id' => $module_id]);
