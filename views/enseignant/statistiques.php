<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$page_title = 'Statistiques des Cours';
require __DIR__ . '/../layouts/header.php';

$mesCours = $coursModel->listerParEnseignant($_SESSION['user_id']);
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Statistiques de Réussite</h1>
        <p class="text-muted">Analysez les performances de vos étudiants par cours et par leçon.</p>
    </div>
</div>

<?php if(empty($mesCours)): ?>
    <div class="empty-state">
        <div class="empty-state-icon"><svg viewBox="0 0 24 24"><path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/></svg></div>
        <h3 class="empty-state-title">Aucune donnée</h3>
        <p class="empty-state-desc">Vous n'avez pas encore de cours avec des données statistiques.</p>
    </div>
<?php else: ?>
    <div class="d-grid gap-5">
        <?php foreach($mesCours as $c): ?>
            <?php 
            $stats = $progressionModel->statsParCours($c['id']); 
            if(empty($stats)) continue; // Skip empty courses if needed, but we can show them
            ?>
            <div class="card">
                <div class="card-header bg-bg">
                    <h3 class="font-semibold text-primary">Cours : <?= e($c['titre']) ?></h3>
                    <span class="text-sm text-muted"><?= $c['nb_inscrits'] ?> inscrit(s)</span>
                </div>
                <div class="table-container" style="border:none; border-radius:0;">
                    <?php if(empty($stats)): ?>
                        <div class="p-4 text-center text-muted">Aucune leçon dans ce cours.</div>

<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */
?>

                    <?php else: ?>
                        
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Leçon</th>
                                <th>Étudiants ayant tenté</th>
                                <th>Validations</th>
                                <th>Taux de réussite</th>
                                <th>Note Moyenne</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($stats as $s): 
                                $taux = $s['nb_tentatives_total'] > 0 ? round(($s['nb_validations'] / $s['nb_tentatives_total']) * 100) : 0;
                            ?>
                            <tr>
                                <td class="text-muted"><?= $s['ordre'] ?></td>
                                <td class="font-medium"><?= e($s['titre']) ?></td>
                                <td><?= $s['nb_tentatives_total'] ?></td>
                                <td><span class="text-success font-bold"><?= $s['nb_validations'] ?></span></td>
                                <td>
                                    <div class="d-flex align-center gap-2">
                                        <div class="progress-container" style="width: 60px;">
                                            <div class="progress-bar <?= $taux >= 70 ? 'complete' : '' ?>" style="width: <?= $taux ?>%;"></div>
                                        </div>
                                        <span class="text-xs"><?= $taux ?>%</span>
                                    </div>
                                </td>
                                <td><span class="badge badge-<?= $s['note_moyenne'] >= 70 ? 'success' : 'warning' ?>"><?= $s['note_moyenne'] ?: '0' ?>%</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php endif; ?>
                    
                </div>
                
            </div>
        <?php endforeach; ?>
    </div>
    
<?php endif; ?>
 
<?php require __DIR__ . '/../layouts/footer.php'; ?>
