<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Utilisateur.php';
require_once __DIR__ . '/../../models/Cours.php';
require_once __DIR__ . '/../../models/Module.php';
require_once __DIR__ . '/../../models/Certificat.php';

header('Content-Type: application/json');
exiger_role(ROLE_PROMOTEUR);

$comptes = (new Utilisateur($pdo))->compterParRole();
$cours = (new Cours($pdo))->listerTous();
$modules = (new Module($pdo))->listerTous();
$certificats = (new Certificat($pdo))->listerTous();

echo json_encode([
    'success' => true,
    'stats' => [
        'etudiants' => $comptes['etudiant'] ?? 0,
        'enseignants' => $comptes['enseignant'] ?? 0,
        'promoteurs' => $comptes['promoteur'] ?? 0,
        'nb_cours' => count($cours),
        'nb_modules' => count($modules),
        'nb_certificats' => count($certificats),
    ],
    'derniers_certificats' => array_slice($certificats, 0, 10),
    'modules' => $modules,
]);
