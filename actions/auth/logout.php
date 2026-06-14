<?php
/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

require_once __DIR__ . '/../../config/session.php';


/*
 * NOM: NYUMEA PEHA DARYL GERVAIS
 * MATRICULE: 24H2571
 * NIVEAU : LICENCE 2
 * UNIVERSITE : UNIVERSITE DE YAOUNDE 1
 */

session_unset();
session_destroy();

// Start new session to allow flash message
session_name(SESSION_NAME);
session_start();

set_flash('success', 'Vous avez été déconnecté avec succès.');
header('Location: ' . APP_BASE_URL . '/index.php?page=login');
exit;
