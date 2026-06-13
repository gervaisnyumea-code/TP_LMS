<?php
// ============================================================
// SEED -- DONNEES DE DEMONSTRATION (PostgreSQL)
// ============================================================
// Usage : php sql/seed.php

require_once __DIR__ . '/../config/database.php';

$users = [
    ['Admin',   'Systeme', 'promoteur@lms.cm',    'promoteur123',  'promoteur'],
    ['Dupont',  'Jean',    'jean.dupont@lms.cm',   'enseignant123', 'enseignant'],
    ['Kamga',   'Paul',    'paul.kamga@lms.cm',    'etudiant123',   'etudiant'],
];

$stmt = $pdo->prepare("
    INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
    VALUES (?, ?, ?, ?, ?)
    ON CONFLICT (email) DO NOTHING
");

foreach ($users as $u) {
    $hash = password_hash($u[3], PASSWORD_BCRYPT);
    $stmt->execute([$u[0], $u[1], $u[2], $hash, $u[4]]);
}

// Recuperer les IDs
$promoteur = $pdo->query("SELECT id FROM utilisateurs WHERE email = 'promoteur@lms.cm'")->fetch();
$enseignant = $pdo->query("SELECT id FROM utilisateurs WHERE email = 'jean.dupont@lms.cm'")->fetch();

if ($promoteur && $enseignant) {
    // Module de demo
    $pdo->prepare("
        INSERT INTO modules (titre, description, promoteur_id)
        VALUES (?, ?, ?)
        ON CONFLICT DO NOTHING
    ")->execute(['Developpement Web', 'Module de formation au developpement web', $promoteur['id']]);

    $module = $pdo->query("SELECT id FROM modules WHERE titre = 'Developpement Web'")->fetch();

    // Cours de demo
    $pdo->prepare("
        INSERT INTO cours (titre, description, enseignant_id, module_id)
        VALUES (?, ?, ?, ?)
        ON CONFLICT DO NOTHING
    ")->execute(['HTML/CSS Fondamentaux', 'Apprenez les bases du HTML et CSS', $enseignant['id'], $module['id'] ?? null]);

    $cours = $pdo->query("SELECT id FROM cours WHERE titre = 'HTML/CSS Fondamentaux'")->fetch();

    if ($cours) {
        // Lecons de demo
        $lecons = [
            ['Introduction au HTML', 'pdf', 1],
            ['Les selecteurs CSS', 'video', 2],
            ['Mise en page Flexbox', 'pdf', 3],
        ];

        foreach ($lecons as $l) {
            $pdo->prepare("
                INSERT INTO lecons (cours_id, titre, type_contenu, url_contenu, ordre)
                VALUES (?, ?, ?, ?, ?)
                ON CONFLICT DO NOTHING
            ")->execute([$cours['id'], $l[0], $l[1], 'https://via.placeholder.com/demo', $l[2]]);
        }
    }
}

echo "Donnees de demonstration inserees." . PHP_EOL;
echo "  - promoteur@lms.cm / promoteur123" . PHP_EOL;
echo "  - jean.dupont@lms.cm / enseignant123" . PHP_EOL;
echo "  - paul.kamga@lms.cm / etudiant123" . PHP_EOL;
