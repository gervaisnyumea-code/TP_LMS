<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$page_title = 'Tableau de bord Promoteur';
require __DIR__ . '/../layouts/header.php';

// Stats
$nbModules = count($moduleModel->listerTous());
$nbCours = count($coursModel->listerTous());
$nbCertifs = count($certificatModel->listerTous());
$userCounts = $utilisateurModel->compterParRole();

$nbEnseignants = $userCounts[ROLE_ENSEIGNANT] ?? 0;
$nbEtudiants = $userCounts[ROLE_ETUDIANT] ?? 0;

$modulesRecents = array_slice($moduleModel->listerTous(), 0, 5);
$certifsRecents = array_slice($certificatModel->listerTous(), 0, 5);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Tableau de bord</h1>
        <p class="text-muted">Vue d'ensemble de la plateforme LMS Cameroun</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg></div>
        <div class="stat-content">
            <span class="stat-value"><?= $nbModules ?></span>
            <span class="stat-label">Modules</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z"/></svg></div>
        <div class="stat-content">
            <span class="stat-value"><?= $nbCours ?></span>
            <span class="stat-label">Cours total</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg></div>
        <div class="stat-content">
            <span class="stat-value"><?= $nbEnseignants ?></span>
            <span class="stat-label">Enseignants</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
        <div class="stat-content">
            <span class="stat-value"><?= $nbEtudiants ?></span>
            <span class="stat-label">Étudiants</span>
        </div>
    </div>
    <div class="stat-card">

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg></div>
        <div class="stat-content">
            <span class="stat-value"><?= $nbCertifs ?></span>
            <span class="stat-label">Certificats délivrés</span>
        </div>
    </div>
</div>

<div class="d-grid gap-4" style="grid-template-columns: 1fr 1fr;">
    <!-- Modules recents -->
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Modules Récents</h3>
            <a href="<?= $base_url ?>/index.php?page=promoteur/modules" class="btn btn-secondary btn-sm">Gérer</a>
        </div>
        <div class="table-container" style="border: none; border-radius: 0;">
            <?php if(empty($modulesRecents)): ?>
                <div class="p-4 text-center text-muted">Aucun module créé.</div>
            <?php else: ?>
            <table class="table">
                <thead><tr><th>Titre</th><th>Cours</th></tr></thead>
                <tbody>
                    <?php foreach($modulesRecents as $m): ?>
                    <tr>
                        <td class="font-medium"><?= e($m['titre']) ?></td>
                        <td><span class="badge badge-primary"><?= $m['nb_cours'] ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Certificats recents -->
    <div class="card">
        <div class="card-header">
            <h3 class="font-semibold">Derniers Certificats</h3>
            <a href="<?= $base_url ?>/index.php?page=promoteur/supervision" class="btn btn-secondary btn-sm">Tout voir</a>
        </div>
        <div class="table-container" style="border: none; border-radius: 0;">
            <?php if(empty($certifsRecents)): ?>
                <div class="p-4 text-center text-muted">Aucun certificat délivré.</div>
            <?php else: ?>
            <table class="table">
                <thead><tr><th>Étudiant</th><th>Module</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach($certifsRecents as $c): ?>
                    <tr>
                        <td class="font-medium"><?= e($c['etudiant_prenom'].' '.$c['etudiant_nom']) ?></td>
                        <td class="text-sm"><?= e($c['module_titre']) ?></td>
                        <td class="text-xs text-muted"><?= date('d/m/Y', strtotime($c['date_delivrance'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
