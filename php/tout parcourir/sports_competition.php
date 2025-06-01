<?php
// inclut le fichier de connexion a la base de donnees
require_once '../connexion.php';

// tableau des specialites pour les sports de competition
$specialites = [
    'Basketball',
    'Football',
    'Tennis',
    'Rugby',
    'Natation / Plongeon'
];

// on construit une liste de points d interrogation pour la requete preparee
$placeholders = rtrim(str_repeat('?,', count($specialites)), ',');

// on prepare la requete pour recuperer l id et les infos des coachs dont la specialite est dans la liste
$sql = "select coachs.id, users.nom, users.prenom, coachs.specialite
        from coachs 
        join users on coachs.id = users.id
        where coachs.specialite in ($placeholders)
        order by users.nom asc";

$stmt = $pdo->prepare($sql);
// on execute la requete en passant les specialites comme parametres
$stmt->execute($specialites);
// on stocke le resultat dans un tableau associatif
$coachs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>sports de competition</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <!-- inclusion des styles de base et specifiques -->
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/sports_competition.css" />
</head>
<body>
<div class="wrapper">

    <!-- header avec le titre et le logo -->
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

    <!-- barre de navigation principale -->
    <nav class="navigation">
        <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
        <button onclick="toggleParcourir()" aria-expanded="false" aria-controls="parcourirLinks">Tout Parcourir</button>
        <button onclick="window.location.href='../../html/recherche/barre_recherche.html'">Recherche</button>
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-vous</button>
        <button onclick="window.location.href='../authentification/votre_compte.php'">Votre compte</button>
    </nav>

    <!-- menu deroule pour les sous sections -->
    <div class="parcourir-dropdown" id="parcourirLinks">
        <a href="activites_sportives.php">Activités sportives</a>
        <a href="sports_competition.php">Les sports de compétition</a>
        <a href="../../html/salle_de_sport.html">Salle de sport omnes</a>
    </div>

    <!-- formulaire de recherche rapide -->
    <div id="rechercheContainer" class="recherche-form">
        <form method="get" action="../../html/recherche/barre_recherche.html">
            <input type="text" name="q" placeholder="rechercher..." />
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- section principale affichant la liste des coachs de sports de compétition -->
    <section class="main-section">
        <h2>Les sports de competition :</h2>
        <ul>
            <?php foreach ($coachs as $coach):
                // on prepare le nom complet et la specialite du coach pour affichage
                $nomComplet = htmlspecialchars($coach['prenom'] . ' ' . $coach['nom']);
                $specialite = htmlspecialchars($coach['specialite']);
                // on code l id pour l inserer dans l url
                $id = urlencode($coach['id']);
                ?>
                <li>
                    <!-- chaque coach renvoie vers la page de profil getCoach avec son id -->
                    <a href="../coach/getCoach.php?id=<?= $id ?>">
                        <?= $nomComplet ?> (<?= $specialite ?>)
                    </a>
                </li>
            <?php endforeach; ?>
            <?php if (empty($coachs)): ?>
                <!-- message si aucun coach trouve pour ces specialites -->
                <li>Aucun coach disponible pour ces sports de competition.</li>
            <?php endif; ?>
        </ul>
    </section>

    <!-- footer avec contact et carte google maps -->
    <footer class="footer">
        <h3>Contactez-nous</h3>
        <p>Email : <a href="mailto:contact@sportify.fr">contact@sportify.fr</a></p>
        <p>Telephone : +33 1 23 45 67 89</p>
        <p>Adresse : 10 rue sextius michel, 75015 paris, france</p>
        <div class="map">
            <!-- integration de la carte google maps pour l adresse -->
            <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2625.3724386130675!2d2.2859626761368914!3d48.851108001219515!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e6701b486bb253%3A0x61e9cc6979f93fae!2s10%20Rue%20Sextius%20Michel%2C%2075015%20Paris!5e0!3m2!1sfr!2sfr!4v1748272681968!5m2!1sfr!2sfr"
                    width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy">
            </iframe>
        </div>
    </footer>

</div>

<!-- inclusion du script js de base -->
<script src="../../js/bases.js"></script>
</body>
</html>
