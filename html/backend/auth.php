<?php
session_start();

// Vérifie si connecté
if (empty($_SESSION['auth'])) {
    header("Location: ../login.php");
    exit;
}

// Vérifie si admin
if ($_SESSION['grade'] != 1) {
    die("Accès refusé : vous n'êtes pas administrateur.");
}

// Connexion SQLite
$pdo = new PDO('sqlite:' . __DIR__ . '/../UserData.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
