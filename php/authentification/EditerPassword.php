<?php
// php/user/EditPassword.php
session_start();
require_once __DIR__ . '/../connexion.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ../../html/authentification/connexion.php');
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$nom     = $_SESSION['nom']    ?? '';
$prenom  = $_SESSION['prenom'] ?? '';

$error   = $_SESSION['error']   ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current = $_POST['current_password'] ?? '';
    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($current === '' || $new === '' || $confirm === '') {
        $error = 'Tous les champs sont obligatoires.';
    }
    elseif ($new !== $confirm) {
        $error = 'Le nouveau mot de passe et sa confirmation ne correspondent pas.';
    }

    if (!$error) {
        $stmt = $pdo->prepare("SELECT mot_de_passe FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || !password_verify($current, $row['mot_de_passe'])) {
            $error = 'Le mot de passe actuel est incorrect.';
        }
    }

    if (!$error) {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $upd = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
        $upd->execute([$hash, $user_id]);
        $success = 'Votre mot de passe a été mis à jour avec succès.';
    }
}

// Préparation des messages et rechargement des données de session
$_SESSION['error']   = $error;
$_SESSION['success'] = $success;
?>
<div class="userinfo">
    Connecté : ID <?= $user_id ?> – <?= htmlspecialchars($nom . ' ' . $prenom) ?>
</div>

<?php if ($error): ?>
    <div class="message error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
<?php if ($success): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

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
