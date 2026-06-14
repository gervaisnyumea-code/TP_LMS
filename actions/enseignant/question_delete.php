<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Evaluation.php';
require_once __DIR__ . '/../../models/Cours.php';

exiger_role(ROLE_ENSEIGNANT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('enseignant/cours');
verifier_csrf();

$evaluation_id = (int)($_POST['evaluation_id'] ?? 0);
$question_id = (int)($_POST['question_id'] ?? 0);

$evaluationModel = new Evaluation($pdo);
// Verification de sécurité pour s'assurer que l'enseignant possède bien ce cours
$eval = $evaluationModel->trouverParId($evaluation_id);
if (!$eval) rediriger('enseignant/cours');

$coursModel = new Cours($pdo);
if ($coursModel->appartientA($eval['cours_id'], $_SESSION['user_id'])) {
    $evaluationModel->supprimerQuestion($question_id);
    set_flash('success', 'Question supprimée.');
} else {
    set_flash('error', 'Accès refusé.');
}

rediriger('enseignant/evaluation_edit', ['id' => $evaluation_id]);
