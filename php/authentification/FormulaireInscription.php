<?php

session_start();
// Récupération & vidage des messages de session
$error   = $_SESSION['error']   ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription – Sportify</title>
    <style>
        body { font-family: Arial, sans-serif; background: #fafafa; padding: 20px; }
        .container { max-width: 500px; margin: auto; background: #fff;
            padding: 20px; border: 1px solid #ddd; border-radius: 6px; }
        h2 { text-align: center; }
        label { display: block; margin-top: 15px; }
        input, textarea { width: 100%; padding: 8px; margin-top: 5px;
            box-sizing: border-box; border:1px solid #ccc; border-radius:4px; }
        .submit-btn { margin-top: 20px; text-align: center; }
        .submit-btn input { padding: 10px 20px; }
        .message { text-align: center; padding: 10px; margin-bottom: 10px; }
        .message.error   { color: #900; background: #fdd; border:1px solid #f99; }
        .message.success { color: #060; background: #dfd; border:1px solid #9f9; }
    </style>
</head>
<body>

<div class="container">
    <h2>Créer un compte Sportify</h2>

    <?php if ($error): ?>
        <div class="message error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="message success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form id="inscriptionForm" action="inscription.php" method="POST">
        <label for="nom">Nom *</label>
        <input type="text" name="nom" id="nom" required>

        <label for="prenom">Prénom *</label>
        <input type="text" name="prenom" id="prenom" required>

        <label for="email">Adresse e-mail *</label>
        <input type="email" name="email" id="email" required>

        <label for="mot_de_passe">Mot de passe *</label>
        <input type="password" name="mot_de_passe" id="mot_de_passe" required>

        <label for="confirm_password">Confirmer le mot de passe *</label>
        <input type="password" name="confirm_password" id="confirm_password" required>

        <label for="adresse">Adresse</label>
        <textarea name="adresse" id="adresse" rows="3"></textarea>

        <label for="telephone">Téléphone</label>
        <input type="text" name="telephone" id="telephone">

        <label for="carte_etudiant">Carte Étudiant</label>
        <input type="text" name="carte_etudiant" id="carte_etudiant">

        <div class="submit-btn">
            <input type="submit" value="Créer mon compte">
        </div>
    </form>
</div>

</body>
</html>

