<?php
$page_title = 'Gestion des Modules';
require __DIR__ . '/../layouts/header.php';

$modules = $moduleModel->listerTous();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Gestion des Modules</h1>
        <p class="text-muted">Créez et gérez les programmes de formation certifiants.</p>
    </div>
</div>

<div class="d-grid gap-4" style="grid-template-columns: 1fr 2fr;">
    <!-- Creation -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Nouveau Module</h3>
            </div>
            <div class="card-body">
                <form action="<?= $base_url ?>/index.php?page=promoteur/module_create" method="POST">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label class="form-label">Titre du module</label>
                        <input type="text" name="titre" class="input-field" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="textarea-field" rows="4"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Créer le module</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Liste -->
    <div>
        <?php if(empty($modules)): ?>
            <div class="empty-state">
                <div class="empty-state-icon"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg></div>
                <h3 class="empty-state-title">Aucun module</h3>
                <p class="empty-state-desc">Commencez par créer un module à gauche.</p>
            </div>
        <?php else: ?>
            <div class="d-grid gap-3" style="grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));">
                <?php foreach($modules as $m): ?>
                <div class="card">
                    <div class="card-header flex-column align-start gap-2">
                        <h3 class="font-semibold"><?= e($m['titre']) ?></h3>
                        <span class="badge badge-primary"><?= $m['nb_cours'] ?> cours associé(s)</span>
                    </div>
                    <div class="card-body text-sm text-muted">
                        <?= e($m['description'] ?: 'Aucune description.') ?>
                    </div>
                    <div class="card-footer d-flex justify-end gap-2">
                        <form action="<?= $base_url ?>/index.php?page=promoteur/module_delete" method="POST" class="d-inline" data-confirm="Êtes-vous sûr de vouloir supprimer ce module ? Les certificats liés seront conservés mais orphelins.">
                            <?= csrf_field() ?>
                            <input type="hidden" name="id" value="<?= $m['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>
                        <a href="<?= $base_url ?>/index.php?page=promoteur/module/edit&id=<?= $m['id'] ?>" class="btn btn-primary btn-sm">
                            Gérer le contenu
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
