<?php
// demarre la session pour utiliser les variables de session
session_start();
// inclut le fichier de connexion a la base de donnees
require_once __DIR__ . '/../connexion.php';

// si l utilisateur n est pas connecte, redirige vers la page de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// recupere l id de l utilisateur courant depuis la session
$current_user_id = $_SESSION['user_id'];

// prepare et execute une requete pour recuperer tous les autres utilisateurs sauf soi-meme
$stmt = $pdo->prepare("SELECT id, nom, prenom, role FROM users WHERE id != ?");
$stmt->execute([$current_user_id]);
// recuperation des resultats sous forme de tableau
$users = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <!-- titre de l onglet -->
    <title>choisir un destinataire - sportify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <!-- inclusion des styles de base et de la page de selection de chat -->
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/chat_select.css" />
</head>
<body>
<div class="wrapper">

    <!-- HEADER -->
    <header class="header">
        <div class="title">
            <!-- titre principal avec style par classes css -->
            <h1><span class="red">Sportify:</span> <span class="blue">Consultation sportive</span></h1>
        </div>
        <div class="logo">
            <!-- lien vers la page d accueil avec logo -->
            <a href="../../html/accueil.html">
                <img src="../../images_accueil/Logo_sportify.png" alt="logo sportify" />
            </a>
        </div>
    </header>

    <!-- NAVIGATION -->
    <nav class="navigation">
        <!-- bouton pour aller a l accueil -->
        <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
        <!-- bouton pour afficher le menu tout parcourir -->
        <button onclick="toggleParcourir()" aria-expanded="false" aria-controls="parcourirLinks">Tout Parcourir</button>
        <!-- bouton pour la page de recherche -->
        <button onclick="window.location.href='../../html/recherche/barre_recherche.html'">Recherche</button>
        <!-- bouton pour consulter les rendez-vous -->
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-vous</button>
        <!-- bouton pour acceder a son compte -->
        <button onclick="window.location.href='../authentification/votre_compte.php'">Votre compte</button>
    </nav>

    <!-- LIENS DEROULES POUR LE MENU 'TOUT PARCOURIR' -->
    <div class="parcourir-dropdown" id="parcourirLinks" style="display: none;">
        <!-- lien vers la page des activites sportives -->
        <a href="../tout parcourir/activites_sportives.php">Activités Sportives</a>
        <!-- lien vers la page des sports de competition -->
        <a href="../tout parcourir/sports_competition.php">Les sports de compétition</a>
        <!-- lien vers la page de la salle de sport omnes -->
        <a href="../../html/salle_de_sport.html">Salle de sport omnes</a>
    </div>

    <!-- FORMULAIRE DE RECHERCHE DANS LE MENU -->
    <div id="rechercheContainer" class="recherche-form">
        <form method="get" action="../../html/recherche/barre_recherche.html">
            <!-- champ texte pour la recherche -->
            <input type="text" name="q" placeholder="rechercher..." />
            <button type="submit">rechercher</button>
        </form>
    </div>

    <!-- SECTION PRINCIPALE -->
    <section class="main-section">
        <!-- titre de la section -->
        <h2>choisissez un destinataire pour chatter</h2>
        <div class="users-list">
            <?php foreach ($users as $u): ?>
                <!-- lien vers la page de chat avec l utilisateur selectionne -->
                <a class="user-link" href="chat.php?user=<?= $u['id'] ?>">
                    <?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?>
                    <!-- affichage du role de l utilisateur entre parenthèses -->
                    <span class="role">(<?= $u['role'] ?>)</span>
                </a>
            <?php endforeach; ?>
        </div>
        <div class="retour-button">
            <!-- bouton pour retourner a la page de compte -->
            <button onclick="window.location.href='../authentification/votre_compte.php'">Retour</button>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <h3>Contactez-nous</h3>
        <!-- informations de contact -->
        <p>Email : <a href="mailto:contact@sportify.fr">contact@sportify.fr</a></p>
        <p>Telephone : +33 1 23 45 67 89</p>
        <p>Adresse : 10 rue sextius michel, 75015 paris, france</p>
        <div class="map">
            <!-- integration google maps pour afficher l adresse -->
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
