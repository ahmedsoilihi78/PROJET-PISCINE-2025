<?php
session_start();
require_once __DIR__ . '/../connexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];

// Vérifie que l'id du destinataire est passé dans l'URL
if (!isset($_GET['user'])) {
    header("Location: chat_select.php");
    exit();
}
$other_user_id = intval($_GET['user']);

// Sécurité : vérifier que ce user existe (et pas soi-même)
$stmt = $pdo->prepare("SELECT nom, prenom FROM users WHERE id = ? AND id != ?");
$stmt->execute([$other_user_id, $current_user_id]);
$dest = $stmt->fetch();
if (!$dest) {
    die("Destinataire introuvable ou non autorisé.");
}

// Envoi d’un nouveau message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['contenu'])) {
    $contenu = htmlspecialchars($_POST['contenu']);
    $date_envoi = date('Y-m-d H:i:s');
    $type = 'texte';
    $sql = "INSERT INTO messages (sender_id, receiver_id, type, contenu, date_envoi) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$current_user_id, $other_user_id, $type, $contenu, $date_envoi]);
    header("Location: chat.php?user=$other_user_id");
    exit();
}

// Récupérer l'historique des messages
$sql = "SELECT m.*, u.nom, u.prenom 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (sender_id = ? AND receiver_id = ?)
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY date_envoi ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$current_user_id, $other_user_id, $other_user_id, $current_user_id]);
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Chat avec <?= htmlspecialchars($dest['prenom'] . ' ' . $dest['nom']) ?> - Sportify</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        .chat-box { border: 1px solid #ddd; padding: 10px; max-width: 500px; height: 400px; overflow-y: scroll; margin-bottom: 20px;}
        .msg { margin: 8px 0; }
        .from-me { text-align: right; color: #007bff; }
        .from-other { text-align: left; color: #333; }
        .chat-form { display: flex; gap: 10px; }
        .chat-form textarea { width: 80%; }
    </style>
</head>
<body>
<h2>Chat avec <?= htmlspecialchars($dest['prenom'] . ' ' . $dest['nom']) ?></h2>
<div class="chat-box">
    <?php if (count($messages) == 0): ?>
        <div>Aucun message pour l'instant.</div>
    <?php else: ?>
        <?php foreach ($messages as $msg): ?>
            <div class="msg <?= $msg['sender_id'] == $current_user_id ? 'from-me' : 'from-other' ?>">
                <strong><?= $msg['prenom'] ?> <?= $msg['nom'] ?>:</strong>
                <?= nl2br(htmlspecialchars($msg['contenu'])) ?><br>
                <small><?= $msg['date_envoi'] ?></small>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
<form method="post" class="chat-form">
    <textarea name="contenu" rows="2" required placeholder="Tapez votre message ici..."></textarea>
    <button type="submit">Envoyer</button>
</form>
<a href="chat_select.php">Changer de destinataire</a> | <a href="javascript:history.back()">Retour</a>
</body>
</html>
