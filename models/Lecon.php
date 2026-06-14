<?php
// ============================================================
// MODEL : LECON
// ============================================================

require_once __DIR__ . '/CloudinaryHelper.php';

class Lecon
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function creer(int $cours_id, string $titre, string $description, string $type_contenu, string $url_contenu, ?int $duree_estimee, int $ordre): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO lecons (cours_id, titre, description, type_contenu, url_contenu, duree_estimee, ordre)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$cours_id, $titre, $description, $type_contenu, $url_contenu, $duree_estimee, $ordre]);
        return (int) $this->pdo->lastInsertId('lecons_id_seq');
    }

    public function modifier(int $id, string $titre, string $description, ?int $duree_estimee, int $ordre): bool
    {
        $stmt = $this->pdo->prepare("UPDATE lecons SET titre = ?, description = ?, duree_estimee = ?, ordre = ? WHERE id = ?");
        return $stmt->execute([$titre, $description, $duree_estimee, $ordre, $id]);
    }

    public function supprimer(int $id): bool
    {
        $lecon = $this->trouverParId($id);
        if ($lecon && !empty($lecon['cloudinary_id'])) {
            $resource_type = ($lecon['type_contenu'] === 'pdf') ? 'raw' : 'video';
            CloudinaryHelper::destroy($lecon['cloudinary_id'], $resource_type);
        } elseif ($lecon && !empty($lecon['url_contenu']) && file_exists($lecon['url_contenu'])) {
            unlink($lecon['url_contenu']);
        }
        $stmt = $this->pdo->prepare("DELETE FROM lecons WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function trouverParId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT l.*, c.titre as cours_titre, c.enseignant_id
            FROM lecons l
            JOIN cours c ON c.id = l.cours_id
            WHERE l.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Lister les lecons d'un cours, ordonnees.
     */
    public function listerParCours(int $cours_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT l.*,
                   e.id as evaluation_id, e.titre as evaluation_titre,
                   (SELECT COUNT(*) FROM questions q WHERE q.evaluation_id = e.id) as nb_questions
            FROM lecons l
            LEFT JOIN evaluations e ON e.lecon_id = l.id
            WHERE l.cours_id = ?
            ORDER BY l.ordre ASC
        ");
        $stmt->execute([$cours_id]);
        return $stmt->fetchAll();
    }

    /**
     * Réordonner les leçons d'un cours en mettant à jour leurs indices.
     */
    public function reordonner(int $cours_id, array $lecons_order): bool
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE lecons SET ordre = ? WHERE id = ? AND cours_id = ?");
            foreach ($lecons_order as $ordre => $id) {
                $stmt->execute([$ordre + 1, $id, $cours_id]);
            }
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }

    /**
     * Prochain ordre disponible pour un cours.
     */
    public function prochainOrdre(int $cours_id): int
    {
        $stmt = $this->pdo->prepare("SELECT COALESCE(MAX(ordre), 0) + 1 FROM lecons WHERE cours_id = ?");
        $stmt->execute([$cours_id]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Compter les lecons d'un cours.
     */
    public function compterParCours(int $cours_id): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM lecons WHERE cours_id = ?");
        $stmt->execute([$cours_id]);
        return (int) $stmt->fetchColumn();
    }
}
