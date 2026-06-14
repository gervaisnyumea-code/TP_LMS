<?php
$page_title = 'Ma Progression';
require_once __DIR__ . '/../layouts/header.php';

$etudiant_id = $_SESSION['user_id'];

$cours_inscrits = $coursModel->listerCoursEtudiant($etudiant_id);
$total_lecons_validees = 0;
$total_lecons = 0;
$cours_termines = 0;

$progressions = [];
foreach ($cours_inscrits as $cours) {
    $prog = (int)$cours['progression'];
    $validees = count($progressionModel->listerParCours($etudiant_id, $cours['id'])); // Wait, listerParCours returns all lecons
    
    // Better calculation for the display
    $lecons_cours = $progressionModel->listerParCours($etudiant_id, $cours['id']);
    $nb_validees = 0;
    foreach($lecons_cours as $lc) if($lc['valide']) $nb_validees++;
    $nb_total = count($lecons_cours);

    $progressions[] = [
        'cours' => $cours,
        'pourcentage' => $prog,
        'lecons_validees' => $nb_validees,
        'total_lecons' => $nb_total,
        'termine' => ($prog >= 100)
    ];

    $total_lecons_validees += $nb_validees;
    $total_lecons += $nb_total;
    if ($prog >= 100) $cours_termines++;
}

$progression_globale = ($total_lecons > 0)
    ? round(($total_lecons_validees / $total_lecons) * 100, 1)
    : 0;
?>

<main class="main-content">
    <div class="topbar">
        <h1><?= e($page_title) ?></h1>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-stat">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            <span class="stat-value"><?= $progression_globale ?>%</span>
            <span class="stat-label">Progression globale</span>
        </div>
        <div class="dashboard-stat">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
            <span class="stat-value"><?= count($cours_inscrits) ?></span>
            <span class="stat-label">Cours inscrits</span>
        </div>
        <div class="dashboard-stat">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            <span class="stat-value"><?= $cours_termines ?></span>
            <span class="stat-label">Cours termines</span>
        </div>
        <div class="dashboard-stat">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
            <span class="stat-value"><?= $total_lecons_validees ?>/<?= $total_lecons ?></span>
            <span class="stat-label">Lecons validees</span>
        </div>
    </div>

    <section class="mt-4">
        <h2>Detail par cours</h2>

        <?php if (empty($progressions)): ?>
            <div class="card mt-2">
                <div class="card-body text-center text-muted">
                    <p>Aucun cours en cours. Inscrivez-vous a un cours pour commencer.</p>
                </div>
            </div>
        <?php else: ?>
            <?php foreach ($progressions as $p): ?>
                <div class="card mt-2">
                    <div class="card-header">
                        <span><?= e($p['cours']['titre']) ?></span>
                        <?php if ($p['termine']): ?>
                            <span class="badge badge-success">Termine</span>
                        <?php else: ?>
                            <span class="badge badge-info">En cours</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="progress-bar">
                            <div class="progress-bar-fill" style="width: <?= $p['pourcentage'] ?>%"></div>
                        </div>
                        <p class="text-muted mt-1">
                            <?= $p['lecons_validees'] ?> / <?= $p['total_lecons'] ?> lecons validees
                            -- <?= $p['pourcentage'] ?>%
                        </p>
                    </div>
                    <div class="card-footer">
                        <a href="index.php?page=etudiant/cours&id=<?= $p['cours']['id'] ?>" class="btn btn-secondary">
                            Voir le cours
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>
</main>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
