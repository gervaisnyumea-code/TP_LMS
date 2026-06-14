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
verifier_csrf();

$cours_id = (int)($_POST['cours_id'] ?? 0);
$evaluation_id = (int)($_POST['evaluation_id'] ?? 0);
$titre = trim($_POST['titre'] ?? '');
$note_de_passage = max(1, min(100, (int)($_POST['note_de_passage'] ?? 70)));
$tentatives_max = max(1, (int)($_POST['tentatives_max'] ?? 3));

$coursModel = new Cours($pdo);
if (!$coursModel->appartientA($cours_id, $_SESSION['user_id'])) {
    set_flash('error', 'Acces refuse.');
    rediriger('enseignant/cours');
}

$evaluationModel = new Evaluation($pdo);
$evaluationModel->modifier($evaluation_id, $titre, $note_de_passage, null, $tentatives_max);

if (!empty($_POST['supprimer_questions'])) {
    foreach ($_POST['supprimer_questions'] as $qid) {
        $evaluationModel->supprimerQuestion((int)$qid);
    }

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

}

if (!empty($_POST['questions_modif'])) {
    foreach ($_POST['questions_modif'] as $qid => $data) {
        $texte = trim($data['texte'] ?? '');
        $options = [
            'A' => trim($data['option_a'] ?? ''),
            'B' => trim($data['option_b'] ?? ''),
            'C' => trim($data['option_c'] ?? ''),
            'D' => trim($data['option_d'] ?? ''),
        ];
        $reponse = strtoupper(trim($data['reponse'] ?? 'A'));
        if ($texte !== '' && in_array($reponse, ['A', 'B', 'C', 'D'])) {
            $evaluationModel->modifierQuestion((int)$qid, $texte, $options, $reponse, (int)($data['ordre'] ?? 0));
        }
    }
}

if (!empty($_POST['nouvelles_questions'])) {
    foreach ($_POST['nouvelles_questions'] as $data) {
        $texte = trim($data['texte'] ?? '');
        $options = [
            'A' => trim($data['option_a'] ?? ''),
            'B' => trim($data['option_b'] ?? ''),
            'C' => trim($data['option_c'] ?? ''),
            'D' => trim($data['option_d'] ?? ''),
        ];
        $reponse = strtoupper(trim($data['reponse'] ?? 'A'));
        $ordre = $evaluationModel->compterQuestions($evaluation_id) + 1;
        if ($texte !== '' && in_array($reponse, ['A', 'B', 'C', 'D'])) {
            $evaluationModel->ajouterQuestion($evaluation_id, $texte, $options, $reponse, $ordre);
        }
    }
}

set_flash('success', 'Evaluation mise a jour.');
rediriger('enseignant/cours/edit', ['id' => $cours_id]);
