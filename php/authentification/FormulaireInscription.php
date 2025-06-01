<?php
// demarre la session pour stocker et recuperer les messages
session_start();
// recupere et initialise les variables d erreur et de succes depuis la session
$error   = $_SESSION['error']   ?? '';
$success = $_SESSION['success'] ?? '';
// supprime les valeurs d erreur et de succes de la session apres lecture
unset($_SESSION['error'], $_SESSION['success']);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <!-- titre de la page -->
    <title>Sportify - Inscription</title>
    <!-- inclusion des styles de base et de la page d inscription -->
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/inscription.css" />
</head>
<body>
<div class="wrapper">

    <!-- HEADER -->
    <header class="header">
        <div class="title">
            <!-- titre principal avec differenciation de styles par classes -->
            <h1><span class="red">Sportify:</span> <span class="blue">consultation sportive</span></h1>
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
        <!-- boutons de navigation vers les differents modules -->
        <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
        <button onclick="toggleParcourir()" aria-expanded="false" aria-controls="parcourirLinks">Tout Parcourir</button>
        <button onclick="window.location.href='../../html/recherche/barre_recherche.html'">Recherche</button>
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-vous</button>
        <button onclick="window.location.href='votre_compte.php'">Votre compte</button>
    </nav>

    <!-- LIENS DEROULES POUR LE MENU 'TOUT PARCOURIR' -->
    <div class="parcourir-dropdown" id="parcourirLinks" style="display: none;">
        <!-- liens vers les pages de consultation des activites sportives -->
        <a href="../tout parcourir/activites_sportives.php">Activités Sportives</a>
        <a href="../tout parcourir/sports_competition.php">Les sports de compétition</a>
        <a href="../../html/salle_de_sport.html">Salle de sport omnes</a>
    </div>

    <!-- FORMULAIRE DE RECHERCHE RAPIDE -->
    <div id="rechercheContainer" class="recherche-form">
        <form method="get" action="../../html/recherche/barre_recherche.html">
            <!-- champ de recherche texte -->
            <input type="text" name="q" placeholder="rechercher..." />
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- SECTION PRINCIPALE : FORMULAIRE D INSCRIPTION -->
    <section class="main-section">
        <h2>inscription</h2>
        <div class="container">

            <!-- affichage des messages d erreur ou de succes -->
            <?php if ($error): ?>
                <div class="message error"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div class="message success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <!-- formulaire d inscription en methode POST vers le meme fichier -->
            <form action="inscription.php" method="POST" class="signup-form">
                <!-- champ pour nom (obligatoire) -->
                <label for="nom">nom *</label>
                <input type="text" name="nom" id="nom" required>

                <!-- champ pour prenom (obligatoire) -->
                <label for="prenom">prenom *</label>
                <input type="text" name="prenom" id="prenom" required>

                <!-- champ pour adresse email (obligatoire) -->
                <label for="email">adresse e-mail *</label>
                <input type="email" name="email" id="email" required>

                <!-- champ pour mot de passe (obligatoire) -->
                <label for="mot_de_passe">mot de passe *</label>
                <input type="password" name="mot_de_passe" id="mot_de_passe" required>

                <!-- champ pour confirmation de mot de passe (obligatoire) -->
                <label for="confirm_password">confirmer le mot de passe *</label>
                <input type="password" name="confirm_password" id="confirm_password" required>

                <!-- champ pour adresse (facultatif) -->
                <label for="adresse">adresse</label>
                <textarea name="adresse" id="adresse" rows="3"></textarea>

                <!-- champ pour telephone (facultatif) -->
                <label for="telephone">telephone</label>
                <input type="text" name="telephone" id="telephone">

                <!-- champ pour carte etudiant (facultatif) -->
                <label for="carte_etudiant">carte etudiant</label>
                <input type="text" name="carte_etudiant" id="carte_etudiant">

                <!-- bouton pour envoyer le formulaire -->
                <div class="submit-btn">
                    <input type="submit" value="creer mon compte" class="btn-inscription">
                </div>
            </form>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <h3>Contactez-nous</h3>
        <!-- informations de contact -->
        <p>Email : <a href="mailto:contact@sportify.fr">contact@sportify.fr</a></p>
        <p>Telephone : +33 1 23 45 67 89</p>
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

<!-- inclusion du script javascript de base -->
<script src="../../js/bases.js"></script>
</body>
</html>
