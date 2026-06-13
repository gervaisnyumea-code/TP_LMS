<?php
$page_title = $page_title ?? APP_NAME;
$base_url = base_url();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> - <?= e(APP_NAME) ?></title>
    
    <!-- Meta tags for JS -->
    <meta name="base-url" content="<?= $base_url ?>">
    <?php if(isset($_SESSION['csrf_token'])): ?>
    <meta name="csrf-token" content="<?= e($_SESSION['csrf_token']) ?>">
    <?php endif; ?>

    <!-- CSS -->
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/reset.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/variables.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/base.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/components.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/layout.css">
    <link rel="stylesheet" href="<?= $base_url ?>/public/css/pages.css">
</head>
<body>
    <div class="app-layout">
        <!-- Sidebar Overlay for mobile -->
        <div class="sidebar-overlay"></div>

        <!-- Sidebar -->
        <?php require __DIR__ . '/sidebar.php'; ?>

        <!-- Main Wrapper -->
        <div class="main-wrapper">
            <!-- Header Bar -->
            <header class="header-bar">
                <div class="d-flex align-center gap-3">
                    <button class="sidebar-toggle">
                        <svg viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
                    </button>
                    <div class="header-title font-bold text-primary d-none d-md-block">
                        <!-- Space for context specific title if needed -->
                    </div>
                </div>
                
                <div class="user-menu dropdown">
                    <button class="d-flex align-center gap-2 dropdown-toggle">
                        <div class="stat-icon" style="width: 36px; height: 36px;">
                            <svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>
                        </div>
                        <span class="font-medium"><?= e($_SESSION['prenom'] ?? '') ?> <?= e($_SESSION['nom'] ?? '') ?></span>
                        <svg viewBox="0 0 24 24" style="width: 16px; height: 16px;"><path d="M7 10l5 5 5-5z"/></svg>
                    </button>
                    <div class="dropdown-menu">
                        <a href="<?= $base_url ?>/index.php?page=logout" class="dropdown-item text-error">
                            Déconnexion
                        </a>
                    </div>
                </div>
            </header>

            <!-- Main Content -->
            <main class="main-content">
                <?php 
                $flash = get_flash();
                if ($flash): 
                ?>
                <div class="alert alert-<?= e($flash['type']) ?>">
                    <?php if($flash['type'] === 'success'): ?>
                    <svg viewBox="0 0 24 24"><path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/></svg>
                    <?php else: ?>
                    <svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/></svg>
                    <?php endif; ?>
                    <?= e($flash['message']) ?>
                </div>
                <?php endif; ?>
