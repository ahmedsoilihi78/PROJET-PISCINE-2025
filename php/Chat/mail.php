<?php
session_start();
require_once __DIR__ . '/../connexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /../connexion.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];

// On récupère le rôle de l'utilisateur connecté
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$current_user_id]);
$current_user = $stmt->fetch();
$current_role = $current_user ? $current_user['role'] : '';

// Si l'utilisateur est client, il ne voit QUE les coachs et admins (jamais les autres clients)
if ($current_role === 'client') {
    $stmt = $pdo->prepare("SELECT id, nom, prenom, email, role FROM users WHERE id != ? AND role != 'client'");
    $stmt->execute([$current_user_id]);
} else {
    // Pour coach et admin : voient tout le monde sauf eux-mêmes
    $stmt = $pdo->prepare("SELECT id, nom, prenom, email, role FROM users WHERE id != ?");
    $stmt->execute([$current_user_id]);
}
$users = $stmt->fetchAll();

$body = "Bonjour,\n\n"; // Pré-remplissage du corps du mail (modifiable si besoin)
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Envoyer un mail - Sportify</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; background: #fafbfc; }
        .users-list { max-width: 600px; margin: auto; }
        .user-block {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            background: #f8f8f8;
            margin-bottom: 18px;
            padding: 15px 18px;
            border-radius: 10px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }
        .user-info {
            font-weight: bold;
            margin-bottom: 6px;
            min-width: 220px;
            word-break: break-all;
        }
        .user-email {
            color: #888; font-size: 0.95em; font-weight: normal; margin-left: 8px;
        }
        .role {
            color: #7b95bd; font-size: 0.9em; font-weight: normal;
        }
        .buttons {
            display: flex;
            gap: 8px;
            margin-top: 6px;
        }
        a.button {
            background: #0b57d0;
            color: #fff;
            text-decoration: none;
            padding: 8px 17px;
            border-radius: 6px;
            font-weight: 500;
            transition: background 0.2s;
            border: none;
            display: inline-block;
            white-space: nowrap;
        }
        a.button:hover { background: #174ea6; }
        @media (max-width: 650px) {
            .user-block { flex-direction: column; align-items: flex-start; }
            .buttons { margin-top: 12px; }
        }
    </style>
</head>
<body>
<h2 style="text-align: center; color: #174ea6;">Envoyer un mail à un utilisateur</h2>
<div class="users-list">
    <?php foreach ($users as $user):
        $outlook_url = "https://outlook.live.com/mail/0/deeplink/compose?to=" . urlencode($user['email']) .
            "&body=" . urlencode($body);
        ?>
        <div class="user-block">
            <span>
                <span class="user-info"><?= htmlspecialchars($user['prenom'] . " " . $user['nom']) ?>
                    <span class="role">(<?= $user['role'] ?>)</span>
                </span>
                <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
            </span>
            <span class="buttons">
                <a class="button" href="<?= $outlook_url ?>" target="_blank">Outlook Web</a>
            </span>
        </div>
    <?php endforeach; ?>
</div>
<div style="text-align:center; margin-top:30px;">
    <a href="javascript:history.back()" style="color:#0b57d0;text-decoration:underline;">Retour</a>
</div>
</body>
</html>
