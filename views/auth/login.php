<?php
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
    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo">
                <svg viewBox="0 0 24 24"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/></svg>
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
