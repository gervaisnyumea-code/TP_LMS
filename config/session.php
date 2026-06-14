<?php
// ============================================================
// GESTION DE SESSION ET HELPERS D'AUTHENTIFICATION
// ============================================================

require_once __DIR__ . '/constants.php';

// -- Initialisation session securisee
if (session_status() === PHP_SESSION_NONE) {
    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

// -- Timeout d'inactivite
if (isset($_SESSION['last_activity'])) {
    if (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT) {
        session_unset();
        session_destroy();
        session_start();
    }
}
$_SESSION['last_activity'] = time();

// -- Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// ============================================================
// FONCTIONS HELPERS
// ============================================================

/**
 * Verifie si un utilisateur est connecte.
 */
function est_connecte(): bool
{
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Verifie le role de l'utilisateur connecte.
 */
function a_le_role(string $role): bool
{
    return est_connecte() && $_SESSION['role'] === $role;
}

/**
 * Exige qu'un utilisateur soit connecte. Redirige vers login sinon.
 */
function exiger_connexion(): void
{
    if (!est_connecte()) {
        header('Location: ' . base_url('index.php?page=login'));
        exit;
    }
}

/**
 * Exige un role specifique. Retourne 403 si non autorise.
 */
function exiger_role(string $role): void
{
    exiger_connexion();
    if (!a_le_role($role)) {
        http_response_code(403);
        exit('Acces refuse.');
    }
}

/**
 * Genere un champ hidden CSRF pour les formulaires.
 */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') . '">';
}

/**
 * Verifie le token CSRF soumis. Arrete l'execution si invalide.
 */
function verifier_csrf(): void
{
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        http_response_code(403);
        exit('Erreur de securite CSRF.');
    }
}

/**
 * Echappe une variable pour l'affichage HTML (anti-XSS).
 */
function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

/**
 * Redirige vers une page.
 */
function rediriger(string $page, array $params = []): void
{
    $url = base_url('index.php?page=' . $page);
    if (!empty($params)) {
        $url .= '&' . http_build_query($params);
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Stocke un message flash en session.
 */
function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Recupere et supprime le message flash.
 */
function get_flash(): ?array
{
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Retourne l'URL de base de l'application.
 */
function base_url(string $path = ''): string
{
    $base = rtrim(APP_BASE_URL, '/');
    if ($path === '') return $base;
    return $base . '/' . ltrim($path, '/');
}
