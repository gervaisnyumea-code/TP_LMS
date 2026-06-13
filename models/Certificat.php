<?php
// ============================================================
// MODEL : CERTIFICAT
// ============================================================

class Certificat
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Generer un certificat pour un etudiant ayant complete un module.
     */
    public function generer(int $etudiant_id, int $module_id): ?int
    {
        // Verifier qu'il n'existe pas deja
        if ($this->existe($etudiant_id, $module_id)) {
            return null;
        }

        $code = $this->genererCode($etudiant_id, $module_id);
        $stmt = $this->pdo->prepare("
            INSERT INTO certificats (etudiant_id, module_id, code_verification)
            VALUES (?, ?, ?)
        ");
        $stmt->execute([$etudiant_id, $module_id, $code]);
        return (int) $this->pdo->lastInsertId('certificats_id_seq');
    }

    /**
     * Verifier si un certificat existe deja.
     */
    public function existe(int $etudiant_id, int $module_id): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM certificats WHERE etudiant_id = ? AND module_id = ?");
        $stmt->execute([$etudiant_id, $module_id]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Trouver un certificat par ID.
     */
    public function trouverParId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT cert.*, 
                   u.nom as etudiant_nom, u.prenom as etudiant_prenom, u.email as etudiant_email,
                   m.titre as module_titre, m.description as module_description
            FROM certificats cert
            JOIN utilisateurs u ON u.id = cert.etudiant_id
            JOIN modules m ON m.id = cert.module_id
            WHERE cert.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Verifier un certificat par son code de verification.
     */
    public function verifierParCode(string $code): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT cert.*, 
                   u.nom as etudiant_nom, u.prenom as etudiant_prenom,
                   m.titre as module_titre
            FROM certificats cert
            JOIN utilisateurs u ON u.id = cert.etudiant_id
            JOIN modules m ON m.id = cert.module_id
            WHERE cert.code_verification = ?
        ");
        $stmt->execute([$code]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Lister les certificats d'un etudiant.
     */
    public function listerParEtudiant(int $etudiant_id): array
    {
        $stmt = $this->pdo->prepare("
            SELECT cert.*, m.titre as module_titre, m.description as module_description
            FROM certificats cert
            JOIN modules m ON m.id = cert.module_id
            WHERE cert.etudiant_id = ?
            ORDER BY cert.date_delivrance DESC
        ");
        $stmt->execute([$etudiant_id]);
        return $stmt->fetchAll();
    }

    /**
     * Lister tous les certificats (promoteur).
     */
    public function listerTous(): array
    {
        $stmt = $this->pdo->query("
            SELECT cert.*, 
                   u.nom as etudiant_nom, u.prenom as etudiant_prenom,
                   m.titre as module_titre
            FROM certificats cert
            JOIN utilisateurs u ON u.id = cert.etudiant_id
            JOIN modules m ON m.id = cert.module_id
            ORDER BY cert.date_delivrance DESC
        ");
        return $stmt->fetchAll();
    }

    /**
     * Verifier si un module est complet pour un etudiant et generer le certificat si oui.
     */
    public function verifierEtGenerer(int $etudiant_id, int $module_id): ?int
    {
        // Recuperer tous les cours du module
        $stmt = $this->pdo->prepare("SELECT id FROM cours WHERE module_id = ?");
        $stmt->execute([$module_id]);
        $cours_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($cours_ids)) return null;

        // Verifier que chaque cours est a 100%
        $progression = new Progression($this->pdo);
        foreach ($cours_ids as $cours_id) {
            if (!$progression->coursComplet($etudiant_id, $cours_id)) {
                return null;
            }
        }

        // Tous les cours sont complets : generer le certificat
        return $this->generer($etudiant_id, $module_id);
    }

    /**
     * Generer un code de verification unique (SHA-256 + random).
     */
    private function genererCode(int $etudiant_id, int $module_id): string
    {
        $data = $etudiant_id . '-' . $module_id . '-' . time() . '-' . bin2hex(random_bytes(16));
        return hash('sha256', $data);
    }
}
