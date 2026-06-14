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


$id = (int)($_POST['id'] ?? 0);
$titre = trim($_POST['titre'] ?? '');
$description = trim($_POST['description'] ?? '');
$visible = isset($_POST['visible']);

$coursModel = new Cours($pdo);

if ($id > 0 && $coursModel->appartientA($id, $_SESSION['user_id'])) {
    $coursModel->modifier($id, $titre, $description, $visible);
    set_flash('success', 'Informations du cours mises à jour.');
} else {
    set_flash('error', 'Accès refusé ou données invalides.');
}

rediriger('enseignant/cours/edit', ['id' => $id]);
