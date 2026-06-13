<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Cours.php';

exiger_role(ROLE_ETUDIANT);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('etudiant/catalogue');
verifier_csrf();

$cours_id = (int)($_POST['cours_id'] ?? 0);

if ($cours_id > 0) {
    $coursModel = new Cours($pdo);
    $coursModel->inscrireEtudiant($_SESSION['user_id'], $cours_id);
    set_flash('success', 'Vous êtes maintenant inscrit à ce cours.');
    rediriger('etudiant/cours', ['id' => $cours_id]);
}

rediriger('etudiant/catalogue');
