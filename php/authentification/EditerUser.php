<?php

session_start();
require_once __DIR__ . '/../connexion.php';

// 1) Connexion requise
if (empty($_SESSION['user_id'])) {
    header('Location: ../../html/authentification/connexion.php');
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// 2) Messages éventuels (du POST précédent)
$error   = $_SESSION['error']   ?? '';
$success = $_SESSION['success'] ?? '';
unset($_SESSION['error'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3) Traitement du POST
    $nomInput       = trim($_POST['nom']            ?? '');
    $prenomInput    = trim($_POST['prenom']         ?? '');
    $email          = trim($_POST['email']          ?? '');
    $adresse        = trim($_POST['adresse']        ?? '');
    $telephone      = trim($_POST['telephone']      ?? '');
    $carte_etudiant = trim($_POST['carte_etudiant'] ?? '');

    if ($nomInput === '' || $prenomInput === '' || $email === '') {
        $error = 'Nom, prénom et email sont obligatoires.';
    }
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email invalide.';
    }
    else {
        $stmt = $pdo->prepare(
            "SELECT id FROM users WHERE email = ? AND id <> ?"
        );
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error = 'Cet email est déjà utilisé.';
        }
    }

    if (!$error) {
        $upd = $pdo->prepare("
      UPDATE users SET
        nom            = :nom,
        prenom         = :prenom,
        email          = :email,
        adresse        = :adresse,
        telephone      = :telephone,
        carte_etudiant = :carte
      WHERE id = :id
    ");
        $upd->execute([
            ':nom'       => $nomInput,
            ':prenom'    => $prenomInput,
            ':email'     => $email,
            ':adresse'   => $adresse ?: null,
            ':telephone' => $telephone ?: null,
            ':carte'     => $carte_etudiant ?: null,
            ':id'        => $user_id
        ]);

        $_SESSION['nom']    = $nomInput;
        $_SESSION['prenom'] = $prenomInput;
        $_SESSION['email']  = $email;
        $success = 'Profil mis à jour avec succès.';
    }
}

// 4) Recharger les données pour préremplissage
$stmt = $pdo->prepare("
  SELECT nom, prenom, email, adresse, telephone, carte_etudiant
  FROM users
  WHERE id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// 5) Affichage du formulaire + messages
?>
<div class="userinfo">
    Connecté : ID <?= $user_id ?>
    – <?= htmlspecialchars($_SESSION['nom'].' '.$_SESSION['prenom']) ?>
</div>

<?php if ($error): ?>
    <div class="message error"><?= htmlspecialchars($error) ?></div>
<?php endif;?>
<?php if ($success): ?>
    <div class="message success"><?= htmlspecialchars($success) ?></div>
<?php endif;?>

<form method="post" action="">
    <label for="nom">Nom *</label>
    <input type="text" name="nom" id="nom"
           value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>

    <label for="prenom">Prénom *</label>
    <input type="text" name="prenom" id="prenom"
           value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" required>

    <label for="email">Email *</label>
    <input type="email" name="email" id="email"
           value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>

    <label for="adresse">Adresse</label>
    <textarea name="adresse" id="adresse" rows="3"><?= htmlspecialchars($user['adresse'] ?? '') ?></textarea>

    <label for="telephone">Téléphone</label>
    <input type="text" name="telephone" id="telephone"
           value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">

    <label for="carte_etudiant">Carte Étudiant</label>
    <input type="text" name="carte_etudiant" id="carte_etudiant"
           value="<?= htmlspecialchars($user['carte_etudiant'] ?? '') ?>">

    <div class="submit-btn">
        <input type="submit" value="Mettre à jour mon profil">
    </div>
</form>
