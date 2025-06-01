<?php
// demarre la session pour utiliser les variables de session
session_start();
// inclut le fichier de connexion a la base de donnees
require_once __DIR__ . '/../connexion.php';

// initialise le message de succes et la variable d erreur
$message_success = "";
$erreur = "";

// si le formulaire est soumis en methode POST, on traite les donnees
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // recupere et nettoie l adresse email et le mot de passe saisis
    $email        = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // prepare la requete pour recuperer l utilisateur par email
    $sql  = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    // recupere les donnees de l utilisateur si trouve
    $user = $stmt->fetch();

    // si l utilisateur existe
    if ($user) {
        // verifie que le mot de passe saisi correspond au mot de passe hache en base
        if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
            // stocke les informations de l utilisateur dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['prenom']  = $user['prenom'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            // definit le message de succes avant redirection
            $message_success = "connexion reussie. redirection en cours...";

            // selectionne l url de redirection en fonction du role de l utilisateur
            switch ($user['role']) {
                case 'admin':
                    $redirect_url = '../admin/dashboard.php';
                    break;
                case 'coach':
                    $redirect_url = '../coach/espace_coach.php';
                    break;
                case 'client':
                    $redirect_url = '../client/espace_client.php';
                    break;
                default:
                    // si le role n est pas reconnu, on reste sur la page de connexion
                    $redirect_url = 'login.php';
            }

            // redirection vers l url correspondante
            header("Location: " . $redirect_url);
            exit;
        } else {
            // si le mot de passe est incorrect, on definit un message d erreur
            $erreur = "mot de passe incorrect.";
        }
    } else {
        // si aucun utilisateur ne correspond a cet email, on definit un message d erreur
        $erreur = "email non reconnu.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>connexion - sportify</title>
</head>
<body>
<h2>connexion a sportify</h2>

<!-- affiche le message de succes ou derreur si present -->
<?php if ($message_success) : ?>
    <p style="color:green;"><?= htmlspecialchars($message_success) ?></p>
<?php elseif ($erreur) : ?>
    <p style="color:red;"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<!-- formulaire de connexion avec champs email et mot de passe -->
<form method="post" action="">
    <label for="email">email :</label><br>
    <input type="email" name="email" id="email" required><br><br>

    <label for="mot_de_passe">mot de passe :</label><br>
    <input type="password" name="mot_de_passe" id="mot_de_passe" required><br><br>

    <input type="submit" value="se connecter">
</form>
</body>
</html>
