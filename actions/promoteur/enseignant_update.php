<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Utilisateur.php';

exiger_role(ROLE_PROMOTEUR);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('promoteur/supervision');
verifier_csrf();

$id = (int)$_POST['id'];
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$utilisateurModel = new Utilisateur($pdo);

$utilisateurModel->modifierProfil($id, $nom, $prenom, $email);

if (!empty($password)) {
    $utilisateurModel->modifierMotDePasse($id, $password);
}

set_flash('success', 'Informations enseignant mises à jour.');
rediriger('promoteur/supervision');
