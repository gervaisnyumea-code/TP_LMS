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


/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

exiger_role(ROLE_PROMOTEUR);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('promoteur/supervision');
verifier_csrf();

$cours_id = (int)$_POST['cours_id'];
$enseignant_id = (int)$_POST['enseignant_id'];

$coursModel = new Cours($pdo);
$coursModel->assignerEnseignant($cours_id, $enseignant_id);

set_flash('success', 'Enseignant assigné au cours avec succès.');
rediriger('promoteur/supervision?tab=cours');
