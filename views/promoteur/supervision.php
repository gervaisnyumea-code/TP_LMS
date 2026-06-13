<?php
$page_title = 'Supervision';
require __DIR__ . '/../layouts/header.php';

$enseignants = $utilisateurModel->listerParRole(ROLE_ENSEIGNANT);
$etudiants = $utilisateurModel->listerParRole(ROLE_ETUDIANT);
$certificats = $certificatModel->listerTous();
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Supervision Globale</h1>
        <p class="text-muted">Vue détaillée des utilisateurs et des certificats.</p>
    </div>
</div>

<div class="card">
    <div class="tabs">
        <div class="tab-item active" data-target="#tab-enseignants">Enseignants (<?= count($enseignants) ?>)</div>
        <div class="tab-item" data-target="#tab-etudiants">Étudiants (<?= count($etudiants) ?>)</div>
        <div class="tab-item" data-target="#tab-certificats">Certificats (<?= count($certificats) ?>)</div>
    </div>

    <div class="card-body p-0">
        <!-- Onglet Enseignants -->
        <div id="tab-enseignants" class="tab-content active">
            <div class="table-container" style="border:none;">
                <table class="table">
                    <thead><tr><th>Nom</th><th>Email</th><th>Statut</th><th>Date création</th></tr></thead>
                    <tbody>
                        <?php foreach($enseignants as $e): ?>
                        <tr>
                            <td class="font-medium"><?= e($e['prenom'].' '.$e['nom']) ?></td>
                            <td><?= e($e['email']) ?></td>
                            <td><span class="badge badge-<?= $e['actif'] ? 'success' : 'error' ?>"><?= $e['actif'] ? 'Actif' : 'Inactif' ?></span></td>
                            <td class="text-sm text-muted"><?= date('d/m/Y', strtotime($e['date_creation'])) ?></td>
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

        <!-- Onglet Certificats -->
        <div id="tab-certificats" class="tab-content">
            <div class="table-container" style="border:none;">
                <table class="table">
                    <thead><tr><th>Étudiant</th><th>Module</th><th>Code Vérification</th><th>Date Délivrance</th></tr></thead>
                    <tbody>
                        <?php foreach($certificats as $c): ?>
                        <tr>
                            <td class="font-medium"><?= e($c['etudiant_prenom'].' '.$c['etudiant_nom']) ?></td>
                            <td><?= e($c['module_titre']) ?></td>
                            <td><code class="text-xs"><?= substr($c['code_verification'], 0, 16) ?>...</code></td>
                            <td class="text-sm text-muted"><?= date('d/m/Y H:i', strtotime($c['date_delivrance'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
