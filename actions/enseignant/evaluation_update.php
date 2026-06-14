<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Cours.php';
require_once __DIR__ . '/../../models/Evaluation.php';

exiger_role(ROLE_ENSEIGNANT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('enseignant/cours');

error_log("--- EVALUATION UPDATE START ---");
error_log("POST DATA: " . print_r($_POST, true));

verifier_csrf();
error_log("CSRF OK");

$cours_id = (int)($_POST['cours_id'] ?? 0);
$evaluation_id = (int)($_POST['evaluation_id'] ?? 0);
$titre = trim($_POST['titre'] ?? '');
$note_de_passage = max(1, min(100, (int)($_POST['note_de_passage'] ?? 70)));
$tentatives_max = max(1, (int)($_POST['tentatives_max'] ?? 3));

error_log("Params: CID=$cours_id, EID=$evaluation_id, Title=$titre, Note=$note_de_passage, Max=$tentatives_max");

$coursModel = new Cours($pdo);
if (!$coursModel->appartientA($cours_id, $_SESSION['user_id'])) {
    error_log("OWNERSHIP FAILED");
    set_flash('error', 'Acces refuse.');
    rediriger('enseignant/cours');
}
error_log("OWNERSHIP OK");

$evaluationModel = new Evaluation($pdo);
try {
    $evaluationModel->modifier($evaluation_id, $titre, $note_de_passage, null, $tentatives_max);
    error_log("DATABASE UPDATE EXECUTED");
} catch (Exception $e) {
    error_log("DATABASE UPDATE FAILED: " . $e->getMessage());
}

set_flash('success', 'Evaluation mise a jour.');
error_log("REDIRECTING...");
rediriger('enseignant/cours/edit', ['id' => $cours_id]);
