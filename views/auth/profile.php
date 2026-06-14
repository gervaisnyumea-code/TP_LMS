<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$page_title = 'Mon Profil';
require __DIR__ . '/../layouts/header.php';
$user = $utilisateurModel->trouverParId($_SESSION['user_id']);
?>

<div class="page-header">
    <h1 class="page-title">Mon Profil</h1>
</div>

<div class="card" style="max-width: 600px;">
    <div class="card-body">
        <form action="<?= base_url('index.php?page=auth/profile_update') ?>" method="POST">
            <?= csrf_field() ?>
            
            <div class="form-group">
                <label class="form-label">Email</label>

/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

                <input type="email" name="email" class="input-field" value="<?= e($user['email']) ?>" required>
            </div>
            
            <div class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                <div class="form-group">
                    <label class="form-label">Prénom</label>
                    <input type="text" name="prenom" class="input-field" value="<?= e($user['prenom']) ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input type="text" name="nom" class="input-field" value="<?= e($user['nom']) ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                <input type="password" name="password" class="input-field" minlength="6">
            </div>
            
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
