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
require_once __DIR__ . '/../../models/Lecon.php';

exiger_role(ROLE_ENSEIGNANT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('enseignant/cours');

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

verifier_csrf();

$id = (int)($_POST['id'] ?? 0);
$cours_id = (int)($_POST['cours_id'] ?? 0);

$coursModel = new Cours($pdo);
if ($id > 0 && $coursModel->appartientA($cours_id, $_SESSION['user_id'])) {
    $leconModel = new Lecon($pdo);
    $leconModel->supprimer($id);
    set_flash('success', 'Leçon supprimée.');
} else {
    set_flash('error', 'Accès refusé.');
}

rediriger('enseignant/cours/edit', ['id' => $cours_id]);
