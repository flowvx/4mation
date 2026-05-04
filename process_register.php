<?php
/**
 * 4Mation — process_register.php (CORRIGÉ)
 */
require_once __DIR__ . '/includes/functions.php';

$prenom   = postStr('prenom');
$nom      = postStr('nom');
$email    = postStr('email');
$password = postStr('password');
$confirm  = postStr('password_confirm');

if ($password !== $confirm) {
    redirectWithMessage('login.php', 'error', 'Les mots de passe ne correspondent pas.');
}

$pdo = getPDO();
$hash = password_hash($password, PASSWORD_DEFAULT);
$username = normalizeText($prenom . '.' . $nom) . rand(10, 99);

try {
    $ins = $pdo->prepare('INSERT INTO admins (username, password, email, nom, prenom) VALUES (?, ?, ?, ?, ?)');
    $ins->execute([$username, $hash, $email, $nom, $prenom]);
    
    loginAdmin([
        'id' => $pdo->lastInsertId(),
        'username' => $username,
        'nom' => $nom,
        'prenom' => $prenom
    ]);

    redirectWithMessage('candidatures.php', 'success', 'Compte créé ! Identifiant : ' . $username);

} catch (PDOException $e) {
    // Affiche l'erreur si la création échoue (ex: colonne manquante)
    die("Erreur lors de la création : " . $e->getMessage());
}