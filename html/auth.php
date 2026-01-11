<?php
session_start();
date_default_timezone_set('Europe/Paris'); // ou ton fuseau

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['auth']) || $_SESSION['auth'] !== true) {
    header("Location: /login.php");
    exit;
}

// Connexion à SQLite
$pdo = new PDO('sqlite:' . __DIR__ . '/UserData.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Récupère les infos de l'utilisateur
$stmt = $pdo->prepare("SELECT username, created_at, last_login FROM users WHERE username = :u LIMIT 1");
$stmt->execute([':u' => $_SESSION['user']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'utilisateur n'existe plus pour une raison quelconque
if (!$user) {
    session_destroy();
    header("Location: /login.php");
    exit;
}

// Initialiser created_at si vide
if (empty($user['created_at'])) {
    $updateCreated = $pdo->prepare("UPDATE users SET created_at = CURRENT_TIMESTAMP WHERE username = :u");
    $updateCreated->execute([':u' => $_SESSION['user']]);
}

// Mettre à jour last_login à chaque chargement de page
$updateLogin = $pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE username = :u");
$updateLogin->execute([':u' => $_SESSION['user']]);

// Stocker à nouveau dans la session si besoin
$_SESSION['last_login'] = $user['last_login'];
$_SESSION['created_at'] = $user['created_at'];
