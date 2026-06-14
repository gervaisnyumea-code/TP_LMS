<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Utilisateur.php';

exiger_role(ROLE_PROMOTEUR);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('promoteur/supervision');
verifier_csrf();

$id = (int)$_POST['id'];

$utilisateurModel = new Utilisateur($pdo);
$utilisateurModel->supprimer($id);

set_flash('success', 'Enseignant supprimé.');
rediriger('promoteur/supervision');
