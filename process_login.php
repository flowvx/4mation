<?php
/**
 * 4Mation — process_login.php (CORRIGÉ)
 */
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

$username = postStr('username');
$password = postStr('password');

if (empty($username) || empty($password)) {
    redirectWithMessage('login.php', 'error', 'Tous les champs sont obligatoires.');
}

$pdo = getPDO();

// Correction ici : on nomme deux paramètres différents
$stmt = $pdo->prepare('SELECT * FROM admins WHERE username = :u1 OR email = :u2');
$stmt->execute([
    ':u1' => $username, 
    ':u2' => $username
]);

$admin = $stmt->fetch();

if ($admin && password_verify($password, $admin['password'])) {
    loginAdmin([
        'id'       => $admin['id'],
        'username' => $admin['username'],
        'nom'      => $admin['nom'],
        'prenom'   => $admin['prenom']
    ]);

    redirectWithMessage('candidatures.php', 'success', 'Ravi de vous revoir, ' . $admin['prenom'] . ' !');
} else {
    redirectWithMessage('login.php', 'error', 'Identifiant ou mot de passe incorrect.');
}