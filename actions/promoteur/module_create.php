<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Module.php';

exiger_role(ROLE_PROMOTEUR);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('promoteur/modules');

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

verifier_csrf();

$titre = trim($_POST['titre'] ?? '');
$description = trim($_POST['description'] ?? '');

if (empty($titre)) {
    set_flash('error', 'Le titre est obligatoire.');
} else {
    $moduleModel = new Module($pdo);
    $moduleModel->creer($titre, $description, $_SESSION['user_id']);
    set_flash('success', 'Module créé avec succès.');
}

rediriger('promoteur/modules');
