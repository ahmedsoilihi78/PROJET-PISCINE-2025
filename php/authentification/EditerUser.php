<?php

// demarre la session pour pouvoir utiliser les variables de session
session_start();
// inclut le fichier de connexion a la base de donnees
require_once __DIR__ . '/../connexion.php';

// verifie si l utilisateur est connecte sinon redirige vers la page de connexion
if (empty($_SESSION['user_id'])) {
    header('Location: ../connexion.php');
    exit;
}
$user_id = (int)$_SESSION['user_id'];

// recupere les messages d erreur ou de succes precedemment stockes dans la session
$error   = $_SESSION['error']   ?? '';
$success = $_SESSION['success'] ?? '';
// supprime les messages de la session apres les avoir recupere
unset($_SESSION['error'], $_SESSION['success']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // recupere et nettoie les donnees recues du formulaire
    $nomInput       = trim($_POST['nom']            ?? '');
    $prenomInput    = trim($_POST['prenom']         ?? '');
    $email          = trim($_POST['email']          ?? '');
    $adresse        = trim($_POST['adresse']        ?? '');
    $telephone      = trim($_POST['telephone']      ?? '');
    $carte_etudiant = trim($_POST['carte_etudiant'] ?? '');

    // verifie que les champs nom prenom et email ne sont pas vides
    if ($nomInput === '' || $prenomInput === '' || $email === '') {
        $error = 'nom prenom et email sont obligatoires.';
    }
    // verifie que l email a un format valide
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'email invalide.';
    }
    else {
        // verifie que l email n est pas deja utilise par un autre utilisateur
        $stmt = $pdo->prepare(
            "SELECT id FROM users WHERE email = ? AND id <> ?"
        );
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $error = 'cet email est deja utilise.';
        }
    }

    if (!$error) {
        // prepare et execute la requete pour mettre a jour les informations de l utilisateur
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

        // met a jour les valeurs de nom prenom et email dans la session
        $_SESSION['nom']    = $nomInput;
        $_SESSION['prenom'] = $prenomInput;
        $_SESSION['email']  = $email;
        // definit le message de succes
        $success = 'profil mis a jour avec succes.';
    }
}

// recupere les donnees de l utilisateur pour pre remplir le formulaire
$stmt = $pdo->prepare("
  SELECT nom, prenom, email, adresse, telephone, carte_etudiant
  FROM users
  WHERE id = ?
");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<div class="userinfo">
    <!-- affiche l id et le nom et prenom de l utilisateur connecte -->
    Connecte : ID <?= $user_id ?>
    – <?= htmlspecialchars($_SESSION['nom'].' '.$_SESSION['prenom']) ?>
</div>

<?php if ($error): ?>
    <!-- affiche le message d erreur si present -->
    <div class="message error"><?= htmlspecialchars($error) ?></div>
<?php endif;?>
<?php if ($success): ?>
    <!-- affiche le message de succes si present -->
    <div class="message success"><?= htmlspecialchars($success) ?></div>
<?php endif;?>

<form method="post" action="">
    <!-- champ pour modifier le nom -->
    <label for="nom">Nom *</label>
    <input type="text" name="nom" id="nom"
           value="<?= htmlspecialchars($user['nom'] ?? '') ?>" required>

    <!-- champ pour modifier le prenom -->
    <label for="prenom">Prenom *</label>
    <input type="text" name="prenom" id="prenom"
           value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" required>

    <!-- champ pour modifier l email -->
    <label for="email">Email *</label>
    <input type="email" name="email" id="email"
           value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>

    <!-- champ pour modifier l adresse (facultatif) -->
    <label for="adresse">Adresse</label>
    <textarea name="adresse" id="adresse" rows="3"><?= htmlspecialchars($user['adresse'] ?? '') ?></textarea>

    <!-- champ pour modifier le telephone (facultatif) -->
    <label for="telephone">Telephone</label>
    <input type="text" name="telephone" id="telephone"
           value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">

    <!-- champ pour modifier la carte etudiant (facultatif) -->
    <label for="carte_etudiant">Carte Etudiant</label>
    <input type="text" name="carte_etudiant" id="carte_etudiant"
           value="<?= htmlspecialchars($user['carte_etudiant'] ?? '') ?>">

    <!-- bouton pour soumettre le formulaire -->
    <div class="submit-btn">
        <input type="submit" value="mettre a jour mon profil">
    </div>
</form>
