<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Evaluation.php';
require_once __DIR__ . '/../../models/Cours.php';

exiger_role(ROLE_ENSEIGNANT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('enseignant/cours');
verifier_csrf();

$evaluation_id = (int)($_POST['evaluation_id'] ?? 0);
$question_id = (int)($_POST['question_id'] ?? 0); // 0 pour création
$texte = trim($_POST['texte'] ?? '');
$reponse = trim($_POST['reponse'] ?? '');
$ordre = (int)($_POST['ordre'] ?? 1);

// Options JSON formatées (A, B, C, D)
$options = [
    'A' => trim($_POST['option_A'] ?? ''),
    'B' => trim($_POST['option_B'] ?? ''),
    'C' => trim($_POST['option_C'] ?? ''),
    'D' => trim($_POST['option_D'] ?? '')
];

$evaluationModel = new Evaluation($pdo);
$eval = $evaluationModel->trouverParId($evaluation_id);

if (!$eval) {
    set_flash('error', 'Évaluation introuvable.');
    rediriger('enseignant/cours');
}

if ($question_id > 0) {
    $evaluationModel->modifierQuestion($question_id, $texte, $options, $reponse, $ordre);
    set_flash('success', 'Question mise à jour.');
} else {
    $evaluationModel->ajouterQuestion($evaluation_id, $texte, $options, $reponse, $ordre);
    set_flash('success', 'Question ajoutée.');
}

rediriger('enseignant/evaluation_edit', ['id' => $evaluation_id]);
