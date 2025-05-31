<?php
require_once '../connexion.php'; // Ajuste le chemin si besoin

// Liste des spécialités à afficher pour les sports de compétition
$specialites = [
    'Basketball',
    'Football',
    'Tennis',
    'Rugby',
    'Natation / Plongeon'
];

// Préparer la requête, on sélectionne aussi l'id du coach
$placeholders = rtrim(str_repeat('?,', count($specialites)), ',');
$sql = "SELECT coachs.id, users.nom, users.prenom, coachs.specialite
        FROM coachs 
        JOIN users ON coachs.id = users.id
        WHERE coachs.specialite IN ($placeholders)
        ORDER BY users.nom ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($specialites);
$coachs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <title>Sports de compétition</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/sports_competition.css" />
</head>
<body>
<div class="wrapper">

    <!-- HEADER -->
    <header class="header">
        <div class="title">
            <h1><span class="red">Sportify:</span> <span class="blue">Consultation Sportive</span></h1>
        </div>
        <div class="logo">
            <a href="../../html/accueil.html">
                <img src="../../images_accueil/Logo_sportify.png" alt="Logo Sportify" />
            </a>
        </div>
    </header>

    <!-- NAVIGATION -->
    <nav class="navigation">
        <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
        <button onclick="toggleParcourir()" aria-expanded="false" aria-controls="parcourirLinks">Tout Parcourir</button>
        <button onclick="toggleRecherche()">Recherche</button>
        <button onclick="window.location.href='../php/rdv/ConsulterRdv.php'">Rendez-Vous</button>
        <button onclick="window.location.href='../php/authentification/votre_compte.php'">Votre Compte</button>
    </nav>

    <!-- LIENS DÉROULÉS -->
    <div class="parcourir-dropdown" id="parcourirLinks">
        <a href="activites_sportives.php">Activités sportives</a>
        <a href="sports_competition.php">Les Sports de compétition</a>
        <a href="../../html/salle_de_sport.html">Salle de sport Omnes</a>
    </div>

    <!-- FORMULAIRE DE RECHERCHE -->
    <div id="rechercheContainer" class="recherche-form">
        <form>
            <input type="text" placeholder="Rechercher..." />
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- MAIN SECTION -->
    <section class="main-section">
        <h2>Les sports de compétition :</h2>
        <ul>
            <?php foreach ($coachs as $coach):
                $nomComplet = htmlspecialchars($coach['prenom'] . ' ' . $coach['nom']);
                $specialite = htmlspecialchars($coach['specialite']);
                $id = urlencode($coach['id']);
                ?>
                <li>
                    <a href="../coach/getCoach.php?id=<?= $id ?>">
                        <?= $nomComplet ?> (<?= $specialite ?>)
                    </a>
                </li>
            <?php endforeach; ?>
            <?php if (empty($coachs)): ?>
                <li>Aucun coach disponible pour ces sports de compétition.</li>
            <?php endif; ?>
        </ul>
    </section>

    <!-- FOOTER identique -->
    <footer class="footer">
        <h3>Contactez-nous</h3>
        <p>Email : <a href="mailto:contact@sportify.fr">contact@sportify.fr</a></p>
        <p>Téléphone : +33 1 23 45 67 89</p>
        <p>Adresse : 10 Rue Sextius Michel, 75015 Paris, France</p>
        <div class="map">
            <iframe
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2625.3724386130675!2d2.2859626761368914!3d48.851108001219515!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e6701b486bb253%3A0x61e9cc6979f93fae!2s10%20Rue%20Sextius%20Michel%2C%2075015%20Paris!5e0!3m2!1sfr!2sfr!4v1748272681968!5m2!1sfr!2sfr"
                width="100%" height="250" style="border:0;" allowfullscreen="" loading="lazy">
            </iframe>
        </div>
    </footer>

</div>

<script src="../../js/bases.js"></script>
</body>
</html>
