<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Utilisateur.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    rediriger('register');
}

verifier_csrf();

$prenom = trim($_POST['prenom'] ?? '');
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$password_confirm = $_POST['password_confirm'] ?? '';

// Validations basiques
if (empty($prenom) || empty($nom) || empty($email) || empty($password)) {
    set_flash('error', 'Tous les champs sont obligatoires.');
    rediriger('register');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    set_flash('error', 'Format d\'email invalide.');
    rediriger('register');
}

if (strlen($password) < 6) {
    set_flash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
    rediriger('register');
}

if ($password !== $password_confirm) {
    set_flash('error', 'Les mots de passe ne correspondent pas.');
    rediriger('register');
}

$utilisateurModel = new Utilisateur($pdo);

if ($utilisateurModel->emailExiste($email)) {
    set_flash('error', 'Cet email est déjà utilisé.');
    rediriger('register');
}

// Inscription forcé en tant qu'étudiant
$userId = $utilisateurModel->inscrire($nom, $prenom, $email, $password, ROLE_ETUDIANT);

if ($userId) {
    // Notification
    require_once __DIR__ . '/../../services/EmailService.php';
    EmailService::send(
        $email,
        $prenom . ' ' . $nom,
        'Bienvenue sur ' . APP_NAME,
        '<h1>Bienvenue !</h1><p>Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.</p>'
    );

    set_flash('success', 'Votre compte a été créé avec succès. Vous pouvez maintenant vous connecter.');
    rediriger('login');
} else {
    set_flash('error', 'Une erreur est survenue lors de l\'inscription.');
    rediriger('register');
}
