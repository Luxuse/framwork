<?php
require 'auth.php';

$error = null;
$success = null;

// Ajouter un nouvel utilisateur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $grade = intval($_POST['grade'] ?? 2);

    if ($username === '' || $password === '') {
        $error = "Username et mot de passe requis";
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        try {
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, grade, created_at) VALUES (:u, :p, :g, CURRENT_TIMESTAMP)");
            $stmt->execute([':u' => $username, ':p' => $hash, ':g' => $grade]);
            $success = "Utilisateur '$username' créé !";
        } catch (Exception $e) {
            $error = "Erreur : " . $e->getMessage();
        }
    }
}

// Supprimer un utilisateur
if (isset($_GET['delete'])) {
    $del = $_GET['delete'];

    // On empêche de supprimer soi-même
    if ($del === $_SESSION['user']) {
        $error = "Impossible de supprimer votre propre compte";
    } else {
        $stmt = $pdo->prepare("DELETE FROM users WHERE username = :u");
        $stmt->execute([':u' => $del]);
        $success = "Utilisateur '$del' supprimé !";
    }
}

// Récupérer tous les utilisateurs
$users = $pdo->query("SELECT username, grade, created_at, last_login FROM users ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Gestion des utilisateurs</title>
<style>
body { font-family: sans-serif; padding: 2rem; background: #f0f2f5; }
table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
th, td { padding: 0.5rem; border: 1px solid #ccc; text-align: left; }
.admin { color: red; font-weight: bold; }
.error { color: red; }
.success { color: green; }
form { margin-top: 1rem; }
input, select { padding: .4rem; margin-right: .5rem; }
button { padding: .4rem; cursor: pointer; }
</style>
</head>
<body>

<h1>Gestion des utilisateurs</h1>

<?php if ($error): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<h2>Ajouter un utilisateur</h2>
<form method="post">
    <input type="hidden" name="action" value="add">
    <input type="text" name="username" placeholder="Nom d'utilisateur" required>
    <input type="password" name="password" placeholder="Mot de passe" required>
    <select name="grade">
        <option value="2">Utilisateur simple</option>
        <option value="1">Admin</option>
    </select>
    <button type="submit">Créer</button>
</form>

<h2>Liste des utilisateurs</h2>
<table>
    <tr>
        <th>Username</th>
        <th>Grade</th>
        <th>Créé le</th>
        <th>Dernière connexion</th>
        <th>Action</th>
    </tr>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?= htmlspecialchars($u['username']) ?></td>
            <td><?= $u['grade']==1 ? '<span class="admin">Admin</span>' : 'Utilisateur' ?></td>
            <td><?= htmlspecialchars($u['created_at']) ?></td>
            <td><?= htmlspecialchars($u['last_login']) ?></td>
            <td>
                <?php if ($u['username'] !== $_SESSION['user']): ?>
                    <a href="?delete=<?= urlencode($u['username']) ?>" onclick="return confirm('Supprimer <?= htmlspecialchars($u['username']) ?> ?')">Supprimer</a>
                <?php else: ?>
                    -
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<a href="../index.php">Retour au site</a>

</body>
</html>
