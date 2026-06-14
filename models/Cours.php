<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

// ============================================================
// MODEL : COURS
// ============================================================

class Cours
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function creer(string $titre, string $description, int $enseignant_id, ?int $module_id = null): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO cours (titre, description, enseignant_id, module_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$titre, $description, $enseignant_id, $module_id]);
        return (int) $this->pdo->lastInsertId('cours_id_seq');
    }

    public function modifier(int $id, string $titre, string $description, bool $visible): bool
    {
        $stmt = $this->pdo->prepare("UPDATE cours SET titre = ?, description = ?, visible = ? WHERE id = ?");
        return $stmt->execute([$titre, $description, $visible, $id]);
    }

    public function supprimer(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM cours WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function trouverParId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
                   m.titre as module_titre,
                   COUNT(l.id) as nb_lecons
            FROM cours c
            LEFT JOIN utilisateurs u ON u.id = c.enseignant_id
            LEFT JOIN modules m ON m.id = c.module_id
            LEFT JOIN lecons l ON l.cours_id = c.id
            WHERE c.id = ?
            GROUP BY c.id, u.nom, u.prenom, m.titre
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function listerParEnseignant(int $enseignant_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, COUNT(l.id) as nb_lecons,
                   m.titre as module_titre,
                   (SELECT COUNT(*) FROM inscriptions i WHERE i.cours_id = c.id) as nb_inscrits
            FROM cours c
            LEFT JOIN lecons l ON l.cours_id = c.id
            LEFT JOIN modules m ON m.id = c.module_id
            WHERE c.enseignant_id = ?
            GROUP BY c.id, m.titre
            ORDER BY c.date_creation DESC
        ");
        $stmt->execute([$enseignant_id]);
        return $stmt->fetchAll();
    }

    public function listerVisibles(): array
    {
        $stmt = $this->pdo->query("
            SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
                   COUNT(l.id) as nb_lecons,
                   m.titre as module_titre

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

            FROM cours c
            LEFT JOIN utilisateurs u ON u.id = c.enseignant_id
            LEFT JOIN lecons l ON l.cours_id = c.id
            LEFT JOIN modules m ON m.id = c.module_id
            WHERE c.visible = TRUE
            GROUP BY c.id, u.nom, u.prenom, m.titre
            ORDER BY c.date_creation DESC
        ");
        return $stmt->fetchAll();
    }

    public function listerTous(): array
    {
        $stmt = $this->pdo->query("
            SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
                   COUNT(DISTINCT l.id) as nb_lecons,
                   COUNT(DISTINCT i.id) as nb_inscrits,
                   m.titre as module_titre
            FROM cours c
            LEFT JOIN utilisateurs u ON u.id = c.enseignant_id
            LEFT JOIN lecons l ON l.cours_id = c.id
            LEFT JOIN inscriptions i ON i.cours_id = c.id
            LEFT JOIN modules m ON m.id = c.module_id
            GROUP BY c.id, u.nom, u.prenom, m.titre
            ORDER BY c.date_creation DESC
        ");
        return $stmt->fetchAll();
    }

    public function assignerEnseignant(int $cours_id, int $enseignant_id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE cours SET enseignant_id = ? WHERE id = ?");
        return $stmt->execute([$enseignant_id, $cours_id]);
    }

    public function appartientA(int $cours_id, int $enseignant_id): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM cours WHERE id = ? AND enseignant_id = ?");
        $stmt->execute([$cours_id, $enseignant_id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function estInscrit(int $etudiant_id, int $cours_id): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM inscriptions WHERE etudiant_id = ? AND cours_id = ?");
        $stmt->execute([$etudiant_id, $cours_id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function inscrireEtudiant(int $etudiant_id, int $cours_id): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO inscriptions (etudiant_id, cours_id) VALUES (?, ?) ON CONFLICT DO NOTHING");
        return $stmt->execute([$etudiant_id, $cours_id]);
    }

    public function listerCoursEtudiant(int $etudiant_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
                   COUNT(DISTINCT l.id) as nb_lecons,
                   i.date_inscription,
                   m.titre as module_titre,
                   (
                       SELECT COALESCE(ROUND(COUNT(p.id) * 100.0 / NULLIF(COUNT(l2.id), 0)), 0)
                       FROM lecons l2
                       LEFT JOIN progressions p ON p.lecon_id = l2.id AND p.etudiant_id = ? AND p.valide = TRUE
                       WHERE l2.cours_id = c.id
                   ) as progression
            FROM inscriptions i
            JOIN cours c ON c.id = i.cours_id
            LEFT JOIN utilisateurs u ON u.id = c.enseignant_id
            LEFT JOIN lecons l ON l.cours_id = c.id
            LEFT JOIN modules m ON m.id = c.module_id
            WHERE i.etudiant_id = ?
            GROUP BY c.id, u.nom, u.prenom, i.date_inscription, m.titre
            ORDER BY i.date_inscription DESC
        ");
        $stmt->execute([$etudiant_id, $etudiant_id]);
        return $stmt->fetchAll();
    }
}
