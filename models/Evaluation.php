<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

// ============================================================
// MODEL : EVALUATION
// ============================================================

class Evaluation
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function creer(int $lecon_id, string $titre, int $note_de_passage = 70, ?int $duree_limite = null, int $tentatives_max = 3): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO evaluations (lecon_id, titre, note_de_passage, duree_limite, tentatives_max)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$lecon_id, $titre, $note_de_passage, $duree_limite, $tentatives_max]);
        return (int) $this->pdo->lastInsertId('evaluations_id_seq');
    }

    public function modifier(int $id, string $titre, int $note_de_passage, ?int $duree_limite, int $tentatives_max): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE evaluations SET titre = ?, note_de_passage = ?, duree_limite = ?, tentatives_max = ?
            WHERE id = ?
        ");
        return $stmt->execute([$titre, $note_de_passage, $duree_limite, $tentatives_max, $id]);
    }

    public function trouverParId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT e.*, l.cours_id, l.titre as lecon_titre
            FROM evaluations e
            JOIN lecons l ON l.id = e.lecon_id
            WHERE e.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function trouverParLecon(int $lecon_id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM evaluations WHERE lecon_id = ?");
        $stmt->execute([$lecon_id]);

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

        return $stmt->fetch() ?: null;
    }

    // ---- QUESTIONS ----

    public function ajouterQuestion(int $evaluation_id, string $question_text, array $options, string $reponse_correcte, int $ordre): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO questions (evaluation_id, question_text, options_json, reponse_correcte, ordre)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$evaluation_id, $question_text, json_encode($options), $reponse_correcte, $ordre]);
        return (int) $this->pdo->lastInsertId('questions_id_seq');
    }

    public function modifierQuestion(int $id, string $question_text, array $options, string $reponse_correcte, int $ordre): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE questions SET question_text = ?, options_json = ?, reponse_correcte = ?, ordre = ?
            WHERE id = ?
        ");
        return $stmt->execute([$question_text, json_encode($options), $reponse_correcte, $ordre, $id]);
    }

    public function supprimerQuestion(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM questions WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function listerQuestions(int $evaluation_id): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM questions WHERE evaluation_id = ? ORDER BY ordre ASC");
        $stmt->execute([$evaluation_id]);
        $questions = $stmt->fetchAll();
        foreach ($questions as &$q) {
            $q['options'] = json_decode($q['options_json'], true);
        }
        return $questions;
    }

    public function compterQuestions(int $evaluation_id): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM questions WHERE evaluation_id = ?");
        $stmt->execute([$evaluation_id]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Verifier si une evaluation a deja des questions.
     */
    public function aDesQuestions(int $evaluation_id): bool
    {
        return $this->compterQuestions($evaluation_id) > 0;
    }
}
