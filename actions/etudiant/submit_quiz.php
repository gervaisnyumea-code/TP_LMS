<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Evaluation.php';
require_once __DIR__ . '/../../models/Progression.php';
require_once __DIR__ . '/../../models/Certificat.php';
require_once __DIR__ . '/../../models/Cours.php';

header('Content-Type: application/json');

if (!est_connecte() || $_SESSION['role'] !== ROLE_ETUDIANT || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Acces refuse.']);
    exit;
}

$evaluation_id = (int)($_POST['evaluation_id'] ?? 0);
$cours_id = (int)($_POST['cours_id'] ?? 0);
$etudiant_id = (int)$_SESSION['user_id'];

if (!$evaluation_id || !$cours_id) {
    echo json_encode(['success' => false, 'message' => 'Donnees invalides.']);
    exit;
}

$evaluationModel = new Evaluation($pdo);
$evalInfo = $evaluationModel->trouverParId($evaluation_id);
if (!$evalInfo) {
    echo json_encode(['success' => false, 'message' => 'Evaluation introuvable.']);
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
    $score = (int)round(($bonnesReponses / $totalQuestions) * 100);
}


/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$progressionModel = new Progression($pdo);
$lecon_id = (int)$evalInfo['lecon_id'];

// Verification tentatives
$prog = $progressionModel->trouverProgression($etudiant_id, $lecon_id);
$max = (int)($evalInfo['tentatives_max'] ?? 3);
$actuelles = $prog ? (int)$prog['nb_tentatives'] : 0;
$estValidee = $prog ? ($prog['valide'] == 1) : false;

if ($actuelles >= $max && $max > 0 && !$estValidee) {
    echo json_encode(['success' => false, 'message' => 'Nombre maximal de tentatives atteint.']);
    exit;
}

// Enregistrer la tentative
$noteDePassage = (int)$evalInfo['note_de_passage'];
$progressionModel->enregistrerTentative($etudiant_id, $lecon_id, $evaluation_id, $score, $noteDePassage);

$valide = ($score >= $noteDePassage);
$nouveauPourcentage = $progressionModel->calculerProgressionCours($etudiant_id, $cours_id);

// Verification certificat
$certificatGenere = false;
$coursModel = new Cours($pdo);
$cours = $coursModel->trouverParId($cours_id);
if ($cours && $cours['module_id'] && $nouveauPourcentage >= 100) {
    $certificatModel = new Certificat($pdo);
    $certifId = $certificatModel->verifierEtGenerer($etudiant_id, (int)$cours['module_id']);
    if ($certifId) {
        $certificatGenere = true;
    }
}

// Notification
require_once __DIR__ . '/../../services/EmailService.php';
$message = $valide ? "Félicitations, vous avez validé le quiz avec un score de $score%." : "Dommage, vous avez obtenu $score%. Veuillez retenter.";
EmailService::send(
    $_SESSION['email'],
    $_SESSION['prenom'] . ' ' . $_SESSION['nom'],
    'Résultat du quiz : ' . $evalInfo['titre'],
    '<h1>Résultat</h1><p>' . $message . '</p>'
);

echo json_encode([
    'success' => true,
    'score' => $score,
    'seuil' => $noteDePassage,
    'valide' => $valide,
    'nouvelle_progression' => $nouveauPourcentage,
    'tentatives_restantes' => max(0, $max - ($actuelles + 1)),
    'certificat_genere' => $certificatGenere,
    'cours_id' => $cours_id,
]);
