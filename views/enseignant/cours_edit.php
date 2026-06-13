<?php
$cours_id = (int)($_GET['id'] ?? 0);
$cours = $coursModel->trouverParId($cours_id);

if (!$cours || !$coursModel->appartientA($cours_id, $_SESSION['user_id'])) {
    set_flash('error', 'Cours introuvable ou accès refusé.');
    rediriger('enseignant/cours');
}

$lecons = $leconModel->listerParCours($cours_id);

$page_title = 'Éditer le Cours : ' . $cours['titre'];
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= $base_url ?>/index.php?page=enseignant/cours" class="text-secondary text-sm d-block mb-2">← Retour à mes cours</a>
        <h1 class="page-title"><?= e($cours['titre']) ?></h1>
    </div>
</div>

<div class="d-grid gap-4" style="grid-template-columns: 1fr 2fr;">
    <!-- Info Cours -->
    <div>
        <div class="card mb-4">
            <div class="card-header"><h3 class="font-semibold">Informations du cours</h3></div>
            <div class="card-body">
                <form action="<?= $base_url ?>/actions/enseignant/cours_update.php" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $cours_id ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Titre</label>
                        <input type="text" name="titre" class="input-field" value="<?= e($cours['titre']) ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="textarea-field" rows="4"><?= e($cours['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-group">
                        <label class="d-flex align-center gap-2 cursor-pointer">
                            <input type="checkbox" name="visible" value="1" <?= $cours['visible'] ? 'checked' : '' ?> style="width: 18px; height: 18px;">
                            <span>Publier (visible aux étudiants)</span>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary w-100" style="width: 100%;">Enregistrer les infos</button>
                </form>
            </div>
        </div>

        <!-- Add Leçon -->
        <div class="card">
            <div class="card-header"><h3 class="font-semibold">Ajouter une leçon</h3></div>
            <div class="card-body">
                <form action="<?= $base_url ?>/actions/enseignant/lecon_create.php" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="cours_id" value="<?= $cours_id ?>">
                    
                    <div class="form-group">
                        <label class="form-label">Titre de la leçon</label>
                        <input type="text" name="titre" class="input-field" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Type de contenu</label>
                        <select name="type_contenu" class="select-field" required>
                            <option value="video">Vidéo (MP4, WebM)</option>
                            <option value="pdf">Document PDF</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Fichier</label>
                        <input type="file" name="fichier" class="input-field" required accept=".pdf,.mp4,.webm">
                    </div>
                    
                    <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                        <div class="form-group mb-0">
                            <label class="form-label">Durée (min)</label>
                            <input type="number" name="duree_estimee" class="input-field">
                        </div>
                        <div class="form-group mb-0">
                            <label class="form-label">Ordre</label>
                            <input type="number" name="ordre" class="input-field" value="<?= $leconModel->prochainOrdre($cours_id) ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-secondary mt-3" style="width: 100%;">Ajouter la leçon</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Liste Lecons -->
    <div>
        <div class="card">
            <div class="card-header d-flex justify-between align-center">
                <h3 class="font-semibold">Plan du cours (<?= count($lecons) ?> leçons)</h3>
            </div>
            
            <div class="card-body p-0">
                <?php if(empty($lecons)): ?>
                    <div class="p-4 text-center text-muted">Aucune leçon pour le moment.</div>
                <?php else: ?>
                    <?php foreach($lecons as $l): ?>
                    <div class="lesson-item d-flex flex-column align-stretch">
                        <div class="d-flex justify-between align-center w-100">
                            <div class="d-flex align-center gap-3">
                                <span class="badge badge-locked"><?= $l['ordre'] ?></span>
                                <div>
                                    <h4 class="font-medium"><?= e($l['titre']) ?></h4>
                                    <div class="text-xs text-muted d-flex gap-2 align-center">
                                        <span class="badge badge-<?= $l['type_contenu'] === 'pdf' ? 'error' : 'secondary' ?>"><?= strtoupper($l['type_contenu']) ?></span>
                                        <?php if($l['duree_estimee']): ?>
                                            <span>~<?= $l['duree_estimee'] ?> min</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <form action="<?= $base_url ?>/actions/enseignant/lecon_delete.php" method="POST" data-confirm="Supprimer cette leçon et son évaluation ?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $l['id'] ?>">
                                <input type="hidden" name="cours_id" value="<?= $cours_id ?>">
                                <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                            </form>
                        </div>

                        <!-- Evaluation Info -->
                        <div class="mt-3 p-3 bg-bg rounded">
                            <?php if($l['evaluation_id']): ?>
                                <div class="d-flex justify-between align-center">
                                    <div>
                                        <span class="font-medium text-sm">Évaluation : <?= e($l['evaluation_titre']) ?></span>
                                        <span class="text-xs text-muted ml-2">(<?= $l['nb_questions'] ?> questions)</span>
                                    </div>
                                    <!-- Here we would link to a dedicated evaluation edit page or open a modal -->
                                    <button class="btn btn-secondary btn-sm" onclick="alert('L\'édition des évaluations se fait sur une vue détaillée (à implémenter si besoin).')">Gérer QCM</button>
                                </div>
                            <?php else: ?>
                                <div class="d-flex justify-between align-center">
                                    <span class="text-sm text-warning font-medium d-flex align-center gap-1"><svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg> Aucune évaluation définie</span>
                                    <button class="btn btn-primary btn-sm" onclick="document.getElementById('eval-form-<?= $l['id'] ?>').classList.toggle('d-none')">Créer Évaluation</button>
                                </div>
                                
                                <!-- Inline Eval Create Form -->
                                <form id="eval-form-<?= $l['id'] ?>" action="<?= $base_url ?>/actions/enseignant/evaluation_create.php" method="POST" class="d-none mt-3">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="lecon_id" value="<?= $l['id'] ?>">
                                    <input type="hidden" name="cours_id" value="<?= $cours_id ?>">
                                    <div class="d-grid gap-2" style="grid-template-columns: 2fr 1fr 1fr;">
                                        <input type="text" name="titre" class="input-field input-sm" placeholder="Titre du Quiz" value="Quiz : <?= e($l['titre']) ?>" required>
                                        <input type="number" name="note_de_passage" class="input-field input-sm" placeholder="Seuil %" value="70" required>
                                        <input type="number" name="tentatives_max" class="input-field input-sm" placeholder="Essais" value="3" required>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm mt-2">Sauvegarder l'évaluation</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
