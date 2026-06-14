<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$lecon_id = (int)($_GET['id'] ?? 0);
$lecon = $leconModel->trouverParId($lecon_id);

if (!$lecon) rediriger('etudiant/catalogue');

$cours_id = $lecon['cours_id'];

$lecons = $progressionModel->listerParCours($_SESSION['user_id'], $cours_id);
$accessible = false;
$lecon_data_prog = null;

foreach ($lecons as $i => $l) {
    if ($l['lecon_id'] == $lecon_id) {
        if ($i == 0 || $lecons[$i - 1]['valide'] || $l['valide']) {
            $accessible = true;
        }
        $lecon_data_prog = $l;
        break;
    }
}

if (!$accessible || !$lecon_data_prog) {
    set_flash('error', 'Lecon non accessible.');
    rediriger('etudiant/cours', ['id' => $cours_id]);
}

$evaluation = null;
$questions = [];
$tentatives_restantes = 0;

if (!empty($lecon_data_prog['evaluation_id'])) {
    $evaluation = $evaluationModel->trouverParId((int)$lecon_data_prog['evaluation_id']);
    $questions = $evaluationModel->listerQuestions((int)$lecon_data_prog['evaluation_id']);
    $max = (int)($evaluation['tentatives_max'] ?? 3);
    $faites = (int)($lecon_data_prog['nb_tentatives'] ?? 0);
    $tentatives_restantes = max(0, $max - $faites);
}

$file_url = $lecon['url_contenu'] ?? '';

$page_title = $lecon['titre'];
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header mb-3">
    <div>
        <a href="<?= base_url('index.php?page=etudiant/cours&id=' . $cours_id) ?>" class="text-secondary text-sm d-block mb-1">Retour au plan du cours</a>
        <h1 class="page-title text-xl"><?= e($lecon['titre']) ?></h1>
    </div>
</div>

<div class="lesson-player">
    <div class="player-main">
        <div class="player-content-wrapper">
            <?php if ($lecon['type_contenu'] === 'video'): ?>
                <video id="lesson-video" class="player-video" controls controlsList="nodownload">
                    <source src="<?= e($lecon['url_contenu']) ?>" type="video/mp4">
                    Votre navigateur ne supporte pas la lecture video.
                </video>
            <?php elseif ($lecon['type_contenu'] === 'pdf'): ?>
                <iframe id="lesson-pdf" class="player-pdf" src="https://docs.google.com/viewer?url=<?= urlencode($lecon['url_contenu']) ?>&embedded=true" frameborder="0" style="width:100%;height:70vh;"></iframe>
            <?php endif; ?>
        </div>

        <?php if ($evaluation): ?>
            <div class="quiz-card">
                <div class="d-flex justify-between align-center mb-3">
                    <div>
                        <h3 class="font-semibold">Evaluation : <?= e($evaluation['titre']) ?></h3>
                        <p class="text-sm text-muted">Note requise : <?= $evaluation['note_de_passage'] ?>% | Tentatives restantes : <?= $tentatives_restantes ?></p>
                    </div>

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

                </div>

                <div id="resultat-quiz"></div>

                <?php if ($lecon_data_prog['valide']): ?>
                    <div class="alert alert-success">
                        Lecon validee ! Score : <?= $lecon_data_prog['note_obtenue'] ?>%
                    </div>
                <?php elseif ($tentatives_restantes <= 0): ?>
                    <div class="alert alert-error">
                        Nombre maximal de tentatives atteint.
                    </div>
                <?php else: ?>
                    <button id="btn-start-quiz" class="btn btn-primary w-100 mt-2" style="width:100%;">
                        Passer l'evaluation
                    </button>

                    <div id="quiz-section" class="d-none mt-4">
                        <form id="form-quiz-<?= $evaluation['id'] ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="evaluation_id" value="<?= $evaluation['id'] ?>">
                            <input type="hidden" name="cours_id" value="<?= $cours_id ?>">

                            <?php foreach ($questions as $index => $q):
                                $options = json_decode($q['options_json'], true);
                            ?>
                                <div class="quiz-question">
                                    <div class="quiz-question-title"><?= ($index + 1) ?>. <?= e($q['question_text']) ?></div>
                                    <?php foreach ($options as $lettre => $texte): ?>
                                        <label class="quiz-option">
                                            <input type="radio" name="question_<?= $q['id'] ?>" value="<?= e($lettre) ?>">
                                            <span><?= e($lettre) ?>. <?= e($texte) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>

                            <button type="button" class="btn btn-primary" onclick="soumettreEvaluation(<?= $evaluation['id'] ?>)">Soumettre mes reponses</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="card mt-2 p-4">
                <p class="text-muted text-sm m-0">Aucune evaluation associee a cette lecon.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="player-sidebar d-none d-lg-flex">
        <div class="player-sidebar-header">
            <h3 class="font-semibold text-sm">Plan du cours</h3>
        </div>
        <div class="player-sidebar-list">
            <?php foreach ($lecons as $index => $l):
                $is_accessible = ($index == 0 || $lecons[$index - 1]['valide']);
                $isActive = $l['lecon_id'] == $lecon_id;
            ?>
            <div class="lesson-item <?= $isActive ? 'active' : '' ?> <?= $is_accessible ? 'cursor-pointer' : 'opacity-50' ?>"
                 <?= $is_accessible && !$isActive ? 'onclick="window.location.href=\'' . base_url('index.php?page=etudiant/lecon&id=' . $l['lecon_id']) . '\'"' : '' ?>>
                <div class="lesson-icon <?= $l['valide'] ? 'validated' : ($is_accessible ? 'available' : 'locked') ?>">
                    <?php if ($l['valide']): ?>
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    <?php elseif ($is_accessible): ?>
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                    <?php endif; ?>
                </div>
                <div class="lesson-details">
                    <div class="lesson-title <?= $isActive ? 'font-bold' : '' ?>"><?= $l['ordre'] ?>. <?= e($l['lecon_titre']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
