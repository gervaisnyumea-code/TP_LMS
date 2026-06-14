<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

$current_page = $_GET['page'] ?? '';
$role = $_SESSION['role'] ?? '';
$base_url = base_url();
?>
<aside class="sidebar">
    <div class="sidebar-header">
        <img src="<?= base_url('public/img/logo/LMS.png') ?>" alt="LMS Logo" style="height: 40px; width: auto;">
        <span>LMS</span>
    </div>
    
    <div class="sidebar-user">
        <span class="sidebar-user-name"><?= e($_SESSION['prenom'] ?? '') ?> <?= e($_SESSION['nom'] ?? '') ?></span>
        <span class="sidebar-user-role"><?= e($_SESSION['role'] ?? '') ?></span>
        <a href="<?= $base_url ?>/index.php?page=auth/profile" class="text-sm text-primary mt-1">Éditer mon profil</a>
    </div>

    <nav class="sidebar-nav">
        <?php if ($role === ROLE_ETUDIANT): ?>
            <div class="sidebar-section">
                <div class="sidebar-section-title">Apprentissage</div>
                <a href="<?= $base_url ?>/index.php?page=etudiant/dashboard" class="sidebar-link <?= strpos($current_page, 'etudiant/dashboard') !== false ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    Tableau de bord
                </a>
                <a href="<?= $base_url ?>/index.php?page=etudiant/catalogue" class="sidebar-link <?= strpos($current_page, 'etudiant/catalogue') !== false ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/></svg>
                    Catalogue
                </a>
            </div>
            <div class="sidebar-section">
                <div class="sidebar-section-title">Mes Acquis</div>
                <a href="<?= $base_url ?>/index.php?page=etudiant/certificats" class="sidebar-link <?= strpos($current_page, 'etudiant/certificat') !== false ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
                    Certificats
                </a>
            </div>

<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */
?>


        <?php elseif ($role === ROLE_ENSEIGNANT): ?>
            <div class="sidebar-section">
                <div class="sidebar-section-title">Enseignement</div>
                <a href="<?= $base_url ?>/index.php?page=enseignant/dashboard" class="sidebar-link <?= strpos($current_page, 'enseignant/dashboard') !== false ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    Tableau de bord
                </a>
                <a href="<?= $base_url ?>/index.php?page=enseignant/cours" class="sidebar-link <?= strpos($current_page, 'enseignant/cours') !== false && strpos($current_page, 'edit') === false ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg>
                    Mes Cours
                </a>
                <a href="<?= $base_url ?>/index.php?page=enseignant/statistiques" class="sidebar-link <?= strpos($current_page, 'enseignant/statistiques') !== false ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M16 6l2.29 2.29-4.88 4.88-4-4L2 16.59 3.41 18l6-6 4 4 6.3-6.29L22 12V6z"/></svg>
                    Statistiques
                </a>
            </div>

        <?php elseif ($role === ROLE_PROMOTEUR): ?>
            <div class="sidebar-section">
                <div class="sidebar-section-title">Administration</div>
                <a href="<?= $base_url ?>/index.php?page=promoteur/dashboard" class="sidebar-link <?= strpos($current_page, 'promoteur/dashboard') !== false ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
                    Tableau de bord
                </a>
                <a href="<?= $base_url ?>/index.php?page=promoteur/modules" class="sidebar-link <?= strpos($current_page, 'promoteur/module') !== false ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M4 6H2v14c0 1.1.9 2 2 2h14v-2H4V6zm16-4H8c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-1 9H9V9h10v2zm-4 4H9v-2h6v2zm4-8H9V5h10v2z"/></svg>
                    Modules
                </a>
                <a href="<?= $base_url ?>/index.php?page=promoteur/supervision" class="sidebar-link <?= strpos($current_page, 'promoteur/supervision') !== false ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
                    Supervision
                </a>
            </div>
        <?php endif; ?>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= $base_url ?>/index.php?page=logout" class="sidebar-logout">
            <svg viewBox="0 0 24 24"><path d="M10.09 15.59L11.5 17l5-5-5-5-1.41 1.41L12.67 11H3v2h9.67l-2.58 2.59zM19 3H5c-1.11 0-2 .9-2 2v4h2V5h14v14H5v-4H3v4c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2z"/></svg>
            Déconnexion
        </a>
    </div>
</aside>
