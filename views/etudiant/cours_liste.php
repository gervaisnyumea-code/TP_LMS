<?php
$page_title = 'Catalogue des Cours';
require __DIR__ . '/../layouts/header.php';

$coursVisibles = $coursModel->listerVisibles();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Catalogue des Cours</h1>
        <p class="text-muted">Découvrez toutes nos formations disponibles.</p>
    </div>
</div>

<?php if(empty($coursVisibles)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg></div>
        <h3 class="empty-state-title">Aucun cours disponible</h3>
        <p class="empty-state-desc">Revenez plus tard pour découvrir de nouveaux contenus.</p>
    </div>
<?php else: ?>
    <div class="content-grid">
        <?php foreach($coursVisibles as $c): 
            $estInscrit = $coursModel->estInscrit($_SESSION['user_id'], $c['id']);
        ?>
        <div class="card course-card">
            <div class="course-card-img" style="background: linear-gradient(135deg, var(--color-primary) 0%, #0d2136 100%);">
                <?= strtoupper(substr($c['titre'], 0, 1)) ?>
            </div>
            <div class="course-card-content">
                <div class="d-flex justify-between align-start mb-2">
                    <h3 class="course-card-title m-0"><?= e($c['titre']) ?></h3>
                    <?php if($estInscrit): ?>
                        <span class="badge badge-success">Inscrit</span>
                    <?php endif; ?>
                </div>
                
                <?php if($c['module_titre']): ?>
                    <span class="badge badge-primary mb-3" style="align-self: flex-start;"><?= e($c['module_titre']) ?></span>
                <?php endif; ?>

                <div class="course-card-meta mb-3 mt-2">
                    <?= e($c['enseignant_prenom'].' '.$c['enseignant_nom']) ?>
                    <span style="margin:0 5px">•</span>
                    <?= $c['nb_lecons'] ?> leçon(s)
                </div>
                
                <p class="text-sm text-muted mb-4" style="flex:1;"><?= e(mb_strimwidth($c['description'] ?? '', 0, 100, '...')) ?></p>
            </div>
            <div class="card-footer">
                <?php if($estInscrit): ?>
                    <a href="<?= $base_url ?>/index.php?page=etudiant/cours&id=<?= $c['id'] ?>" class="btn btn-secondary w-100" style="width:100%; justify-content:center;">Accéder au cours</a>
                <?php else: ?>
                    <form action="<?= $base_url ?>/index.php?page=etudiant/cours_inscrire" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="cours_id" value="<?= $c['id'] ?>">
                        <button type="submit" class="btn btn-primary w-100" style="width:100%; justify-content:center;">S'inscrire</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
