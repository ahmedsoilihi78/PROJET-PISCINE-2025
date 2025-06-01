<?php
// demarre la session pour acceder aux variables de session
session_start();
// inclut le fichier de connexion a la base de donnees pour utiliser pdo
require_once __DIR__ . '/../connexion.php';

// verifie si l utilisateur n est pas connecte et redirige vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: /../connexion.php");
    exit();
}
// recupere l id de l utilisateur courant depuis la session
$current_user_id = $_SESSION['user_id'];

// prepare une requete pour recuperer le role de l utilisateur courant
$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$current_user_id]);
// recupere le resultat de la requete
$current_user = $stmt->fetch();
// stocke le role ou une chaine vide si aucun resultat
$current_role = $current_user ? $current_user['role'] : '';

// si l utilisateur est de role client, on ne selectionne que les utilisateurs non clients
if ($current_role === 'client') {
    // prepare la requete pour recuperer les utilisateurs autres que client et different de l utilisateur courant
    $stmt = $pdo->prepare("SELECT id, nom, prenom, email, role FROM users WHERE id != ? AND role != 'client'");
    $stmt->execute([$current_user_id]);
} else {
    // pour les autres roles, on recupere tous les utilisateurs sauf l utilisateur courant
    $stmt = $pdo->prepare("SELECT id, nom, prenom, email, role FROM users WHERE id != ?");
    $stmt->execute([$current_user_id]);
}
// recupere la liste des utilisateurs selon le role
$users = $stmt->fetchAll();

// initialise le corps du message pour l invitation teams
$body = "bonjour, je vous propose une visioconference via teams.\n\n";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <!-- titre de l onglet du navigateur -->
    <title>visioconference teams - sportify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <!-- inclusion des styles de base et de la page mail -->
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/mail.css" />
</head>
<body>
<div class="wrapper">

    <!-- HEADER : titre et logo du site -->
    <header class="header">
        <div class="title">
            <!-- affichage du nom du site avec mise en forme par classes css -->
            <h1><span class="red">Sportify:</span> <span class="blue">Consultation sportive</span></h1>
        </div>
        <div class="logo">
            <!-- lien vers la page d accueil avec le logo -->
            <a href="../../html/accueil.html">
                <img src="../../images_accueil/Logo_sportify.png" alt="logo sportify" />
            </a>
        </div>
    </header>

    <!-- NAVIGATION principale avec boutons vers les sections -->
    <nav class="navigation">
        <!-- bouton vers l accueil -->
        <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
        <!-- bouton pour derouler le menu tout parcourir -->
        <button onclick="toggleParcourir()" aria-expanded="false" aria-controls="parcourirLinks">Tout Parcourir</button>
        <!-- bouton vers la page de recherche -->
        <button onclick="window.location.href='../../html/recherche/barre_recherche.html'">Recherche</button>
        <!-- bouton vers la page de consultation des rdv -->
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-vous</button>
        <!-- bouton vers la page de gestion du compte utilisateur -->
        <button onclick="window.location.href='../authentification/votre_compte.php'">Votre compte</button>
    </nav>

    <!-- LIENS DEROULES pour le menu 'tout parcourir' -->
    <div class="parcourir-dropdown" id="parcourirLinks" style="display: none;">
        <!-- lien vers la page activites sportives -->
        <a href="../tout parcourir/activites_sportives.php">Activités Sportives</a>
        <!-- lien vers la page sports de competition -->
        <a href="../tout parcourir/sports_competition.php">Les sports de compétition</a>
        <!-- lien vers la page salle de sport omnes -->
        <a href="../../html/salle_de_sport.html">Salle de sport omnes</a>
    </div>

    <!-- FORMULAIRE DE RECHERCHE rapide -->
    <div id="rechercheContainer" class="recherche-form">
        <form method="get" action="../../html/recherche/barre_recherche.html">
            <!-- champ de saisie pour la recherche -->
            <input type="text" name="q" placeholder="rechercher..." />
            <button type="submit">rechercher</button>
        </form>
    </div>

    <!-- SECTION PRINCIPALE : invitation teams -->
    <section class="main-section">
        <h2>inviter a une visioconference teams</h2>
        <div class="users-list">
            <?php foreach ($users as $user):
                // genere l url pour ouvrir teams avec l adresse email et le corps du message pre-rempli
                $teams_url = "https://teams.microsoft.com/l/meeting/new?subject=" . urlencode("réunion sportify") . "&attendees=" . urlencode($user['email']) . "&body=" . urlencode($body);
                ?>
                <div class="user-block">
                    <div>
                        <!-- affiche le prenom et nom de l utilisateur suivi de son role -->
                        <span class="user-info"><?= htmlspecialchars($user['prenom'] . " " . $user['nom']) ?>
                          <span class="role">(<?= $user['role'] ?>)</span>
                        </span>
                        <!-- affiche l adresse email de l utilisateur -->
                        <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="buttons">
                        <!-- bouton pour ouvrir teams dans un nouvel onglet -->
                        <a class="button" href="<?= $teams_url ?>" target="_blank">teams</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- bouton pour retourner a la page de gestion du compte -->
        <div class="retour-button">
            <button onclick="window.location.href='../authentification/votre_compte.php'">retour</button>
        </div>
    </section>

    <!-- FOOTER : contact et carte google maps -->
    <footer class="footer">
        <h3>Contactez-nous</h3>
        <!-- adresse email de contact -->
        <p>Email : <a href="mailto:contact@sportify.fr">contact@sportify.fr</a></p>
        <!-- numero de telephone de contact -->
        <p>Telephone : +33 1 23 45 67 89</p>
        <!-- adresse postale -->
        <p>Adresse : 10 rue sextius michel, 75015 paris, france</p>
        <div class="map">
            <!-- integration de la carte google maps pour afficher l adresse -->
            <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2625.3724386130675!2d2.2859626761368914!3d48.851108001219515!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e6701b486bb253%3A0x61e9cc6979f93fae!2s10%20Rue%20Sextius%20Michel%2C%2075015%20Paris!5e0!3m2!1sfr!2sfr!4v1748272681968!5m2!1sfr!2sfr"
                    width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy">
            </iframe>
        </div>
    </footer>

</div>

<!-- inclusion du script javascript commun pour le site -->
<script src="../../js/bases.js"></script>
</body>
</html>
