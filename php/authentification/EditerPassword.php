<?php

// demarre la session pour acceder aux variables de session
session_start();

// inclusion du fichier de connexion a la base de donnees
require_once __DIR__ . '/../connexion.php';

// verifie si lutilisateur est connecte sinon redirige vers la page de connexion
if (empty($_SESSION['user_id'])) {
    header('Location: ../../html/authentification/connexion.php');
    exit;
}

// recupere lid de lutilisateur et ses nom et prenom depuis la session
$user_id = (int)$_SESSION['user_id'];
$nom     = $_SESSION['nom']    ?? '';
$prenom  = $_SESSION['prenom'] ?? '';

// recupere les messages derror et de succes precedents depuis la session puis vide ces valeurs de la session
$error   = $_SESSION['error']   ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

// si le formulaire a ete envoye, traite les donnees
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // recupere les mots de passe saisis ou valeurs vides
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // verifie que tous les champs sont remplis
    if ($current === '' || $new === '' || $confirm === '') {
        $error = 'Tous les champs sont obligatoires.';
    }
    // verifie que le nouveau mot de passe et sa confirmation correspondent
    elseif ($new !== $confirm) {
        $error = 'Le nouveau mot de passe et sa confirmation ne correspondent pas.';
    }

    // si pas derreur, verifie que le mot de passe actuel est correct
    if (!$error) {
        // prepare la requete pour recuperer le mot de passe hache de lutilisateur
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // verifie le mot de passe saisi avec celui en base
        if (!$row || !password_verify($current, $row['mot_de_passe'])) {
            $error = 'Le mot de passe actuel est incorrect.';
        }
    }

    // si toujours pas derreur, hash le nouveau mot de passe et met a jour lutilisateur
    if (!$error) {
        // calcule le hash du nouveau mot de passe
        $hash = password_hash($new, PASSWORD_DEFAULT);
        // prepare la requete pour mettre a jour le mot de passe et execute la requete
        $upd = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
        $upd->execute([$hash, $user_id]);
        // definit le message de succes
        $success = 'Votre mot de passe a été mis à jour avec succès.';
    }
}

// stocke les messages derror et de succes dans la session
$_SESSION['error']   = $error;
$_SESSION['success'] = $success;
?>
<div class="userinfo">
    <!-- affiche les infos de lutilisateur connecte -->
    Connecté : ID <?= $user_id ?> – <?= htmlspecialchars($nom . ' ' . $prenom) ?>
</div>

<?php if ($error): ?>
    <!-- si erreur existe, affiche le message derreur -->
    <div class="message error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <!-- si succes existe, affiche le message de succes -->
    <div class="message success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<!-- affiche le formulaire de changement de mot de passe -->
<form method="post" action="">
    <label for="current_password">Mot de passe actuel *</label>
    <input type="password" name="current_password" id="current_password" required>

    <label for="new_password">Nouveau mot de passe *</label>
    <input type="password" name="new_password" id="new_password" required>

    <label for="confirm_password">Confirmer le nouveau mot de passe *</label>
    <input type="password" name="confirm_password" id="confirm_password" required>

    <div class="submit-btn">
        <input type="submit" value="Mettre à jour">
    </div>
</form>
