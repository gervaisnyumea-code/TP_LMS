<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

require_once __DIR__ . '/../../config/session.php';
// Rediriger si deja connecte
if (est_connecte()) {
    rediriger($_SESSION['role'] . '/dashboard');
}
$base_url = base_url();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - <?= e(APP_NAME) ?></title>
    <link rel="icon" type="image/png" href="<?= $base_url ?>/public/img/logo/LMS.png">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/reset.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/variables.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/base.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/components.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/pages.css">
    <script>
        (function() {
            const savedTheme = localStorage.getItem('lms_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
</head>
<body class="auth-layout">
    <div class="auth-info-badge">
        AUTEUR : 
        <br>
        <ul>
            <li><span class="auth-info-name">NYUMEA PEHA DARYL GERVAIS</span></li>
            <li><span class="auth-info-detail">LICENCE 2 &middot; UNIVERSITE DE YAOUNDE 1</span></li>
        </ul>
        </div>
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <img src="<?= $base_url ?>/public/img/logo/LMS.png" alt="LMS Logo" style="height: 48px; width: auto;">

<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */
?>

            </div>
            <h2>Connexion</h2>
            <p class="text-muted text-sm">Bienvenue sur la plateforme <?= e(APP_NAME) ?></p>
        </div>

        <?php $flash = get_flash(); if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> mb-4">
                <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= $base_url ?>/index.php?page=auth/login" method="POST">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="input-field" required autofocus>
            </div>

            <div class="form-group mb-5">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="input-field" required>
            </div>

            <button type="submit" class="btn btn-primary d-block" style="width: 100%;">
                Se connecter
            </button>
        </form>

        <div class="text-center mt-4 text-sm text-muted">
            Pas encore de compte ? <a href="<?= $base_url ?>/index.php?page=register" class="font-medium">S'inscrire</a>
        </div>
        
        <div class="text-center mt-2 text-sm text-muted">
            <a href="<?= $base_url ?>/index.php?page=certificat/verifier" class="text-secondary">Vérifier un certificat</a>
        </div>
    </div>
</body>
</html>
