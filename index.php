<?php
// ============================================================
// ROUTEUR PRINCIPAL -- LMS CAMEROUN
// ============================================================

require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/database.php';

// -- Charger tous les models
require_once __DIR__ . '/models/Utilisateur.php';
require_once __DIR__ . '/models/Module.php';
require_once __DIR__ . '/models/Cours.php';
require_once __DIR__ . '/models/Lecon.php';
require_once __DIR__ . '/models/Evaluation.php';
require_once __DIR__ . '/models/Progression.php';
require_once __DIR__ . '/models/Certificat.php';

// -- Instancier les models
$utilisateurModel  = new Utilisateur($pdo);
$moduleModel       = new Module($pdo);
$coursModel        = new Cours($pdo);
$leconModel        = new Lecon($pdo);
$evaluationModel   = new Evaluation($pdo);
$progressionModel  = new Progression($pdo);
$certificatModel   = new Certificat($pdo);

// -- Determiner la page demandee
$page = $_GET['page'] ?? '';

// -- Routes publiques (sans authentification)
$routes_publiques = ['login', 'register', 'certificat/verifier'];

// -- Si pas de page et connecte, rediriger vers le dashboard du role
if ($page === '' && est_connecte()) {
    $page = $_SESSION['role'] . '/dashboard';
    header('Location: ' . APP_BASE_URL . '/index.php?page=' . $page);
    exit;
}

// -- Si pas de page et non connecte, aller vers login
if ($page === '' && !est_connecte()) {
    $page = 'login';
}

// -- Verifier l'authentification pour les routes non publiques
if (!in_array($page, $routes_publiques) && !est_connecte()) {
    // Permettre les actions auth
    if (!in_array($page, ['login', 'register'])) {
        header('Location: ' . APP_BASE_URL . '/index.php?page=login');
        exit;
    }
}

// -- Dispatch des pages
switch ($page) {
    // ---- AUTH ----
    case 'login':
        require __DIR__ . '/views/auth/login.php';
        break;
    case 'register':
        require __DIR__ . '/views/auth/register.php';
        break;
    case 'logout':
        require __DIR__ . '/actions/auth/logout.php';
        break;

    // ---- ETUDIANT ----
    case 'etudiant/dashboard':
        exiger_role(ROLE_ETUDIANT);
        require __DIR__ . '/views/etudiant/dashboard.php';
        break;
    case 'etudiant/catalogue':
        exiger_role(ROLE_ETUDIANT);
        require __DIR__ . '/views/etudiant/cours_liste.php';
        break;
    case 'etudiant/cours':
        exiger_role(ROLE_ETUDIANT);
        require __DIR__ . '/views/etudiant/cours_detail.php';
        break;
    case 'etudiant/lecon':
        exiger_role(ROLE_ETUDIANT);
        require __DIR__ . '/views/etudiant/lecon_player.php';
        break;
    case 'etudiant/certificats':
        exiger_role(ROLE_ETUDIANT);
        require __DIR__ . '/views/etudiant/certificats.php';
        break;

    // ---- ENSEIGNANT ----
    case 'enseignant/dashboard':
        exiger_role(ROLE_ENSEIGNANT);
        require __DIR__ . '/views/enseignant/dashboard.php';
        break;
    case 'enseignant/cours':
        exiger_role(ROLE_ENSEIGNANT);
        require __DIR__ . '/views/enseignant/cours_gestion.php';
        break;
    case 'enseignant/cours/edit':
        exiger_role(ROLE_ENSEIGNANT);
        require __DIR__ . '/views/enseignant/cours_edit.php';
        break;
    case 'enseignant/statistiques':
        exiger_role(ROLE_ENSEIGNANT);
        require __DIR__ . '/views/enseignant/statistiques.php';
        break;

    // ---- PROMOTEUR ----
    case 'promoteur/dashboard':
        exiger_role(ROLE_PROMOTEUR);
        require __DIR__ . '/views/promoteur/dashboard.php';
        break;
    case 'promoteur/modules':
        exiger_role(ROLE_PROMOTEUR);
        require __DIR__ . '/views/promoteur/modules_gestion.php';
        break;
    case 'promoteur/module/edit':
        exiger_role(ROLE_PROMOTEUR);
        require __DIR__ . '/views/promoteur/module_edit.php';
        break;
    case 'promoteur/supervision':
        exiger_role(ROLE_PROMOTEUR);
        require __DIR__ . '/views/promoteur/supervision.php';
        break;

    // ---- VERIFICATION CERTIFICAT (publique) ----
    case 'certificat/verifier':
        require __DIR__ . '/views/etudiant/certificat_verifier.php';
        break;

    // ---- 404 ----
    default:
        http_response_code(404);
        echo '<!DOCTYPE html><html><head><title>404</title></head><body><h1>Page introuvable</h1><p><a href="' . APP_BASE_URL . '/index.php">Retour a l\'accueil</a></p></body></html>';
        break;
}
