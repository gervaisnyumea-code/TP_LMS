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
    // Si la lecon n'a pas d'évaluation, la vue valide directement
    // Dans le cadre du TP, on enregistre qu'elle est vue dans la session
    $_SESSION['lecons_vues'][$lecon_id] = true;
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
