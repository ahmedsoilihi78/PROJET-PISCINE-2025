<?php
// inclut le fichier de connexion a la base de donnees
require_once '../connexion.php';

// tableau des specialites a afficher
$specialites = [
    'Musculation/Fitness',
    'Cardio-Training',
    'Biking',
    'Fitness',
    'Cours collectifs'
];

// construit la liste de points d interrogation pour la requete
$placeholders = rtrim(str_repeat('?,', count($specialites)), ',');

// prepare la requete pour recuperer l id et le nom des coachs selon specialite
$sql = "SELECT coachs.id, users.nom, users.prenom, coachs.specialite
        FROM coachs 
        JOIN users ON coachs.id = users.id
        WHERE coachs.specialite IN ($placeholders)
        ORDER BY users.nom ASC";

$stmt = $pdo->prepare($sql);
// execute la requete avec les specialites comme parametres
$stmt->execute($specialites);
// recupere tous les coachs correspondants sous forme de tableau
$coachs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>activites sportives</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <!-- inclusion des styles de base et spécifiques -->
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

    <!-- liens deroules pour tout parcourir -->
    <div class="parcourir-dropdown" id="parcourirLinks">
        <a href="activites_sportives.php">activites sportives</a>
        <a href="sports_competition.php">les sports de competition</a>
        <a href="../../html/salle_de_sport.html">salle de sport omnes</a>
    </div>

    <!-- formulaire de recherche rapide -->
    <div id="rechercheContainer" class="recherche-form">
        <form method="get" action="../../html/recherche/barre_recherche.html">
            <input type="text" name="q" placeholder="rechercher..." />
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- section principale affichant la liste des coachs selon specialite -->
    <section class="main-section">
        <h2>Les activités sportives :</h2>
        <ul>
            <?php foreach ($coachs as $coach):
                // prepare le nom complet et la specialite pour affichage
                $nomComplet = htmlspecialchars($coach['prenom'] . ' ' . $coach['nom']);
                $specialite = htmlspecialchars($coach['specialite']);
                $id = urlencode($coach['id']);
                ?>
                <li>
                    <!-- lien vers la page de profil du coach avec id en parametre -->
                    <a href="../coach/getCoach.php?id=<?= $id ?>">
                        <?= $nomComplet ?> (<?= $specialite ?>)
                    </a>
                </li>
            <?php endforeach; ?>
            <?php if (empty($coachs)): ?>
                <!-- message si aucun coach trouve pour ces specialites -->
                <li>aucun coach disponible pour ces activites.</li>
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
            <!-- integration de la carte google maps pour afficher l adresse -->
            <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2625.3724386130675!2d2.2859626761368914!3d48.851108001219515!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e6701b486bb253%3A0x61e9cc6979f93fae!2s10%20Rue%20Sextius%20Michel%2C%2075015%20Paris!5e0!3m2!1sfr!2sfr!4v1748272681968!5m2!1sfr!2sfr"
                    width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy">
            </iframe>
        </div>
    </footer>

</div>

<!-- inclusion du script javascript commun -->
<script src="../../js/bases.js"></script>
</body>
</html>
