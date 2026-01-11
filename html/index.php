<?php
require 'auth.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <style>
        body { font-family: sans-serif; padding: 2rem; background: #f0f2f5; }
        .btn { padding: .5rem 1rem; margin-right: .5rem; text-decoration: none; color: white; border-radius: 4px; }
        .logout { background: #d9534f; }
        .admin { background: #0275d8; }
    </style>
</head>
<body>

    <h1>Bienvenue <?= htmlspecialchars($_SESSION['user']) ?></h1>
    <p>Créé le : <?= htmlspecialchars($_SESSION['created_at']) ?></p>
    <p>Dernière connexion : <?= htmlspecialchars($_SESSION['last_login']) ?></p>

    <div>
        <a href="logout.php" class="btn logout">Déconnexion</a>

        <?php if ($_SESSION['grade'] == 1): ?>
            <a href="backend/index.php" class="btn admin">Admin Page</a>
        <?php endif; ?>
    </div>

</body>
</html>
