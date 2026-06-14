<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Utilisateur.php';

exiger_connexion();
verifier_csrf();

$id = (int)$_SESSION['user_id'];
$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$utilisateurModel = new Utilisateur($pdo);


/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

if (empty($nom) || empty($prenom) || empty($email)) {
    set_flash('error', 'Champs obligatoires manquants.');
    rediriger('auth/profile');
}

$utilisateurModel->modifierProfil($id, $nom, $prenom, $email);

if (!empty($password)) {
    if (strlen($password) < 6) {
        set_flash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
    } else {
        $utilisateurModel->modifierMotDePasse($id, $password);
        set_flash('success', 'Profil et mot de passe mis à jour.');
    }
} else {
    set_flash('success', 'Profil mis à jour.');
}

// Mettre à jour la session
$_SESSION['nom'] = $nom;
$_SESSION['prenom'] = $prenom;

rediriger('auth/profile');
