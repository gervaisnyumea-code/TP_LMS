<?php
// ============================================================
// MODEL : UTILISATEUR
// ============================================================

class Utilisateur
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Inscription d'un nouvel utilisateur.
     */
    public function inscrire(string $nom, string $prenom, string $email, string $mot_de_passe, string $role = 'etudiant'): int
    {
        $hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);
        $stmt = $this->pdo->prepare("
            INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$nom, $prenom, $email, $hash, $role]);
        return (int) $this->pdo->lastInsertId('utilisateurs_id_seq');
    }

    /**
     * Authentification par email et mot de passe.
     * Retourne les donnees utilisateur ou null.
     */
    public function authentifier(string $email, string $mot_de_passe): ?array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM utilisateurs WHERE email = ? AND actif = TRUE");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            unset($user['mot_de_passe']);
            return $user;
        }
        return null;
    }

    /**
     * Recuperer un utilisateur par ID.
     */
    public function trouverParId(int $id): ?array
    {
        $stmt = $this->pdo->prepare("SELECT id, nom, prenom, email, role, actif, date_creation FROM utilisateurs WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Verifier si un email existe deja.
     */
    public function emailExiste(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Lister les utilisateurs par role.
     */
    public function listerParRole(string $role): array
    {
        $stmt = $this->pdo->prepare("SELECT id, nom, prenom, email, actif, date_creation FROM utilisateurs WHERE role = ? ORDER BY nom, prenom");
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }

    /**
     * Lister tous les utilisateurs.
     */
    public function listerTous(): array
    {
        $stmt = $this->pdo->query("SELECT id, nom, prenom, email, role, actif, date_creation FROM utilisateurs ORDER BY role, nom");
        return $stmt->fetchAll();
    }

    /**
     * Activer ou desactiver un utilisateur.
     */
    public function toggleActif(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE utilisateurs SET actif = CASE WHEN actif = TRUE THEN FALSE ELSE TRUE END WHERE id = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Compter les utilisateurs par role.
     */
    public function compterParRole(): array
    {
        $stmt = $this->pdo->query("SELECT role, COUNT(*) as total FROM utilisateurs GROUP BY role");
        $result = [];
        foreach ($stmt->fetchAll() as $row) {
            $result[$row['role']] = (int) $row['total'];
        }
        return $result;
    }
}
