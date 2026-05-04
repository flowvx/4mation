<?php
/**
 * 4Mation — logout.php
 * Déconnexion sécurisée de l'admin.
 */
require_once __DIR__ . '/includes/functions.php';
logoutAdmin(); // Détruit la session et redirige vers login.html
