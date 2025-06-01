<?php
// demarre la session pour pouvoir stocker et recuperer des informations entre les pages
session_start();
// inclut le fichier de connexion a la base de donnees pour pouvoir utiliser $pdo
require_once __DIR__ . '/../connexion.php';
// initialise un indicateur pour savoir si un utilisateur est deja connecte
$utilisateur_connecte = false;

// si la session contient deja un utilisateur connecté et son role
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    // redirige selon le role de l utilisateur
    switch ($_SESSION['role']) {
        case 'admin':
            // si l utilisateur est admin, redirige vers la page admin
            header("Location: ../../html/admin.html");
            exit;
        case 'coach':
            // si l utilisateur est coach, redirige vers la page coach
            header("Location: ../../html/coach.html");
            exit;
        case 'client':
            // si l utilisateur est client, redirige vers la page client
            header("Location: ../../html/client.html");
            exit;
        default:
            // si le role n est pas reconnu, redirige vers la page d accueil
            header("Location: ../../html/accueil.html");
            exit;
    }
}

// si l utilisateur n est pas encore connecté, on traite la soumission du formulaire
$message_success = "";
$erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // recupere et nettoie l email saisi
    $email        = trim($_POST['email']);
    // recupere le mot de passe saisi (non nettoye pour pouvoir verifier avec password_verify)
    $mot_de_passe = $_POST['mot_de_passe'];

    // prepare la requete pour chercher l utilisateur par son email
    $sql  = "SELECT * FROM users WHERE email = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    // recupere la ligne correspondante s il y en a une
    $user = $stmt->fetch();

    if ($user) {
        // si un utilisateur est trouve, on verifie le mot de passe
        if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
            // si le mot de passe correspond, on stocke les infos utilisateur dans la session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nom']     = $user['nom'];
            $_SESSION['prenom']  = $user['prenom'];
            $_SESSION['email']   = $user['email'];
            $_SESSION['role']    = $user['role'];

            // redirection immediat apres connexion reussie, selon le role
            switch ($user['role']) {
                case 'admin':
                    header("Location: ../../html/admin.html");
                    exit;
                case 'coach':
                    header("Location: ../../html/coach.html");
                    exit;
                case 'client':
                    header("Location: ../../html/client.html");
                    exit;
            }
        } else {
            // si le mot de passe ne correspond pas, on definit un message d erreur
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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <!-- titre de l onglet de navigation -->
    <title>Sportify - Votre compte</title>
    <!-- inclusion des fichiers css de base et de la page votre_compte -->
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/votre_compte.css" />
</head>
<body>
<div class="wrapper">

    <!-- HEADER -->
    <header class="header">
        <div class="title">
            <!-- titre principal de la page -->
            <h1><span class="red">Sportify:</span> <span class="blue">Consultation sportive</span></h1>
        </div>
        <div class="logo">
            <!-- lien vers la page d accueil avec le logo -->
            <a href="../../html/accueil.html">
                <img src="../../images_accueil/Logo_sportify.png" alt="logo sportify" />
            </a>
        </div>
    </header>

    <!-- NAVIGATION PRINCIPALE -->
    <nav class="navigation">
        <!-- bouton pour aller a l accueil -->
        <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
        <!-- bouton pour derouler le menu tout parcourir -->
        <button onclick="toggleParcourir()" aria-expanded="false" aria-controls="parcourirLinks">Tout Parcourir</button>
        <!-- bouton redirigeant vers la page de prise de rendez-vous -->
        <button onclick="window.location.href='../../html/recherche/barre_recherche.html'">Rendez-vous</button>
        <!-- bouton redirigeant vers la page de consultation des rendez-vous -->
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-vous</button>
        <!-- bouton vers la page de connexion/compte -->
        <button onclick="window.location.href='votre_compte.php'">Votre compte</button>
    </nav>

    <?php if ($utilisateur_connecte): ?>
        <!-- si l utilisateur est connecte, affiche le bouton de deconnexion -->
        <div style="text-align: right; margin: 10px 20px;">
            <form method="post" action="logout.php" style="display: inline;">
                <button type="submit" class="btn-deconnexion">Déconnexion</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- LIENS DEROULES POUR LE MENU 'TOUT PARCOURIR' -->
    <div class="parcourir-dropdown" id="parcourirLinks" style="display: none;">
        <!-- lien vers la page activites sportives -->
        <a href="../tout parcourir/activites_sportives.php">Activités Sportives</a>
        <!-- lien vers la page sports de competition -->
        <a href="../tout parcourir/sports_competition.php">Les sports de compétition</a>
        <!-- lien vers la page salle de sport omnes -->
        <a href="../../html/salle_de_sport.html">Salle de sport omnes</a>
    </div>

    <!-- FORMULAIRE DE RECHERCHE DANS LE MENU -->
    <div id="rechercheContainer" class="recherche-form">
        <form method="get" action="../../html/recherche/barre_recherche.html">
            <!-- champ de recherche texte -->
            <input type="text" name="q" placeholder="rechercher..." />
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- SECTION PRINCIPALE : FORMULAIRE DE CONNEXION -->
    <section class="main-section">
        <h2>connexion a votre compte</h2>

        <!-- affiche un message de succes ou d erreur si definit -->
        <?php if ($message_success): ?>
            <p style="color: green;"><?= htmlspecialchars($message_success) ?></p>
        <?php elseif ($erreur): ?>
            <p style="color: red;"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <!-- formulaire de connexion avec les champs email et mot de passe -->
        <form class="login-form" method="POST" action="votre_compte.php">
            <!-- champ email obligatoire -->
            <label for="identifiant">email :</label>
            <input type="email" id="identifiant" name="email" required />

            <!-- champ mot de passe obligatoire -->
            <label for="motdepasse">mot de passe :</label>
            <input type="password" id="motdepasse" name="mot_de_passe" required />

            <!-- bouton pour se connecter -->
            <button type="submit" class="btn-connexion">Connexion</button>
            <!-- bouton pour aller a la page d inscription -->
            <button type="button" class="btn-inscription" onclick="window.location.href='FormulaireInscription.php'">inscription</button>
        </form>
    </section>

    <!-- FOOTER AVEC LES INFORMATIONS DE CONTACT -->
    <footer class="footer">
        <h3>Contactez-nous</h3>
        <!-- adresse email de contact -->
        <p>Email : <a href="mailto:contact@sportify.fr">contact@sportify.fr</a></p>
        <!-- numero de telephone de contact -->
        <p>Telephone : +33 1 23 45 67 89</p>
        <!-- adresse postale -->
        <p>Adresse : 10 rue sextius michel, 75015 paris, france</p>
        <!-- integration d une carte google maps pour l adresse -->
        <div class="map">
            <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2625.3724386130675!2d2.2859626761368914!3d48.851108001219515!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e6701b486bb253%3A0x61e9cc6979f93fae!2s10%20Rue%20Sextius%20Michel%2C%2075015%20Paris!5e0!3m2!1sfr!2sfr!4v1748272681968!5m2!1sfr!2sfr"
                    width="100%"
                    height="250"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy">
            </iframe>
        </div>
    </footer>

</div>

<!-- inclusion du script javascript commun -->
<script src="../../js/bases.js"></script>
</body>
</html>
