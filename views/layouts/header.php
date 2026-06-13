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

    <!-- Theme Initialization Script (Before body to prevent flash) -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('lms_theme') || 'light';
            document.documentElement.setAttribute('data-theme', savedTheme);
        })();
    </script>
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
                    <button class="sidebar-toggle" aria-label="Toggle Sidebar">
                        <svg class="icon icon-lg" viewBox="0 0 24 24"><path d="M3 18h18v-2H3v2zm0-5h18v-2H3v2zm0-7v2h18V6H3z"/></svg>
                    </button>
                    <div class="header-title font-bold text-primary d-none d-md-block">
                        <!-- Context title if needed -->
                    </div>
                </div>
                
                <div class="d-flex align-center gap-3">
                    <button class="theme-toggle" id="themeToggleBtn" aria-label="Changer le thème">
                        <!-- Sun Icon (shown in dark mode, hidden in light mode via JS) -->
                        <svg class="icon icon-md sun-icon" style="display: none;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><path d="M12 1v2m0 18v2M4.22 4.22l1.42 1.42m12.72 12.72l1.42 1.42M1 12h2m18 0h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        <!-- Moon Icon (shown in light mode, hidden in dark mode via JS) -->
                        <svg class="icon icon-md moon-icon" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>

                    <div class="user-menu dropdown">
                        <button class="d-flex align-center gap-2 dropdown-toggle" style="background:transparent; border:none; cursor:pointer;">
                            <div class="stat-icon" style="width: 36px; height: 36px;">
                                <svg class="icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                            </div>
                            <span class="font-medium text-text"><?= e($_SESSION['prenom'] ?? '') ?> <?= e($_SESSION['nom'] ?? '') ?></span>
                            <svg class="icon icon-sm text-text-muted" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </button>
                        <div class="dropdown-menu">
                            <a href="<?= $base_url ?>/index.php?page=logout" class="dropdown-item text-error d-flex align-center gap-2">
                                <svg class="icon icon-sm" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                                Déconnexion
                            </a>
                        </div>
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
                    <svg class="icon" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    <?php else: ?>
                    <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                    <?php endif; ?>
                    <?= e($flash['message']) ?>
                </div>
                <?php endif; ?>
