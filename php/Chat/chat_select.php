<?php
session_start();
require_once __DIR__ . '/../connexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];

// Récupère tous les utilisateurs sauf soi-même
$stmt = $pdo->prepare("SELECT id, nom, prenom, role FROM users WHERE id != ?");
$stmt->execute([$current_user_id]);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Choisir un destinataire</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        .users-list { max-width: 400px; margin: auto; }
        .user-link { display: block; margin-bottom: 12px; padding: 10px; background: #f8f8f8; border-radius: 6px; text-decoration: none; color: #333; }
        .user-link:hover { background: #d4e2ff; }
    </style>
</head>
<body>
<h2>Choisissez un destinataire</h2>
<div class="users-list">
    <?php foreach ($users as $u): ?>
        <a class="user-link" href="chat.php?user=<?= $u['id'] ?>">
            <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
            <span style="color: #888; font-size: 0.9em;">(<?= $u['role'] ?>)</span>
        </a>
    <?php endforeach; ?>
</div>
<a href="javascript:history.back()">Retour</a>
</body>
</html>

