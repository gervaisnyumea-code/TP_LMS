<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/constants.php';
require_once __DIR__ . '/../../models/Evaluation.php';
require_once __DIR__ . '/../../models/Progression.php';

$evaluation_id = (int)($_GET['evaluation_id'] ?? 0);
$lecon_id = (int)($_GET['lecon_id'] ?? 0);

if ($evaluation_id <= 0) {
    echo '<p>Evaluation introuvable.</p>';
    exit;
}

$evaluationModel = new Evaluation($pdo);
$evaluation = $evaluationModel->trouverParId($evaluation_id);
$questions = $evaluationModel->listerQuestions($evaluation_id);

if (!$evaluation || empty($questions)) {
    echo '<p>Aucune evaluation configuree pour cette lecon.</p>';
    exit;
}

$progressionModel = new Progression($pdo);
$prog = $progressionModel->trouverProgression($_SESSION['user_id'] ?? 0, $lecon_id);
$tentatives_utilisees = $prog ? (int)$prog['nb_tentatives'] : 0;
$tentatives_restantes = ($evaluation['tentatives_max'] > 0)
    ? max(0, $evaluation['tentatives_max'] - $tentatives_utilisees)
    : PHP_INT_MAX;
$deja_valide = $prog && $prog['valide'];
?>

<div class="quiz-header">
    <h3><?= e($evaluation['titre']) ?></h3>

<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */
?>

    <p class="text-muted">
        <?= count($questions) ?> questions -- Seuil : <?= (int)$evaluation['note_de_passage'] ?>%
        <?php if ($evaluation['tentatives_max'] > 0): ?>
            -- Tentatives restantes : <?= $tentatives_restantes ?>
        <?php endif; ?>
    </p>
</div>

<?php if ($deja_valide): ?>
    <div class="alert alert-success">
        <p>Vous avez deja valide cette evaluation avec un score de <strong><?= (int)$prog['note_obtenue'] ?>%</strong>.</p>
    </div>
<?php elseif ($tentatives_restantes <= 0 && !$deja_valide): ?>
    <div class="alert alert-error">
        <p>Vous avez epuise vos <?= (int)$evaluation['tentatives_max'] ?> tentatives.</p>
    </div>
<?php else: ?>
    <form id="form-quiz-<?= $evaluation_id ?>" class="quiz-container">
        <?php foreach ($questions as $i => $q): ?>
            <div class="quiz-question card mt-2">
                <div class="card-body">
                    <p class="quiz-question-text"><strong>Question <?= $i + 1 ?>.</strong> <?= e($q['question_text']) ?></p>
                    <?php foreach ($q['options'] as $lettre => $texte): ?>
                        <label class="quiz-option">
                            <input type="radio" name="question_<?= $q['id'] ?>" value="<?= e($lettre) ?>" required>
                            <span class="quiz-option-label"><?= e($lettre) ?>.</span>
                            <span class="quiz-option-text"><?= e($texte) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
        <input type="hidden" name="evaluation_id" value="<?= $evaluation_id ?>">
        <input type="hidden" name="lecon_id" value="<?= $lecon_id ?>">
        <?= csrf_field() ?>
        <div class="quiz-actions mt-2">
            <button type="button" class="btn btn-secondary" onclick="fermerQuiz()">Annuler</button>
            <button type="submit" class="btn btn-primary">Soumettre mes reponses</button>
        </div>
    </form>
<?php endif; ?>

<div id="quiz-resultat-<?= $evaluation_id ?>" class="quiz-result mt-2" style="display:none;"></div>
