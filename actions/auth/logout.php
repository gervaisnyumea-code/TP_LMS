<?php
require_once __DIR__ . '/../../config/session.php';

session_unset();
session_destroy();

// Start new session to allow flash message
session_name(SESSION_NAME);
session_start();

set_flash('success', 'Vous avez été déconnecté avec succès.');
header('Location: ' . APP_BASE_URL . '/index.php?page=login');
exit;
