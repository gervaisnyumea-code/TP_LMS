<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

// ============================================================
// MODEL : PROGRESSION
// ============================================================

class Progression
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Enregistrer ou mettre a jour la progression d'un etudiant sur une lecon.
     * Conserve la meilleure note. Incremente le compteur de tentatives.
     */
    public function enregistrerTentative(int $etudiant_id, int $lecon_id, ?int $evaluation_id, int $score, int $note_de_passage): array
    {
        $valide = ($score >= $note_de_passage) ? 1 : 0;

        $existing = $this->trouverProgression($etudiant_id, $lecon_id);

        if ($existing) {
            $stmt = $this->pdo->prepare("
                UPDATE progressions
                SET note_obtenue = GREATEST(note_obtenue, ?),
                    valide = CASE WHEN ?::boolean = TRUE THEN TRUE ELSE valide END,
                    nb_tentatives = nb_tentatives + 1,
                    derniere_tentative = CURRENT_TIMESTAMP
                WHERE etudiant_id = ? AND lecon_id = ?
            ");
            $stmt->execute([$score, $valide, $etudiant_id, $lecon_id]);
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO progressions (etudiant_id, lecon_id, evaluation_id, note_obtenue, valide, nb_tentatives, derniere_tentative)
                VALUES (?, ?, ?, ?, ?::boolean, 1, CURRENT_TIMESTAMP)
            ");
            $stmt->execute([$etudiant_id, $lecon_id, $evaluation_id, $score, $valide]);
        }

        return $this->trouverProgression($etudiant_id, $lecon_id);
    }

    /**
     * Marquer une lecon comme consultee par l'etudiant.
     * Cree la progression si elle n'existe pas encore.
     */
    public function marquerConsultee(int $etudiant_id, int $lecon_id, ?int $evaluation_id): bool
    {
        $existing = $this->trouverProgression($etudiant_id, $lecon_id);

        if ($existing) {
            $stmt = $this->pdo->prepare("UPDATE progressions SET lecon_consultee = TRUE WHERE etudiant_id = ? AND lecon_id = ?");
            return $stmt->execute([$etudiant_id, $lecon_id]);
        } else {
            $stmt = $this->pdo->prepare("
                INSERT INTO progressions (etudiant_id, lecon_id, evaluation_id, note_obtenue, valide, nb_tentatives, lecon_consultee)
                VALUES (?, ?, ?, 0, FALSE, 0, TRUE)
            ");
            return $stmt->execute([$etudiant_id, $lecon_id, $evaluation_id]);
        }
    }

    /**
     * Trouver la progression d'un etudiant sur une lecon.
     */
    public function trouverProgression(int $etudiant_id, int $lecon_id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM progressions WHERE etudiant_id = ? AND lecon_id = ?");
        $stmt->execute([$etudiant_id, $lecon_id]);
        return $stmt->fetch() ?: null;
    }


/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

    /**
     * Calculer la progression globale d'un etudiant sur un cours (en %).
     */
    public function calculerProgressionCours(int $etudiant_id, int $cours_id): int
    {
        // Nombre total de lecons du cours
        $stmt_total = $this->pdo->prepare("SELECT COUNT(*) FROM lecons WHERE cours_id = ?");
        $stmt_total->execute([$cours_id]);
        $total = (int) $stmt_total->fetchColumn();

        if ($total === 0) return 0;

        // Nombre de lecons validees
        $stmt_validees = $this->pdo->prepare("
            SELECT COUNT(*) FROM progressions p
            JOIN lecons l ON l.id = p.lecon_id
            WHERE p.etudiant_id = ? AND l.cours_id = ? AND p.valide = TRUE
        ");
        $stmt_validees->execute([$etudiant_id, $cours_id]);
        $validees = (int) $stmt_validees->fetchColumn();

        return (int) round(($validees / $total) * 100);
    }

    /**
     * Lister les progressions d'un etudiant pour un cours donne.
     */
    public function listerParCours(int $etudiant_id, int $cours_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT l.id as lecon_id, l.titre as lecon_titre, l.ordre, l.type_contenu, l.duree_estimee,
                   p.note_obtenue, COALESCE(p.valide, false) as valide, p.nb_tentatives, p.derniere_tentative,
                   e.id as evaluation_id, e.note_de_passage, e.tentatives_max
            FROM lecons l
            LEFT JOIN progressions p ON p.lecon_id = l.id AND p.etudiant_id = ?
            LEFT JOIN evaluations e ON e.lecon_id = l.id
            WHERE l.cours_id = ?
            ORDER BY l.ordre ASC
        ");
        $stmt->execute([$etudiant_id, $cours_id]);
        return $stmt->fetchAll();
    }

    /**
     * Verifier si un etudiant peut passer un quiz (tentatives restantes).
     */
    public function peutTenterQuiz(int $etudiant_id, int $lecon_id, int $tentatives_max): bool
    {
        $prog = $this->trouverProgression($etudiant_id, $lecon_id);
        if (!$prog) return true;  // Premiere tentative
        if ($prog['valide'] == 1) return false;  // Deja valide
        return $prog['nb_tentatives'] < $tentatives_max;
    }

    /**
     * Verifier si toutes les lecons d'un cours sont validees par un etudiant.
     */
    public function coursComplet(int $etudiant_id, int $cours_id): bool
    {
        return $this->calculerProgressionCours($etudiant_id, $cours_id) === 100;
    }

    /**
     * Statistiques d'un cours pour l'enseignant.
     */
    public function statsParCours(int $cours_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT l.id, l.titre, l.ordre,
                   COUNT(DISTINCT p.etudiant_id) as nb_tentatives_total,
                   SUM(CASE WHEN p.valide = TRUE THEN 1 ELSE 0 END) as nb_validations,
                   ROUND(AVG(p.note_obtenue), 1) as note_moyenne
            FROM lecons l
            LEFT JOIN progressions p ON p.lecon_id = l.id
            WHERE l.cours_id = ?
            GROUP BY l.id
            ORDER BY l.ordre ASC
        ");
        $stmt->execute([$cours_id]);
        return $stmt->fetchAll();
    }
}
