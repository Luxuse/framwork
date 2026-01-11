<?php
session_start();
date_default_timezone_set('Europe/Paris'); // ou ton fuseau
if (!empty($_SESSION['auth'])) {
    header("Location: index.php");
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/UserData.db');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer le username et le password du formulaire
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Récupérer le hash et le grade
    $stmt = $pdo->prepare("SELECT password_hash, grade, created_at FROM users WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['auth'] = true;
        $_SESSION['user'] = $username;
        $_SESSION['grade'] = $user['grade'];

        // Initialiser created_at si vide
        if (empty($user['created_at'])) {
            $now = date('Y-m-d H:i:s'); // heure locale
            $updateCreated = $pdo->prepare("UPDATE users SET created_at = CURRENT_TIMESTAMP WHERE username = :u");
            $updateCreated->execute([':u' => $username]);
        }

        // Mettre à jour last_login
        $now = date('Y-m-d H:i:s'); // heure locale
        $updateLogin = $pdo->prepare("UPDATE users SET last_login = :now WHERE username = :u");
        $updateLogin->execute([':now' => $now, ':u' => $_SESSION['user']]);


        header("Location: index.php");
        exit;
    }

    $error = "Nom d'utilisateur ou mot de passe invalide";
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <style>
        body {
            font-family: sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background: #f0f2f5;
        }

        form {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, .1);
        }

        input,
        button {
            width: 100%;
            padding: .6rem;
            margin-bottom: 1rem;
        }

        button {
            background: #007bff;
            color: white;
            border: 0;
            border-radius: 4px;
            cursor: pointer;
        }

        .error {
            color: red;
            font-size: .8rem;
        }
    </style>
</head>

<body>

    <form method="post">
        <h3>Connexion</h3>
        <input type="text" name="username" placeholder="Nom d'utilisateur" required autofocus>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit">Connexion</button>
        <?php if ($error): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </form>

</body>

</html>