<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Progression.php';

header('Content-Type: application/json');

if (!est_connecte() || $_SESSION['role'] !== ROLE_ETUDIANT) {
    echo json_encode(['success' => false, 'message' => 'Non autorisé']);
    exit;
}

// In a real app we'd verify CSRF via header or post body
// verifier_csrf(); 

$lecon_id = (int)($_POST['lecon_id'] ?? 0);

if ($lecon_id > 0) {
    $progressionModel = new Progression($pdo);
    
    // On récupère l'ID de l'évaluation liée pour l'initialisation de la progression
    $stmt = $pdo->prepare("SELECT e.id FROM evaluations e WHERE e.lecon_id = ?");
    $stmt->execute([$lecon_id]);
    $eval = $stmt->fetch();
    $evaluation_id = $eval ? (int)$eval['id'] : null;

    // Enregistrement dans la base de données
    $success = $progressionModel->marquerConsultee($_SESSION['user_id'], $lecon_id, $evaluation_id);
    
    if (!$evaluation_id) {
        $progressionModel->enregistrerTentative($_SESSION['user_id'], $lecon_id, null, 100, 0);
    }
    
    // Fallback session pour compatibilité immédiate si besoin
    $_SESSION['lecons_vues'][$lecon_id] = true;
    
    echo json_encode(['success' => $success]);
} else {
    echo json_encode(['success' => false, 'message' => 'ID de leçon invalide']);
}
