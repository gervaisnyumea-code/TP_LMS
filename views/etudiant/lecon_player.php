<?php
$lecon_id = (int)($_GET['id'] ?? 0);
$lecon = $leconModel->trouverParId($lecon_id);

if (!$lecon) rediriger('etudiant/catalogue');

$cours_id = $lecon['cours_id'];
if (!$coursModel->estInscrit($_SESSION['user_id'], $cours_id)) {
    set_flash('error', 'Accès non autorisé.');
    rediriger('etudiant/catalogue');
}

// Verifier accessibilite (pour le TP, on permet l'accès si c'est la 1ere non validée ou validée)
$lecons = $progressionModel->listerParCours($cours_id, $_SESSION['user_id']);
$accessible = false;
foreach($lecons as $i => $l) {
    if ($l['id'] == $lecon_id) {
        if ($i == 0 || $lecons[$i-1]['est_valide'] || $l['est_valide']) {
            $accessible = true;
        }
        $lecon_data_prog = $l;
        break;
    }
}

if (!$accessible) {
    set_flash('error', 'Vous devez valider les leçons précédentes.');
    rediriger('etudiant/cours', ['id' => $cours_id]);
}

$evaluation = null;
$questions = [];
$tentatives_restantes = 0;

if ($lecon['evaluation_id']) {
    $evaluation = $evaluationModel->trouverParId($lecon['evaluation_id']);
    $questions = $evaluationModel->listerQuestions($lecon['evaluation_id']);
    // On met par défaut 3 tentatives_max si non défini dans la var
    $max = $evaluation['tentatives_max'] ?? 3;
    $faites = $lecon_data_prog['nb_tentatives'] ?? 0;
    $tentatives_restantes = max(0, $max - $faites);
}

// Hack pour le fichier URL (dans le TP les fichiers sont dans /public/uploads/...)
$file_url = $base_url . '/public/uploads/' . basename($lecon['fichier_url']);

$page_title = $lecon['titre'];
require __DIR__ . '/../layouts/header.php';
?>
<!-- Inject meta tags for JS logic -->
<meta name="lecon-id" content="<?= $lecon_id ?>">

<div class="page-header mb-3">
    <div>
        <a href="<?= $base_url ?>/index.php?page=etudiant/cours&id=<?= $cours_id ?>" class="text-secondary text-sm d-block mb-1">← Retour au plan du cours</a>
        <h1 class="page-title text-xl"><?= e($lecon['titre']) ?></h1>
    </div>
</div>

<div class="lesson-player">
    <div class="player-main">
        <!-- Content Area -->
        <div class="player-content-wrapper">
            <?php if($lecon['type_contenu'] === 'video'): ?>
                <video id="lesson-video" class="player-video" controls controlsList="nodownload">
                    <!-- Pour eviter les pb de chemin absolu/relatif dans le TP -->
                    <source src="<?= e($file_url) ?>" type="video/mp4">
                    Votre navigateur ne supporte pas la lecture vidéo.
                </video>
            <?php elseif($lecon['type_contenu'] === 'pdf'): ?>
                <!-- Note: PDF en iframe local, ou google docs viewer si web public -->
                <iframe id="lesson-pdf" class="player-pdf" src="<?= e($file_url) ?>#toolbar=0" frameborder="0"></iframe>
            <?php endif; ?>
        </div>

        <!-- Quiz Area -->
        <?php if($evaluation): ?>
            <div class="card mt-2 mb-4 p-4">
                <div class="d-flex justify-between align-center mb-3">
                    <div>
                        <h3 class="font-semibold">Évaluation : <?= e($evaluation['titre']) ?></h3>
                        <p class="text-sm text-muted">Note requise : <?= $evaluation['note_de_passage'] ?>% | Tentatives restantes : <?= $tentatives_restantes ?></p>
                    </div>
                </div>

                <div id="resultat-quiz"></div>

                <?php if($lecon_data_prog['est_valide']): ?>
                    <div class="alert alert-success">
                        <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                        <div>
                            <span class="font-bold">Leçon validée !</span><br>
                            Votre score : <?= $lecon_data_prog['meilleur_score'] ?>%
                        </div>
                    </div>
                <?php elseif($tentatives_restantes <= 0): ?>
                    <div class="alert alert-error">
                        Nombre maximal de tentatives atteint. Contactez l'enseignant.
                    </div>
                <?php else: ?>
                    <button id="btn-start-quiz" class="btn btn-disabled w-100 mt-2" style="width:100%;" disabled>
                        Consultez le cours pour débloquer l'évaluation
                    </button>

                    <div id="quiz-section" class="d-none mt-4">
                        <form id="form-quiz-<?= $evaluation['id'] ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="evaluation_id" value="<?= $evaluation['id'] ?>">
                            <input type="hidden" name="cours_id" value="<?= $cours_id ?>">

                            <?php foreach($questions as $index => $q): 
                                $options = json_decode($q['options_json'], true);
                            ?>
                                <div class="quiz-question">
                                    <div class="quiz-question-title"><?= ($index+1) ?>. <?= e($q['question_text']) ?></div>
                                    <?php foreach($options as $lettre => $texte): ?>
                                        <label class="quiz-option">
                                            <input type="radio" name="question_<?= $q['id'] ?>" value="<?= $lettre ?>">
                                            <span><?= $lettre ?>. <?= e($texte) ?></span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            <?php endforeach; ?>

                            <button type="button" class="btn btn-primary" onclick="soumettreEvaluation(<?= $evaluation['id'] ?>)">Soumettre mes réponses</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="card mt-2 p-4">
                <p class="text-muted text-sm m-0">Aucune évaluation associée à cette leçon. La leçon est validée en la consultant.</p>
                <?php if(!$lecon_data_prog['est_valide']): ?>
                    <!-- Formulaire caché pour auto-validation via JS ou bouton manuel -->
                    <button id="btn-start-quiz" class="btn btn-disabled mt-2" disabled>Consultez le cours pour valider</button>
                    <!-- Dans ce TP, si pas de quiz, on peut simuler la validation via AJAX au markViewed (à adapter) -->
                <?php else: ?>
                    <div class="alert alert-success m-0 mt-2 p-2 text-sm">Leçon validée</div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar Right: Lesson List -->
    <div class="player-sidebar d-none d-lg-flex">
        <div class="player-sidebar-header">
            <h3 class="font-semibold text-sm">Plan du cours</h3>
        </div>
        <div class="player-sidebar-list">
            <?php 
            foreach($lecons as $index => $l): 
                $accessible = true;
                if ($index > 0 && !$lecons[$index-1]['est_valide']) $accessible = false;
                $isActive = $l['id'] == $lecon_id;
            ?>
            <div class="lesson-item <?= $isActive ? 'active' : '' ?> <?= $accessible ? 'cursor-pointer' : 'opacity-50' ?>" <?= $accessible && !$isActive ? 'onclick="window.location.href=\''.$base_url.'/index.php?page=etudiant/lecon&id='.$l['id'].'\'"' : '' ?>>
                <div class="lesson-icon <?= $l['est_valide'] ? 'validated' : ($accessible ? 'available' : 'locked') ?>">
                    <?php if($l['est_valide']): ?><svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    <?php elseif($accessible): ?><svg viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    <?php else: ?><svg viewBox="0 0 24 24"><path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg><?php endif; ?>
                </div>
                <div class="lesson-details">
                    <div class="lesson-title <?= $isActive ? 'font-bold' : '' ?>"><?= $l['ordre'] ?>. <?= e($l['titre']) ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
