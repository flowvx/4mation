<?php
$host = getenv('MYSQLHOST')     ?: 'localhost';
$port = getenv('MYSQLPORT')     ?: '3306';
$user = getenv('MYSQLUSER')     ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: 'root';
$db   = getenv('MYSQLDATABASE') ?: '4mation_bd';

try { 
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) { 
    error_log("Erreur de connexion : " . $e->getMessage());
    die("Désolé, une erreur technique est survenue." . $e->getMessage());
}
 
function getPDO() {
    global $pdo;
    return $pdo;
}