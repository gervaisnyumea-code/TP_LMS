<?php
$page_title = 'Mes Certificats';
require __DIR__ . '/../layouts/header.php';

$certificats = $certificatModel->listerParEtudiant($_SESSION['user_id']);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Mes Certificats</h1>
        <p class="text-muted">Retrouvez ici tous vos diplômes et certifications obtenus.</p>
    </div>
</div>

<?php if(empty($certificats)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg></div>
        <h3 class="empty-state-title">Aucun certificat</h3>
        <p class="empty-state-desc">Vous n'avez pas encore terminé de module certifiant. Continuez votre apprentissage !</p>
        <a href="<?= $base_url ?>/index.php?page=etudiant/dashboard" class="btn btn-primary mt-3">Aller à mes cours</a>
    </div>
<?php else: ?>
    <div class="d-grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));">
        <?php foreach($certificats as $c): ?>
        <div class="card" style="border-top: 4px solid var(--color-primary);">
            <div class="card-body text-center py-5">
                <div class="mb-3 text-primary">
                    <svg viewBox="0 0 24 24" style="width: 48px; height: 48px;"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                </div>
                <h3 class="font-bold text-lg mb-2"><?= e($c['module_titre']) ?></h3>
                <p class="text-sm text-muted mb-4">Délivré le <?= date('d/m/Y', strtotime($c['date_delivrance'])) ?></p>
                
                <div class="bg-bg p-2 rounded text-xs text-muted font-mono mb-4">
                    Code : <?= e($c['code_verification']) ?>
                </div>
                
                <a href="<?= $base_url ?>/index.php?page=etudiant/certificat&id=<?= $c['id'] ?>" target="_blank" class="btn btn-primary btn-sm">
                    Voir / Imprimer
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
