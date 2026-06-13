<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Evaluation.php';
require_once __DIR__ . '/../../models/Progression.php';
require_once __DIR__ . '/../../models/Certificat.php';
require_once __DIR__ . '/../../models/Cours.php';

header('Content-Type: application/json');

if (!est_connecte() || $_SESSION['role'] !== ROLE_ETUDIANT || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit;
}

$evaluation_id = (int)($_POST['evaluation_id'] ?? 0);
$cours_id = (int)($_POST['cours_id'] ?? 0);
$etudiant_id = $_SESSION['user_id'];

if (!$evaluation_id || !$cours_id) {
    echo json_encode(['success' => false, 'message' => 'Données invalides.']);
    exit;
}

$evaluationModel = new Evaluation($pdo);
$evalInfo = $evaluationModel->trouverParId($evaluation_id);
if (!$evalInfo) {
    echo json_encode(['success' => false, 'message' => 'Évaluation introuvable.']);
    exit;
}

$questions = $evaluationModel->listerQuestions($evaluation_id);
$totalQuestions = count($questions);
$score = 0;

if ($totalQuestions > 0) {
    $bonnesReponses = 0;
    foreach ($questions as $q) {
        $key = 'question_' . $q['id'];
        if (isset($_POST[$key]) && $_POST[$key] === $q['reponse_correcte']) {
            $bonnesReponses++;
        }
    }
    $score = round(($bonnesReponses / $totalQuestions) * 100);
}

// Progression handling
$progressionModel = new Progression($pdo);
$lecon_id = $evalInfo['lecon_id'];

// Check si on a le droit de tenter (dans le TP, on permet 3 max)
// Note: on aurait du avoir $tentatives_max dans l'évaluation
$max = $evalInfo['tentatives_max'] ?? 3;
$actuelles = $progressionModel->getTentatives($etudiant_id, $lecon_id);

if ($actuelles >= $max && $max > 0 && !$progressionModel->estValidee($etudiant_id, $lecon_id)) {
    echo json_encode(['success' => false, 'message' => 'Nombre maximal de tentatives atteint.']);
    exit;
}

$valide = ($score >= $evalInfo['note_de_passage']) ? 1 : 0;
$progressionModel->enregistrerTentative($etudiant_id, $lecon_id, $score, $valide);

$nouveauPourcentage = $progressionModel->calculerProgressionCours($cours_id, $etudiant_id);
$certificatGenere = false;

// Check si module fini et generation certificat
$coursModel = new Cours($pdo);
$cours = $coursModel->trouverParId($cours_id);
if ($cours && $cours['module_id']) {
    $certificatModel = new Certificat($pdo);
    if ($certificatModel->verifierEtGenerer($cours['module_id'], $etudiant_id)) {
        $certificatGenere = true;
    }
}

echo json_encode([
    'success' => true,
    'score' => $score,
    'seuil' => $evalInfo['note_de_passage'],
    'valide' => $valide == 1,
    'nouvelle_progression' => $nouveauPourcentage,
    'tentatives_restantes' => max(0, $max - ($actuelles + 1)),
    'certificat_genere' => $certificatGenere,
    'cours_id' => $cours_id
]);
