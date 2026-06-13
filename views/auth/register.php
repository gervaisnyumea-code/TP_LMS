<?php
require_once __DIR__ . '/../../config/session.php';
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
    <title>Inscription - <?= e(APP_NAME) ?></title>
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
    <div class="auth-card" style="max-width: 500px;">
        <div class="auth-header mb-4">
            <div class="auth-logo" style="width: 48px; height: 48px; margin-bottom: var(--spacing-2);">
                <svg viewBox="0 0 24 24" style="width: 24px; height: 24px;"><path d="M15 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm-9-2V7H4v3H1v2h3v3h2v-3h3v-2H6zm9 4c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
            </div>
            <h2>Inscription Étudiant</h2>
        </div>

        <?php $flash = get_flash(); if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> mb-4">
                <?= e($flash['message']) ?>
            </div>
        <?php endif; ?>

        <form action="<?= $base_url ?>/actions/auth/register.php" method="POST">
            <?= csrf_field() ?>
            
            <div class="d-grid gap-3" style="grid-template-columns: 1fr 1fr;">
                <div class="form-group mb-0">
                    <label for="prenom" class="form-label">Prénom</label>
                    <input type="text" id="prenom" name="prenom" class="input-field" required>
                </div>
                <div class="form-group mb-0">
                    <label for="nom" class="form-label">Nom</label>
                    <input type="text" id="nom" name="nom" class="input-field" required>
                </div>
            </div>

            <div class="form-group mt-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" class="input-field" required>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Mot de passe</label>
                <input type="password" id="password" name="password" class="input-field" required minlength="6">
            </div>

            <div class="form-group mb-5">
                <label for="password_confirm" class="form-label">Confirmer le mot de passe</label>
                <input type="password" id="password_confirm" name="password_confirm" class="input-field" required minlength="6">
            </div>

            <button type="submit" class="btn btn-primary d-block" style="width: 100%;">
                Créer mon compte
            </button>
        </form>

        <div class="text-center mt-4 text-sm text-muted">
            Déjà un compte ? <a href="<?= $base_url ?>/index.php?page=login" class="font-medium">Se connecter</a>
        </div>
    </div>
</body>
</html>
