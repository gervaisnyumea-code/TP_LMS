<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$module_id = (int)($_GET['id'] ?? 0);
$module = $moduleModel->trouverParId($module_id);

if (!$module) {
    set_flash('error', 'Module introuvable.');
    rediriger('promoteur/modules');
}

$coursAssocies = $moduleModel->listerCours($module_id);
$coursDisponibles = $moduleModel->listerCoursDisponibles();

$page_title = 'Éditer le Module : ' . $module['titre'];
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= $base_url ?>/index.php?page=promoteur/modules" class="text-secondary text-sm d-block mb-2">← Retour aux modules</a>
        <h1 class="page-title"><?= e($module['titre']) ?></h1>
        <p class="text-muted">Gestion du contenu du module</p>
    </div>
</div>

<div class="d-grid gap-4" style="grid-template-columns: 1fr 2fr;">
    <!-- Info Edition -->
    <div>
        <div class="card">
            <div class="card-header"><h3 class="font-semibold">Informations</h3></div>
            <div class="card-body">
                <form action="<?= $base_url ?>/index.php?page=promoteur/module_update" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="id" value="<?= $module_id ?>">
                    <div class="form-group">
                        <label class="form-label">Titre</label>
                        <input type="text" name="titre" class="input-field" value="<?= e($module['titre']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="textarea-field" rows="4"><?= e($module['description'] ?? '') ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Enregistrer</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Cours Associations -->
    <div>
        <!-- Cours Associés -->
        <div class="card mb-4">
            <div class="card-header">
                <h3 class="font-semibold text-success">Cours Associés (<?= count($coursAssocies) ?>)</h3>
            </div>
            <div class="table-container" style="border: none; border-radius: 0;">
                <?php if(empty($coursAssocies)): ?>

<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */
?>

                    <div class="p-4 text-center text-muted">Aucun cours n'est associé à ce module pour le moment.</div>
                <?php else: ?>
                <table class="table">
                    <thead><tr><th>Titre</th><th>Enseignant</th><th>Leçons</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach($coursAssocies as $c): ?>
                        <tr>
                            <td class="font-medium"><?= e($c['titre']) ?></td>
                            <td class="text-sm"><?= e($c['enseignant_prenom'].' '.$c['enseignant_nom']) ?></td>
                            <td><span class="badge badge-locked"><?= $c['nb_lecons'] ?></span></td>
                            <td>
                                <form action="<?= $base_url ?>/index.php?page=promoteur/module_assign" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="module_id" value="<?= $module_id ?>">
                                    <input type="hidden" name="cours_id" value="<?= $c['id'] ?>">
                                    <input type="hidden" name="action" value="dissocier">
                                    <button type="submit" class="btn btn-danger btn-sm">Retirer</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cours Disponibles -->
        <div class="card">
            <div class="card-header">
                <h3 class="font-semibold">Cours Disponibles pour Association</h3>
            </div>
            <div class="table-container" style="border: none; border-radius: 0;">
                <?php if(empty($coursDisponibles)): ?>
                    <div class="p-4 text-center text-muted">Tous les cours sont déjà associés à des modules.</div>
                <?php else: ?>
                <table class="table">
                    <thead><tr><th>Titre</th><th>Enseignant</th><th>Action</th></tr></thead>
                    <tbody>
                        <?php foreach($coursDisponibles as $c): ?>
                        <tr>
                            <td class="font-medium"><?= e($c['titre']) ?></td>
                            <td class="text-sm"><?= e($c['enseignant_prenom'].' '.$c['enseignant_nom']) ?></td>
                            <td>
                                <form action="<?= $base_url ?>/index.php?page=promoteur/module_assign" method="POST">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="module_id" value="<?= $module_id ?>">
                                    <input type="hidden" name="cours_id" value="<?= $c['id'] ?>">
                                    <input type="hidden" name="action" value="associer">
                                    <button type="submit" class="btn btn-primary btn-sm">Ajouter</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
