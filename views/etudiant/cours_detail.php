<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$cours_id = (int)($_GET['id'] ?? 0);

if (!$coursModel->estInscrit($_SESSION['user_id'], $cours_id)) {
    set_flash('error', 'Vous devez être inscrit pour accéder à ce cours.');
    rediriger('etudiant/catalogue');
}

$cours = $coursModel->trouverParId($cours_id);
if (!$cours) rediriger('etudiant/catalogue');

$lecons = $progressionModel->listerParCours($_SESSION['user_id'], $cours_id);
$progressionGlobale = $progressionModel->calculerProgressionCours($_SESSION['user_id'], $cours_id);

$page_title = $cours['titre'];
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= $base_url ?>/index.php?page=etudiant/dashboard" class="text-secondary text-sm d-block mb-2">← Tableau de bord</a>
        <h1 class="page-title"><?= e($cours['titre']) ?></h1>
        <p class="text-muted">Par <?= e($cours['enseignant_prenom'] ?? '') ?> <?= e($cours['enseignant_nom'] ?? '') ?></p>
    </div>
</div>

<div class="d-grid gap-4" style="grid-template-columns: 1fr 2fr;">
    <!-- Info -->
    <div>
        <div class="card mb-4">
            <div class="card-body">
                <h3 class="font-semibold mb-2">Progression</h3>
                <div class="course-card-progress mb-3">
                    <div class="course-card-progress-text">
                        <span>Avancement</span>
                        <span class="font-bold text-primary" id="prog-text"><?= $progressionGlobale ?>%</span>
                    </div>
                    <div class="progress-container">
                        <div class="progress-bar <?= $progressionGlobale == 100 ? 'complete' : '' ?>" style="width: <?= $progressionGlobale ?>%;" id="prog-bar"></div>
                    </div>
                </div>
                
                <?php if($progressionGlobale == 100 && $cours['module_titre']): ?>
                    <div class="alert alert-success p-2 mt-4">
                        <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                        <div class="text-sm">
                            <span class="font-bold d-block">Cours terminé !</span>
                            Vérifiez vos certificats si le module est complet.
                        </div>
                    </div>
                <?php endif; ?>

<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */
?>


                <h3 class="font-semibold mb-2 mt-4">Description</h3>
                <p class="text-sm text-muted"><?= e($cours['description'] ?: 'Aucune description fournie.') ?></p>
                
                <?php if($cours['module_titre']): ?>
                    <div class="mt-4">
                        <span class="text-sm font-semibold">Fait partie du module :</span><br>
                        <span class="badge badge-primary mt-1"><?= e($cours['module_titre']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Lecons -->
    <div>
        <div class="card">
            <div class="card-header"><h3 class="font-semibold">Plan du cours</h3></div>
            <div class="card-body p-0">
                <?php 
                foreach($lecons as $index => $l): 
                    // Logique d'accès : la 1ere est tjs dispo, les autres si la précédente est validée
                    $accessible = true;
                    if ($index > 0 && !$lecons[$index-1]['valide']) {
                        $accessible = false;
                    }
                ?>
                <div class="lesson-item <?= $accessible ? 'cursor-pointer' : 'opacity-50' ?>" <?= $accessible ? 'onclick="window.location.href=\''.$base_url.'/index.php?page=etudiant/lecon&id='.$l['lecon_id'].'\'"' : '' ?>>
                    <div class="lesson-icon <?= $l['valide'] ? 'validated' : ($accessible ? 'available' : 'locked') ?>">
                        <?php if($l['valide']): ?>
                            <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        <?php elseif($accessible): ?>
                            <svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                        <?php endif; ?>
                    </div>
                    
                    <div class="lesson-details">
                        <div class="d-flex justify-between align-center">
                            <h4 class="lesson-title"><?= $l['ordre'] ?>. <?= e($l['lecon_titre']) ?></h4>
                            <?php if($l['note_obtenue'] !== null): ?>
                                <span class="badge badge-<?= $l['valide'] ? 'success' : 'warning' ?>">Score: <?= $l['note_obtenue'] ?>%</span>
                            <?php endif; ?>
                        </div>
                        <div class="lesson-meta">
                            <?= strtoupper($l['type_contenu']) ?> 
                            <?= $l['duree_estimee'] ? ' • ~'.$l['duree_estimee'].' min' : '' ?>
                            <?= $l['evaluation_id'] ? ' • Quiz' : '' ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
