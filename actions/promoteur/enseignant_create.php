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

exiger_role(ROLE_PROMOTEUR);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') rediriger('promoteur/supervision');
verifier_csrf();

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */


$nom = trim($_POST['nom'] ?? '');
$prenom = trim($_POST['prenom'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? 'enseignant123'; // Mot de passe par défaut

$utilisateurModel = new Utilisateur($pdo);

if ($utilisateurModel->emailExiste($email)) {
    set_flash('error', 'Cet email est déjà utilisé.');
} else {
    $utilisateurModel->inscrire($nom, $prenom, $email, $password, ROLE_ENSEIGNANT);
    set_flash('success', 'Enseignant créé avec succès.');
}

rediriger('promoteur/supervision');
