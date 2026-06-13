<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Utilisateur.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    rediriger('login');
}

verifier_csrf();

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

if (empty($email) || empty($password)) {
    set_flash('error', 'Veuillez remplir tous les champs.');
    rediriger('login');
}

$utilisateurModel = new Utilisateur($pdo);
$user = $utilisateurModel->authentifier($email, $password);

if ($user) {
    // Regenerer l'ID de session pour la securite
    session_regenerate_id(true);
    
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['nom'] = $user['nom'];
    $_SESSION['prenom'] = $user['prenom'];
    $_SESSION['email'] = $user['email'];
    
    // Rediriger vers le dashboard approprié
    rediriger($user['role'] . '/dashboard');
} else {
    set_flash('error', 'Identifiants incorrects ou compte inactif.');
    rediriger('login');
}
