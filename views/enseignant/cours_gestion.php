<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$page_title = 'Mes Cours';
require __DIR__ . '/../layouts/header.php';

$mesCours = $coursModel->listerParEnseignant($_SESSION['user_id']);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Mes Cours</h1>
        <p class="text-muted">Gérez vos contenus pédagogiques.</p>
    </div>
    <button class="btn btn-primary" onclick="document.getElementById('form-create-cours').classList.toggle('d-none')">Nouveau Cours</button>
</div>

<!-- Creation Form (Hidden by default) -->
<div id="form-create-cours" class="card mb-5 d-none">
    <div class="card-header"><h3 class="font-semibold">Créer un nouveau cours</h3></div>
    <div class="card-body">
        <form action="<?= $base_url ?>/index.php?page=enseignant/cours_create" method="POST">
            <?= csrf_field() ?>
            <div class="form-group">
                <label class="form-label">Titre du cours</label>
                <input type="text" name="titre" class="input-field" required>
            </div>
            <div class="form-group">
                <label class="form-label">Description courte</label>
                <textarea name="description" class="textarea-field" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Module (optionnel)</label>
                <select name="module_id" class="select-field">
                    <option value="">-- Aucun --</option>
                    <?php foreach($moduleModel->listerTous() as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= e($m['titre']) ?></option>
                    <?php endforeach; ?>
                </select>

<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */
?>

            </div>
            <div class="d-flex justify-end gap-2">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('form-create-cours').classList.add('d-none')">Annuler</button>
                <button type="submit" class="btn btn-primary">Créer et continuer</button>
            </div>
        </form>
    </div>
</div>

<?php if(empty($mesCours)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg></div>
        <h3 class="empty-state-title">Aucun cours</h3>
        <p class="empty-state-desc">Vous n'avez pas encore créé de cours. Cliquez sur "Nouveau Cours" pour commencer.</p>
    </div>
<?php else: ?>
    <div class="d-grid gap-4" style="grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));">
        <?php foreach($mesCours as $c): ?>
        <div class="card course-card">
            <div class="course-card-img" style="background: linear-gradient(135deg, var(--color-primary) 0%, #0d2136 100%);">
                <?= strtoupper(substr($c['titre'], 0, 1)) ?>
            </div>
            <div class="course-card-content">
                <h3 class="course-card-title"><?= e($c['titre']) ?></h3>
                <div class="course-card-meta mb-3">
                    <?= $c['nb_lecons'] ?> leçon(s)
                    <span style="margin:0 5px">•</span>
                    <?= $c['nb_inscrits'] ?> inscrit(s)
                </div>
                <p class="text-sm text-muted mb-4" style="flex:1;"><?= e(mb_strimwidth($c['description'] ?? '', 0, 100, '...')) ?></p>
            </div>
            <div class="card-footer d-flex justify-between align-center">
                <form action="<?= $base_url ?>/index.php?page=enseignant/cours_delete" method="POST" data-confirm="Attention: Cette action est irréversible. Supprimer ce cours supprimera toutes ses leçons et les progressions des étudiants. Continuer ?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                </form>
                <a href="<?= $base_url ?>/index.php?page=enseignant/cours/edit&id=<?= $c['id'] ?>" class="btn btn-secondary btn-sm">Modifier le contenu</a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
