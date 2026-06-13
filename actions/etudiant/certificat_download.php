<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Certificat.php';

if (!est_connecte()) rediriger('login');

$id = (int)($_GET['id'] ?? 0);
$certificatModel = new Certificat($pdo);
$certif = $certificatModel->trouverParId($id);

if (!$certif) {
    die("Certificat introuvable.");
}

// On permet au promoteur ou à l'étudiant proprio de le voir
if ($_SESSION['role'] !== ROLE_PROMOTEUR && $certif['etudiant_id'] !== $_SESSION['user_id']) {
    die("Accès refusé.");
}

$base_url = base_url();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Certificat - <?= e($certif['module_titre']) ?></title>
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/variables.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/base.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/pages.css">
    <style>
        body { background-color: #f0f2f5; margin: 0; padding: 0; display: flex; justify-content: center; min-height: 100vh; }
    </style>
</head>
<body>
    <div class="certificate-wrapper">
        <div class="certificate">
            <div class="certificate-inner">
                <div class="cert-logo">
                    <svg viewBox="0 0 24 24"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/></svg>
                </div>
                <div class="cert-title">Certificat de Réussite</div>
                <div class="cert-subtitle">Décerné à</div>
                <div class="cert-name"><?= e($certif['etudiant_prenom'].' '.$certif['etudiant_nom']) ?></div>
                <div class="cert-reason">Pour avoir complété avec succès le programme de formation certifiant</div>
                <div class="cert-course"><?= e($certif['module_titre']) ?></div>
                
                <div class="cert-footer">
                    <div class="text-left">
                        <div class="cert-date">Date : <?= date('d/m/Y', strtotime($certif['date_delivrance'])) ?></div>
                    </div>
                    <div class="text-right">
                        <div class="cert-signature">LMS Cameroun<br><span style="font-size:12px; font-style:italic">Direction Académique</span></div>
                    </div>
                </div>
            </div>
            <div class="cert-meta">
                Code de vérification : <?= e($certif['code_verification']) ?><br>
                Vérifiable sur : <?= $_SERVER['HTTP_HOST'] ?><?= $base_url ?>/index.php?page=certificat/verifier
            </div>
        </div>
    </div>
    
    <script>
        // Print dialog automatically
        window.onload = function() {
            setTimeout(() => window.print(), 500);
        }
    </script>
</body>
</html>
