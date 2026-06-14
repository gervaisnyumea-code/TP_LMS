<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Cours.php';

exiger_role(ROLE_ENSEIGNANT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('enseignant/cours');
verifier_csrf();

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */


$titre = trim($_POST['titre'] ?? '');
$description = trim($_POST['description'] ?? '');
$module_id = !empty($_POST['module_id']) ? (int)$_POST['module_id'] : null;

if (empty($titre)) {
    set_flash('error', 'Le titre est obligatoire.');
    rediriger('enseignant/cours');
}

$coursModel = new Cours($pdo);
$id = $coursModel->creer($titre, $description, $_SESSION['user_id'], $module_id);

set_flash('success', 'Cours créé. Vous pouvez maintenant ajouter des leçons.');
rediriger('enseignant/cours/edit', ['id' => $id]);
