<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Module.php';

exiger_role(ROLE_PROMOTEUR);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('promoteur/supervision');
verifier_csrf();

$module_id = (int)$_POST['module_id'];
$cours_id = (int)$_POST['cours_id'];

$moduleModel = new Module($pdo);

if ($module_id > 0) {
    $moduleModel->associerCours($module_id, $cours_id);
    set_flash('success', 'Cours assigné au module.');
} else {
    $moduleModel->dissocierCours($cours_id);
    set_flash('success', 'Cours dissocié du module.');
}

rediriger('promoteur/supervision?tab=modules');
