<?php
require_once __DIR__ . '/config/constants.php';
echo "BREVO_API_KEY: " . (getenv('BREVO_API_KEY') ? "DEFINIE" : "NON DEFINIE") . "\n";
echo "EMAIL_FROM: " . getenv('EMAIL_FROM') . "\n";
?>
