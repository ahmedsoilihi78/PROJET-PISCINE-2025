<?php
// inclut le fichier de connexion a la base de donnees
require_once '../connexion.php';

// on recupere tous les coachs sans filtrage
$sql = "select coachs.id, users.nom, users.prenom, coachs.specialite
        from coachs
        join users on coachs.id = users.id
        order by users.nom asc";

$stmt = $pdo->query($sql);
// on stocke le resultat dans un tableau associatif
$coachs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>nos coachs - sportify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <!-- inclusion des styles de base et de la page sports_competition -->
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/sports_competition.css" />
</head>
<body>
<div class="wrapper">

    <!-- header avec titre et logo -->
    <header class="header">
        <div class="title">
            <h1><span class="red">Sportify:</span> <span class="blue">Consultation sportive</span></h1>
        </div>
        <div class="logo">
            <!-- lien vers la page d accueil -->
            <a href="../../html/accueil.html">
                <img src="../../images_accueil/Logo_sportify.png" alt="logo sportify" />
            </a>
        </div>
    </header>

    <!-- navigation principale -->
    <nav class="navigation">
        <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
        <button onclick="toggleParcourir()" aria-expanded="false" aria-controls="parcourirLinks">Tout Parcourir</button>
        <button onclick="window.location.href='../../html/recherche/barre_recherche.html'">Recherche</button>
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-vous</button>
        <button onclick="window.location.href='../authentification/votre_compte.php'">Votre compte</button>
    </nav>

    <!-- liens deroules pour le menu tout parcourir -->
    <div class="parcourir-dropdown" id="parcourirLinks">
        <a href="activites_sportives.php">Activités Sportives</a>
        <a href="sports_competition.php">Les sports de compeétition</a>
        <a href="../../html/salle_de_sport.html">Salle de sport omnes</a>
    </div>

    <!-- formulaire de recherche rapide -->
    <div id="rechercheContainer" class="recherche-form">
        <form method="get" action="../../html/recherche/barre_recherche.html">
            <input type="text" name="q" placeholder="rechercher..." />
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- section principale affichant la liste des coachs -->
    <section class="main-section">
        <h2>nos coachs :</h2>
        <ul>
            <?php foreach ($coachs as $coach):
                // preparation du nom complet et specialite pour affichage
                $nomComplet = htmlspecialchars($coach['prenom'] . ' ' . $coach['nom']);
                $specialite = htmlspecialchars($coach['specialite']);
                // on code l id pour l inserer dans l url de la page getCoach
                $id = urlencode($coach['id']);
                ?>
                <li>
                    <!-- lien vers le profil du coach avec id en parametre -->
                    <a href="../../php/coach/getCoach.php?id=<?= $id ?>">
                        <?= $nomComplet ?> (<?= $specialite ?>)
                    </a>
                </li>
            <?php endforeach; ?>
            <?php if (empty($coachs)): ?>
                <!-- message si aucun coach n est enregistre -->
                <li>Aucun coach enregistré.</li>
            <?php endif; ?>
        </ul>
    </section>

    <!-- footer contenant les informations de contact et la carte google maps -->
    <footer class="footer">
        <h3>Contactez-nous</h3>
        <p>Email : <a href="mailto:contact@sportify.fr">contact@sportify.fr</a></p>
        <p>Telephone : +33 1 23 45 67 89</p>
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

<!-- inclusion du script javascript de base -->
<script src="../../js/bases.js"></script>
</body>
</html>
