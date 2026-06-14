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
require_once __DIR__ . '/../../models/Evaluation.php';

exiger_role(ROLE_ENSEIGNANT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('enseignant/cours');
verifier_csrf();

$cours_id = (int)($_POST['cours_id'] ?? 0);
$lecon_id = (int)($_POST['lecon_id'] ?? 0);

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$titre = trim($_POST['titre'] ?? '');
$note_de_passage = (int)($_POST['note_de_passage'] ?? 70);
$tentatives_max = (int)($_POST['tentatives_max'] ?? 3);

$coursModel = new Cours($pdo);
if ($coursModel->appartientA($cours_id, $_SESSION['user_id'])) {
    $evaluationModel = new Evaluation($pdo);
    $eval_id = $evaluationModel->creer($lecon_id, $titre, $note_de_passage, null, $tentatives_max);
    
    // Auto-generate some demo questions for the sake of the TP
    $options = ['A' => 'Vrai', 'B' => 'Faux', 'C' => 'Peut-être', 'D' => 'Aucune idée'];
    $evaluationModel->ajouterQuestion($eval_id, "Avez-vous bien compris cette leçon ?", $options, 'A', 1);
    
    set_flash('success', 'Évaluation créée (avec 1 question par défaut).');
} else {
    set_flash('error', 'Accès refusé.');
}

rediriger('enseignant/cours/edit', ['id' => $cours_id]);
