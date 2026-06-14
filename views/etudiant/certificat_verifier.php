<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Certificat.php';

$base_url = base_url();
$result = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code'] ?? '');
    if (!empty($code)) {
        $certificatModel = new Certificat($pdo);
        $result = $certificatModel->verifierParCode($code);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vérification de Certificat - <?= e(APP_NAME) ?></title>
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
    <div class="auth-card" style="max-width: 600px;">
        <div class="text-center mb-5">
            <div class="auth-logo mb-3">
                <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

            </div>
            <h2>Vérification de Certificat</h2>
            <p class="text-muted mt-2">Saisissez le code unique pour vérifier l'authenticité d'un diplôme délivré par LMS Cameroun.</p>
        </div>

        <form action="<?= $base_url ?>/index.php?page=certificat/verifier" method="POST" class="mb-5">
            <div class="d-flex gap-2">
                <input type="text" name="code" class="input-field" placeholder="Ex: ABCDEF1234567890..." required style="flex:1; font-family: monospace;" value="<?= isset($_POST['code']) ? e($_POST['code']) : '' ?>">
                <button type="submit" class="btn btn-primary">Vérifier</button>
            </div>
        </form>

        <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($code)): ?>
            <?php if ($result): ?>
                <div class="alert alert-success flex-column align-start mb-0">
                    <div class="d-flex align-center gap-2 mb-3">
                        <svg viewBox="0 0 24 24" style="width:28px; height:28px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/></svg>
                        <h3 class="font-bold m-0" style="color:var(--color-success)">Certificat Authentique</h3>
                    </div>
                    
                    <div class="bg-surface w-100 p-3 rounded" style="color:var(--color-text); border:1px solid rgba(0,0,0,0.1);">
                        <div class="d-grid gap-2" style="grid-template-columns: 100px 1fr;">
                            <div class="text-muted text-sm text-right">Titulaire :</div>
                            <div class="font-bold"><?= e($result['prenom'] . ' ' . $result['nom']) ?></div>
                            
                            <div class="text-muted text-sm text-right">Module :</div>
                            <div class="font-medium"><?= e($result['titre']) ?></div>
                            
                            <div class="text-muted text-sm text-right">Délivré le :</div>
                            <div><?= date('d/m/Y', strtotime($result['date_delivrance'])) ?></div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-error text-center p-4">
                    <svg viewBox="0 0 24 24" class="mx-auto mb-2" style="width:32px; height:32px;"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    <p class="font-bold mb-0">Code invalide ou certificat introuvable.</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="text-center mt-5">
            <a href="<?= $base_url ?>/index.php" class="text-secondary text-sm">Retour à l'accueil</a>
        </div>
    </div>
</body>
</html>
