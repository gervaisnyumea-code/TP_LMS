<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$page_title = 'Tableau de bord Enseignant';
require __DIR__ . '/../layouts/header.php';

$mesCours = $coursModel->listerParEnseignant($_SESSION['user_id']);
$nbCours = count($mesCours);

$nbEtudiantsTotal = 0;
$nbLeconsTotal = 0;
foreach ($mesCours as $c) {
    $nbEtudiantsTotal += $c['nb_inscrits'];
    $nbLeconsTotal += $c['nb_lecons'];
}

$coursRecents = array_slice($mesCours, 0, 5);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Bonjour <?= e($_SESSION['prenom']) ?> !</h1>
        <p class="text-muted">Voici l'état de votre activité d'enseignement.</p>
    </div>
    <a href="<?= $base_url ?>/index.php?page=enseignant/cours" class="btn btn-primary">Gérer mes cours</a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg></div>
        <div class="stat-content">
            <span class="stat-value"><?= $nbCours ?></span>
            <span class="stat-label">Cours créés</span>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg></div>
        <div class="stat-content">
            <span class="stat-value"><?= $nbEtudiantsTotal ?></span>
            <span class="stat-label">Inscriptions totales</span>

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><svg viewBox="0 0 24 24"><path d="M8 16h8v2H8zm0-4h8v2H8zm6-10H6c-1.1 0-2 .9-2 2v16c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm4 18H6V4h7v5h5v11z"/></svg></div>
        <div class="stat-content">
            <span class="stat-value"><?= $nbLeconsTotal ?></span>
            <span class="stat-label">Leçons publiées</span>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header">
        <h3 class="font-semibold">Cours Récents</h3>
    </div>
    <div class="table-container" style="border: none; border-radius: 0;">
        <?php if(empty($coursRecents)): ?>
            <div class="p-4 text-center text-muted">Vous n'avez pas encore créé de cours.</div>
        <?php else: ?>
        <table class="table">
            <thead><tr><th>Titre</th><th>Module</th><th>Leçons</th><th>Inscrits</th><th>Statut</th><th>Action</th></tr></thead>
            <tbody>
                <?php foreach($coursRecents as $c): ?>
                <tr>
                    <td class="font-medium"><?= e($c['titre']) ?></td>
                    <td class="text-sm"><?= $c['module_titre'] ? e($c['module_titre']) : '<span class="text-muted">Aucun</span>' ?></td>
                    <td><?= $c['nb_lecons'] ?></td>
                    <td><?= $c['nb_inscrits'] ?></td>
                    <td>
                        <span class="badge badge-<?= $c['visible'] ? 'success' : 'locked' ?>">
                            <?= $c['visible'] ? 'Public' : 'Brouillon' ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?= $base_url ?>/index.php?page=enseignant/cours/edit&id=<?= $c['id'] ?>" class="text-secondary">Éditer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
