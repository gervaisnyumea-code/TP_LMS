<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

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
    $stmt = $pdo->prepare("SELECT id FROM modules WHERE titre = ? AND promoteur_id = ?");
    $stmt->execute(['Developpement Web', $promoteur['id']]);
    $module = $stmt->fetch();

    if (!$module) {
        $stmt = $pdo->prepare("
            INSERT INTO modules (titre, description, promoteur_id)
            VALUES (?, ?, ?)
            RETURNING id
        ");
        $stmt->execute(['Developpement Web', 'Module de formation au developpement web', $promoteur['id']]);
        $module = $stmt->fetch();
    }

    // Cours de demo
    $stmt = $pdo->prepare("SELECT id FROM cours WHERE titre = ? AND enseignant_id = ?");
    $stmt->execute(['HTML/CSS Fondamentaux', $enseignant['id']]);
    $cours = $stmt->fetch();

    if (!$cours) {
        $stmt = $pdo->prepare("
            INSERT INTO cours (titre, description, enseignant_id, module_id)
            VALUES (?, ?, ?, ?)
            RETURNING id
        ");
        $stmt->execute(['HTML/CSS Fondamentaux', 'Apprenez les bases du HTML et CSS', $enseignant['id'], $module['id'] ?? null]);
        $cours = $stmt->fetch();
    }

    if ($cours) {
        // Lecons de demo
        $lecons = [
            [
                'titre' => 'Introduction au HTML',
                'type' => 'pdf',
                'ordre' => 1,
                'evaluation' => 'Quiz : Introduction au HTML',
                'questions' => [
                    ['Que signifie HTML ?', ['A' => 'HyperText Markup Language', 'B' => 'High Text Machine Language', 'C' => 'Hyper Tool Multi Link', 'D' => 'Home Text Markup Language'], 'A'],
                    ['Quelle balise contient le contenu visible ?', ['A' => '<head>', 'B' => '<body>', 'C' => '<meta>', 'D' => '<title>'], 'B'],
                    ['Quel attribut rend un champ obligatoire ?', ['A' => 'checked', 'B' => 'disabled', 'C' => 'required', 'D' => 'readonly'], 'C'],
                ],

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

            ],
            [
                'titre' => 'Les selecteurs CSS',
                'type' => 'video',
                'ordre' => 2,
                'evaluation' => 'Quiz : Les selecteurs CSS',
                'questions' => [
                    ['Quel selecteur cible une classe ?', ['A' => '#menu', 'B' => '.menu', 'C' => 'menu()', 'D' => '@menu'], 'B'],
                    ['Quelle propriete change la couleur du texte ?', ['A' => 'font-color', 'B' => 'text-style', 'C' => 'color', 'D' => 'background'], 'C'],
                    ['Quel selecteur cible un identifiant ?', ['A' => '#header', 'B' => '.header', 'C' => 'header[]', 'D' => '*header'], 'A'],
                ],
            ],
            [
                'titre' => 'Mise en page Flexbox',
                'type' => 'pdf',
                'ordre' => 3,
                'evaluation' => 'Quiz : Mise en page Flexbox',
                'questions' => [
                    ['Quelle declaration active Flexbox ?', ['A' => 'display: grid', 'B' => 'display: block', 'C' => 'display: flex', 'D' => 'position: flex'], 'C'],
                    ['Quelle propriete aligne sur l axe principal ?', ['A' => 'justify-content', 'B' => 'align-title', 'C' => 'text-align', 'D' => 'place-font'], 'A'],
                    ['Quelle propriete permet le retour a la ligne ?', ['A' => 'flex-repeat', 'B' => 'flex-wrap', 'C' => 'line-wrap', 'D' => 'wrap-content'], 'B'],
                ],
            ],
        ];

        foreach ($lecons as $l) {
            $stmt = $pdo->prepare("SELECT id FROM lecons WHERE cours_id = ? AND titre = ?");
            $stmt->execute([$cours['id'], $l['titre']]);
            $lecon = $stmt->fetch();

            if (!$lecon) {
                $stmt = $pdo->prepare("
                    INSERT INTO lecons (cours_id, titre, type_contenu, url_contenu, ordre)
                    VALUES (?, ?, ?, ?, ?)
                    RETURNING id
                ");
                $stmt->execute([$cours['id'], $l['titre'], $l['type'], 'https://via.placeholder.com/demo', $l['ordre']]);
                $lecon = $stmt->fetch();
            }

            $stmt = $pdo->prepare("SELECT id FROM evaluations WHERE lecon_id = ?");
            $stmt->execute([$lecon['id']]);
            $evaluation = $stmt->fetch();

            if (!$evaluation) {
                $stmt = $pdo->prepare("
                    INSERT INTO evaluations (lecon_id, titre, note_de_passage, tentatives_max)
                    VALUES (?, ?, 70, 3)
                    RETURNING id
                ");
                $stmt->execute([$lecon['id'], $l['evaluation']]);
                $evaluation = $stmt->fetch();
            }

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM questions WHERE evaluation_id = ?");
            $stmt->execute([$evaluation['id']]);
            if ((int) $stmt->fetchColumn() === 0) {
                $stmt = $pdo->prepare("
                    INSERT INTO questions (evaluation_id, question_text, options_json, reponse_correcte, ordre)
                    VALUES (?, ?, ?::jsonb, ?, ?)
                ");

                foreach ($l['questions'] as $index => $question) {
                    $stmt->execute([
                        $evaluation['id'],
                        $question[0],
                        json_encode($question[1]),
                        $question[2],
                        $index + 1,
                    ]);
                }
            }
        }
    }
}

echo "Donnees de demonstration inserees." . PHP_EOL;
echo "  - promoteur@lms.cm / promoteur123" . PHP_EOL;
echo "  - jean.dupont@lms.cm / enseignant123" . PHP_EOL;
echo "  - paul.kamga@lms.cm / etudiant123" . PHP_EOL;
