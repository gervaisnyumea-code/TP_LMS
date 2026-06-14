<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$page_title = 'Supervision';
require __DIR__ . '/../layouts/header.php';

$enseignants = $utilisateurModel->listerParRole(ROLE_ENSEIGNANT);
$etudiants = $utilisateurModel->listerParRole(ROLE_ETUDIANT);
$certificats = $certificatModel->listerTous();
$coursTous = $coursModel->listerTous();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Supervision Globale</h1>
        <p class="text-muted">Vue détaillée des utilisateurs, cours et certificats.</p>
    </div>
</div>

<div class="card">
    <div class="tabs">
        <div class="tab-item" data-target="#tab-enseignants">Enseignants (<?= count($enseignants) ?>)</div>
        <div class="tab-item" data-target="#tab-etudiants">Étudiants (<?= count($etudiants) ?>)</div>
        <div class="tab-item" data-target="#tab-modules">Modules (<?= count($modules = $moduleModel->listerTous()) ?>)</div>
        <div class="tab-item" data-target="#tab-cours">Cours (<?= count($coursTous) ?>)</div>
        <div class="tab-item" data-target="#tab-certificats">Certificats (<?= count($certificats) ?>)</div>
    </div>

    <div class="card-body p-0">
        <!-- Onglet Enseignants -->
        <div id="tab-enseignants" class="tab-content active">
            <div class="card mb-4">
                <div class="card-header"><h3 class="font-semibold">Créer un enseignant</h3></div>
                <div class="card-body">
                    <form action="<?= base_url('index.php?page=promoteur/enseignant_create') ?>" method="POST" class="d-grid gap-2" style="grid-template-columns: 1fr 1fr 1fr auto;">
                        <?= csrf_field() ?>
                        <input type="text" name="prenom" class="input-field" placeholder="Prénom" required>
                        <input type="text" name="nom" class="input-field" placeholder="Nom" required>
                        <input type="email" name="email" class="input-field" placeholder="Email" required>
                        <button type="submit" class="btn btn-primary">Créer</button>
                    </form>
                </div>
            </div>
            <div class="table-container" style="border:none;">
                <table class="table">
                    <thead><tr><th>Nom</th><th>Email</th><th>Statut</th><th>Date création</th><th>Actions</th></tr></thead>
                    <tbody>
                        <?php foreach($enseignants as $e): ?>
                        <tr>
                            <td class="font-medium"><?= e($e['prenom'].' '.$e['nom']) ?></td>
                            <td><?= e($e['email']) ?></td>
                            <td><span class="badge badge-<?= $e['actif'] ? 'success' : 'error' ?>"><?= $e['actif'] ? 'Actif' : 'Inactif' ?></span></td>
                            <td class="text-sm text-muted"><?= date('d/m/Y', strtotime($e['date_creation'])) ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-secondary btn-sm" onclick="document.getElementById('edit-enseignant-<?= $e['id'] ?>').classList.toggle('d-none')">Modifier</button>
                                    <form action="<?= base_url('index.php?page=promoteur/enseignant_delete') ?>" method="POST" onsubmit="return confirm('Supprimer ?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                                    </form>
                                </div>
                                <div id="edit-enseignant-<?= $e['id'] ?>" class="d-none mt-2 card p-3">
                                    <form action="<?= base_url('index.php?page=promoteur/enseignant_update') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $e['id'] ?>">
                                        <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                                            <input type="text" name="prenom" class="input-field" value="<?= e($e['prenom']) ?>" required>
                                            <input type="text" name="nom" class="input-field" value="<?= e($e['nom']) ?>" required>
                                            <input type="email" name="email" class="input-field" value="<?= e($e['email']) ?>" required>
                                            <input type="password" name="password" class="input-field" placeholder="Nouveau mot de passe">
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-sm mt-2">Enregistrer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Onglet Etudiants -->
        <div id="tab-etudiants" class="tab-content">
            <div class="table-container" style="border:none;">
                <table class="table">
                    <thead><tr><th>Nom</th><th>Email</th><th>Statut</th><th>Date inscription</th></tr></thead>
                    <tbody>
                        <?php foreach($etudiants as $et): ?>
                        <tr>
                            <td class="font-medium"><?= e($et['prenom'].' '.$et['nom']) ?></td>
                            <td><?= e($et['email']) ?></td>
                            <td><span class="badge badge-<?= $et['actif'] ? 'success' : 'error' ?>"><?= $et['actif'] ? 'Actif' : 'Inactif' ?></span></td>
                            <td class="text-sm text-muted"><?= date('d/m/Y', strtotime($et['date_creation'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Onglet Modules -->
        <div id="tab-modules" class="tab-content">
            <div class="table-container" style="border:none;">

<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */
?>

                <table class="table">
                    <thead><tr><th>Module</th><th>Cours</th><th>Assigner Cours</th></tr></thead>
                    <tbody>
                        <?php foreach($modules as $m): ?>
                        <tr>
                            <td class="font-medium"><?= e($m['titre']) ?></td>
                            <td>
                                <ul>
                                    <?php foreach($moduleModel->listerCours($m['id']) as $cm): ?>
                                        <li><?= e($cm['titre']) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </td>
                            <td>
                                <form action="<?= base_url('index.php?page=promoteur/module_assign_cours') ?>" method="POST" class="d-flex gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="module_id" value="<?= $m['id'] ?>">
                                    <select name="cours_id" class="select-field" required>
                                        <option value="">-- Choisir un cours --</option>
                                        <?php foreach($coursTous as $c): ?>
                                            <option value="<?= $c['id'] ?>"><?= e($c['titre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Assigner</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Onglet Cours -->
        <div id="tab-cours" class="tab-content">

            <div class="table-container" style="border:none;">
                <table class="table">
                    <thead><tr><th>Cours</th><th>Enseignant</th><th>Assigner</th></tr></thead>
                    <tbody>
                        <?php foreach($coursTous as $c): ?>
                        <tr>
                            <td class="font-medium"><?= e($c['titre']) ?></td>
                            <td><?= e(($c['enseignant_prenom'] ?? 'Aucun') . ' ' . ($c['enseignant_nom'] ?? '')) ?></td>
                            <td>
                                <form action="<?= base_url('index.php?page=promoteur/cours_assign') ?>" method="POST" class="d-flex gap-2">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="cours_id" value="<?= $c['id'] ?>">
                                    <select name="enseignant_id" class="select-field" required>
                                        <option value="">-- Choisir --</option>
                                        <?php foreach($enseignants as $e): ?>
                                            <option value="<?= $e['id'] ?>" <?= $c['enseignant_id'] == $e['id'] ? 'selected' : '' ?>><?= e($e['prenom'] . ' ' . $e['nom']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="btn btn-primary btn-sm">Assigner</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Onglet Certificats -->
        <div id="tab-certificats" class="tab-content">
            <div class="table-container" style="border:none;">
                <table class="table">
                    <thead><tr><th>Étudiant</th><th>Module</th><th>Date Délivrance</th></tr></thead>
                    <tbody>
                        <?php foreach($certificats as $c): ?>
                        <tr>
                            <td class="font-medium"><?= e($c['etudiant_prenom'].' '.$c['etudiant_nom']) ?></td>
                            <td><?= e($c['module_titre']) ?></td>
                            <td class="text-sm text-muted"><?= date('d/m/Y H:i', strtotime($c['date_delivrance'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.tab-item').forEach(tab => {
    tab.addEventListener('click', () => {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-item').forEach(el => el.classList.remove('active'));
        document.querySelector(tab.dataset.target).classList.add('active');
        tab.classList.add('active');
        // Mise à jour de l'URL sans recharger
        history.pushState(null, '', '?page=promoteur/supervision&tab=' + tab.dataset.target.substring(1));
    });
});

// Activer le tab depuis l'URL au chargement
window.addEventListener('load', () => {
    const params = new URLSearchParams(window.location.search);
    const tab = params.get('tab');
    if (tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.tab-item').forEach(el => el.classList.remove('active'));
        document.querySelector('#tab-' + tab).classList.add('active');
        document.querySelector('[data-target="#tab-' + tab + '"]').classList.add('active');
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
