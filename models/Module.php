<?php
// ============================================================
// MODEL : MODULE
// ============================================================

class Module
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function creer(string $titre, string $description, int $promoteur_id): int
    {
        $stmt = $this->pdo->prepare("INSERT INTO modules (titre, description, promoteur_id) VALUES (?, ?, ?)");
        $stmt->execute([$titre, $description, $promoteur_id]);
        return (int) $this->pdo->lastInsertId('modules_id_seq');
    }

    public function modifier(int $id, string $titre, string $description): bool
    {
        $stmt = $this->pdo->prepare("UPDATE modules SET titre = ?, description = ? WHERE id = ?");
        return $stmt->execute([$titre, $description, $id]);
    }

    public function supprimer(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM modules WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function trouverParId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM modules WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function listerTous(): array
    {
        $stmt = $this->pdo->query("
            SELECT m.*,
                   COUNT(DISTINCT c.id) as nb_cours,
                   u.nom as promoteur_nom, u.prenom as promoteur_prenom
            FROM modules m
            LEFT JOIN cours c ON c.module_id = m.id
            LEFT JOIN utilisateurs u ON u.id = m.promoteur_id
            GROUP BY m.id, u.nom, u.prenom
            ORDER BY m.date_creation DESC
        ");
        return $stmt->fetchAll();
    }

    public function associerCours(int $module_id, int $cours_id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE cours SET module_id = ? WHERE id = ?");
        return $stmt->execute([$module_id, $cours_id]);
    }

    public function dissocierCours(int $cours_id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE cours SET module_id = NULL WHERE id = ?");
        return $stmt->execute([$cours_id]);
    }

    public function listerCours(int $module_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom,
                   COUNT(l.id) as nb_lecons
            FROM cours c
            LEFT JOIN utilisateurs u ON u.id = c.enseignant_id
            LEFT JOIN lecons l ON l.cours_id = c.id
            WHERE c.module_id = ?
            GROUP BY c.id, u.nom, u.prenom
            ORDER BY c.titre
        ");
        $stmt->execute([$module_id]);
        return $stmt->fetchAll();
    }

    public function listerCoursDisponibles(): array
    {
        $stmt = $this->pdo->query("
            SELECT c.*, u.nom as enseignant_nom, u.prenom as enseignant_prenom
            FROM cours c
            LEFT JOIN utilisateurs u ON u.id = c.enseignant_id
            WHERE c.module_id IS NULL
            ORDER BY c.titre
        ");
        return $stmt->fetchAll();
    }
}
