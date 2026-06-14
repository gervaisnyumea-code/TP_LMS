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
require_once __DIR__ . '/../../models/Lecon.php';

exiger_role(ROLE_ENSEIGNANT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('enseignant/cours');
verifier_csrf();

$cours_id = (int)($_POST['cours_id'] ?? 0);

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$lecons_order_input = $_POST['order'] ?? []; // Tableau [lecon_id => ordre]

asort($lecons_order_input); // Trie par ordre croissant
$lecons_ids = array_keys($lecons_order_input); // Extrait les IDs triés

$coursModel = new Cours($pdo);
if (!$coursModel->appartientA($cours_id, $_SESSION['user_id'])) {
    set_flash('error', 'Acces refuse.');
    rediriger('enseignant/cours');
}

$leconModel = new Lecon($pdo);
if ($leconModel->reordonner($cours_id, $lecons_ids)) {
    set_flash('success', 'Ordre des leçons mis à jour.');
} else {
    set_flash('error', 'Erreur lors de la mise à jour de l\'ordre.');
}

rediriger('enseignant/cours/edit', ['id' => $cours_id]);
