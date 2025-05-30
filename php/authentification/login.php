<?php
// PARTIE PHP DU CODE LOGIN A NE PAS MODIFIER
session_start();
require_once __DIR__ . '/../connexion.php';

$message_success = "";
$erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email        = trim($_POST['email']);
    $mot_de_passe = $_POST['mot_de_passe'];

    // Préparation et exécution
    $sql  = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch();  // on récupère directement

    if ($user) {
        // Vérification du mot de passe (haché)
        if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
            // Mise en session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['prenom']  = $user['prenom'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            $message_success = "Connexion réussie. Redirection en cours...";

            // Choix de la redirection
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
                    $redirect_url = 'login.php';
            }

            exit;
        } else {
            $erreur = "M";
        }
    } else {
        $erreur = "Email non reconnu.";
    }
}
?>

<!DOCTYPE html>

<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Sportify</title>
</head>
<body>
<h2>Connexion à Sportify</h2>

<?php if ($message_success) : ?>
    <p style="color:green;"><?= $message_success ?></p>
<?php elseif ($erreur) : ?>
    <p style="color:red;"><?= $erreur ?></p>
<?php endif; ?>

<form method="post" action="">
    <label>Email :</label><br>
    <input type="email" name="email" required><br><br>

    <label>Mot de passe :</label><br>
    <input type="password" name="mot_de_passe" required><br><br>

    <input type="submit" value="Se connecter">
</form>
</body>
</html>
