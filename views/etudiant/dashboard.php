<?php
$page_title = 'Tableau de bord Étudiant';
require __DIR__ . '/../layouts/header.php';

$mesCours = $coursModel->listerCoursEtudiant($_SESSION['user_id']);
$certificats = $certificatModel->listerParEtudiant($_SESSION['user_id']);

$nbEnCours = 0;
$nbTermines = 0;
foreach($mesCours as $c) {
    if ($c['progression'] >= 100) {
        $nbTermines++;
    } else {
        $nbEnCours++;
    }
}
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Bonjour <?= e($_SESSION['prenom']) ?> !</h1>
        <p class="text-muted">Prêt(e) à continuer votre apprentissage ?</p>
    </div>
    <a href="<?= $base_url ?>/index.php?page=etudiant/catalogue" class="btn btn-primary">Explorer le catalogue</a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg></div>
        <div class="stat-content">
            <span class="stat-value"><?= count($mesCours) ?></span>
            <span class="stat-label">Cours inscrits</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M19 3H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-8.5 13.5l-4-4 1.41-1.41 2.59 2.58 6.59-6.59L18.5 9l-8 8z"/></svg></div>
        <div class="stat-content">
            <span class="stat-value"><?= $nbTermines ?></span>
            <span class="stat-label">Cours terminés</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg></div>
        <div class="stat-content">
            <span class="stat-value"><?= count($certificats) ?></span>
            <span class="stat-label">Certificats obtenus</span>
        </div>
    </div>
</div>

<h2 class="font-semibold mb-4">Mes cours en cours</h2>
<?php if(empty($mesCours)): ?>
    <div class="empty-state card mb-5">
        <div class="empty-state-icon"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg></div>
        <h3 class="empty-state-title">Aucun cours</h3>
        <p class="empty-state-desc">Vous n'êtes inscrit(e) à aucun cours.</p>
        <a href="<?= $base_url ?>/index.php?page=etudiant/catalogue" class="btn btn-primary mt-3">Voir le catalogue</a>
    </div>
<?php else: ?>
    <div class="content-grid mb-5">
        <?php foreach($mesCours as $c): ?>
        <div class="card course-card">
            <div class="course-card-img" style="background: linear-gradient(135deg, var(--color-primary) 0%, #0d2136 100%);">
                <?= strtoupper(substr($c['titre'], 0, 1)) ?>
            </div>
            <div class="course-card-content">
                <h3 class="course-card-title"><?= e($c['titre']) ?></h3>
                <div class="course-card-meta mb-0">
                    <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                    Par <?= e($c['enseignant_prenom'].' '.$c['enseignant_nom']) ?>
                </div>
                
                <div class="course-card-progress mt-4">
                    <div class="course-card-progress-text">
                        <span>Progression</span>
                        <span class="font-bold text-primary"><?= $c['progression'] ?>%</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar <?= $c['progression'] == 100 ? 'complete' : '' ?>" style="width: <?= $c['progression'] ?>%;"></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <a href="<?= $base_url ?>/index.php?page=etudiant/cours&id=<?= $c['id'] ?>" class="btn btn-secondary w-100" style="width:100%; justify-content:center;">
                    <?= $c['progression'] == 100 ? 'Revoir' : ($c['progression'] == 0 ? 'Commencer' : 'Continuer') ?>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
