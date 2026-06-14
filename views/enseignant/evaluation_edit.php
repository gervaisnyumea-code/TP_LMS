<?php
$evaluation_id = (int)($_GET['id'] ?? 0);
$evaluation = $evaluationModel->trouverParId($evaluation_id);

if (!$evaluation) {
    set_flash('error', 'Évaluation introuvable.');
    rediriger('enseignant/cours');
}

$questions = $evaluationModel->listerQuestions($evaluation_id);
$page_title = 'Gérer le Quiz : ' . $evaluation['titre'];
require __DIR__ . '/../layouts/header.php';
?>

<div class="page-header">
    <div>
        <a href="<?= base_url('index.php?page=enseignant/cours/edit&id=' . $evaluation['cours_id']) ?>" class="text-secondary text-sm d-block mb-1">← Retour au cours</a>
        <h1 class="page-title"><?= e($page_title) ?></h1>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h3 class="font-semibold">Ajouter une question</h3></div>
    <div class="card-body">
        <form action="<?= base_url('index.php?page=enseignant/question_save') ?>" method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="evaluation_id" value="<?= $evaluation_id ?>">
            
            <div class="form-group">
                <label class="form-label">Question</label>
                <input type="text" name="texte" class="input-field" required>
            </div>
            
            <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                <div class="form-group"><label class="form-label">Option A</label><input type="text" name="option_A" class="input-field" required></div>
                <div class="form-group"><label class="form-label">Option B</label><input type="text" name="option_B" class="input-field" required></div>
                <div class="form-group"><label class="form-label">Option C</label><input type="text" name="option_C" class="input-field" required></div>
                <div class="form-group"><label class="form-label">Option D</label><input type="text" name="option_D" class="input-field" required></div>
            </div>
            
            <div class="d-grid gap-2" style="grid-template-columns: 2fr 1fr;">
                <div class="form-group">
                    <label class="form-label">Réponse correcte (A, B, C, ou D)</label>
                    <select name="reponse" class="select-field" required>
                        <option value="A">A</option><option value="B">B</option>
                        <option value="C">C</option><option value="D">D</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ordre</label>
                    <input type="number" name="ordre" class="input-field" value="<?= count($questions) + 1 ?>" required>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary">Ajouter la question</button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3 class="font-semibold">Questions existantes (<?= count($questions) ?>)</h3></div>
    <div class="card-body p-0">
        <table class="table">
            <thead><tr><th>Ordre</th><th>Question</th><th>Réponse</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach($questions as $q): ?>
                <tr>
                    <td><?= $q['ordre'] ?></td>
                    <td><?= e($q['question_text']) ?></td>
                    <td><?= e($q['reponse_correcte']) ?></td>
                    <td>
                        <form action="<?= base_url('index.php?page=enseignant/question_delete') ?>" method="POST" onsubmit="return confirm('Supprimer ?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="question_id" value="<?= $q['id'] ?>">
                            <input type="hidden" name="evaluation_id" value="<?= $evaluation_id ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
