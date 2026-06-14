<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$page_title = 'Certificat';
require_once __DIR__ . '/../layouts/header.php';

require_once __DIR__ . '/../../models/Certificat.php';
require_once __DIR__ . '/../../models/Utilisateur.php';

exiger_role(ROLE_ETUDIANT);

$certificat_id = (int)($_GET['id'] ?? 0);
$certificatModel = new Certificat($pdo);
$certificat = $certificatModel->trouverParId($certificat_id);

if (!$certificat || $certificat['etudiant_id'] != $_SESSION['user_id']) {
    set_flash('error', 'Certificat introuvable.');
    rediriger('etudiant/certificats');
}

$etudiant = (new Utilisateur($pdo))->trouverParId($_SESSION['user_id']);

$stmt = $pdo->prepare("SELECT c.titre FROM cours c WHERE c.module_id = ? ORDER BY c.titre");
$stmt->execute([$certificat['module_id']]);
$cours_module = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<div class="certificate-wrapper">
    <div class="no-print text-center mb-2">
        <a href="<?= base_url('index.php?page=etudiant/certificats') ?>" class="btn btn-secondary">Retour</a>
        <button onclick="window.print()" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

            Imprimer / Sauvegarder PDF
        </button>
    </div>

    <div class="certificate">
        <div class="certificate-border">
            <div class="certificate-logo text-center">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--color-primary)" stroke-width="1.5"><circle cx="12" cy="8" r="7"/><polyline points="8.21 13.89 7 23 12 20 17 23 15.79 13.88"/></svg>
            </div>
            <h1 class="certificate-title">CERTIFICAT DE REUSSITE</h1>
            <p class="certificate-platform"><?= e(APP_NAME) ?></p>
            <div class="certificate-body">
                <p class="certificate-label">Ce certificat est decerne a</p>
                <h2 class="certificate-name"><?= e($etudiant['prenom'] . ' ' . $etudiant['nom']) ?></h2>
                <p class="certificate-label">Pour la validation du module</p>
                <h3 class="certificate-module"><?= e($certificat['module_titre']) ?></h3>
                <?php if (!empty($cours_module)): ?>
                    <div class="certificate-courses">
                        <p class="certificate-label">Cours composant ce module :</p>
                        <ul><?php foreach ($cours_module as $t): ?><li><?= e($t) ?></li><?php endforeach; ?></ul>
                    </div>
                <?php endif; ?>
                <div class="certificate-footer">
                    <div class="certificate-date">
                        <p class="certificate-label">Date de delivrance</p>
                        <p><?= date('d F Y', strtotime($certificat['date_delivrance'])) ?></p>
                    </div>
                    <div class="certificate-code">
                        <p class="certificate-label">Code de verification</p>
                        <p class="certificate-code-value"><?= e($certificat['code_verification']) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
