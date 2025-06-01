<?php
// demarre la session pour utiliser les variables de session
session_start();
// inclut le fichier de connexion a la base de donnees pour utiliser pdo
require_once __DIR__ . '/../connexion.php';

// verifie que l id du coach est present dans l url et qu il est numerique sinon affiche un message et quitte
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "aucun coach specifie.";
    exit;
}
// recupere l id du coach depuis l url
$id = $_GET['id'];

// prepare une requete pour recuperer les chemins du fichier xml, du pdf et de la photo depuis la table coachs
$stmt = $pdo->prepare("SELECT cv_xml, cv_pdf, photo FROM coachs WHERE id = ?");
$stmt->execute([$id]);
// recupere le resultat sous forme de tableau associatif
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// si aucun resultat ou chemin xml vide, affiche un message d erreur et quitte
if (!$result || empty($result['cv_xml'])) {
    echo "<h2>erreur : coach ou fichier xml introuvable.</h2>";
    exit;
}
// recupere les chemins depuis le resultat
$xmlPath = $result['cv_xml'];
$cv_pdf  = $result['cv_pdf'] ?? '';
$photo   = $result['photo'] ?? '';

// verifie que le fichier xml existe physiquement sinon affiche un message d erreur et quitte
if (!file_exists($xmlPath)) {
    echo "<h2>erreur : fichier xml introuvable a l emplacement : $xmlPath</h2>";
    exit;
}

// tente de charger le fichier xml sinon affiche un message d erreur et quitte
$xml = @simplexml_load_file($xmlPath);
if (!$xml) {
    echo "<h2>erreur : impossible de charger le fichier xml.</h2>";
    exit;
}

// recupere les informations du xml (prenom, nom, email, video)
$prenom = $xml->prenom ?? '';
$nom    = $xml->nom ?? '';
$email  = $xml->email ?? '';
$video  = $xml->video ?? ''; // si le lien video est stocke dans le xml

// recupere les listes de specialites, formations, experiences et disponibilites depuis le xml
$specialites = $xml->specialites->specialite ?? [];
$formations  = $xml->formations->formation ?? [];
$experiences = $xml->experiences->experience ?? [];
$dispos      = $xml->disponibilite->jour ?? [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <!-- titre dynamique avec prenom et nom du coach -->
    <title>Profil du coach <?= htmlspecialchars("$prenom $nom") ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- inclusion des styles de base et de la page getcoach -->
    <link rel="stylesheet" href="../../css/bases.css">
    <link rel="stylesheet" href="../../css/getcoach.css">
</head>
<body>
<div class="wrapper">

    <!-- header avec titre et logo -->
    <header class="header">
        <div class="title">
            <h1><span class="red">Sportify:</span> <span class="blue">Consultation sportive</span></h1>
        </div>
        <div class="logo">
            <!-- lien vers la page d accueil avec le logo -->
            <a href="../../html/accueil.html">
                <img src="../../images_accueil/Logo_sportify.png" alt="logo sportify" />
            </a>
        </div>
    </header>

    <!-- navigation principale avec boutons de redirection -->
    <nav class="navigation">
        <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
        <button onclick="toggleParcourir()" aria-expanded="false" aria-controls="parcourirLinks">Tout Parcourir</button>
        <button onclick="window.location.href='../../html/recherche/barre_recherche.html'">Recherche</button>
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-vous</button>
        <button onclick="window.location.href='../authentification/votre_compte.php'">Votre compte</button>
    </nav>

    <!-- liens deroules pour le menu tout parcourir -->
    <div class="parcourir-dropdown" id="parcourirLinks" style="display: none;">
        <a href="../tout parcourir/activites_sportives.php">Activités sportives</a>
        <a href="../tout parcourir/sports_competition.php">Les sports de compétition</a>
        <a href="../../html/salle_de_sport.html">Salle de sport omnes</a>
    </div>

    <!-- formulaire de recherche rapide -->
    <div id="rechercheContainer" class="recherche-form">
        <form method="get" action="../../html/recherche/barre_recherche.html">
            <input type="text" name="q" placeholder="rechercher..." />
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- section principale affichant le profil du coach -->
    <section class="main-section coach-container">
        <!-- affiche le prenom et nom du coach -->
        <h2><?= htmlspecialchars("$prenom $nom") ?></h2>
        <?php if ($photo && file_exists($photo)): ?>
            <!-- affiche la photo du coach si le fichier existe -->
            <img src="<?= htmlspecialchars($photo) ?>" alt="photo de <?= htmlspecialchars($prenom) ?>" class="coach-photo">
        <?php else: ?>
            <!-- texte si aucune photo disponible -->
            <p>pas de photo disponible.</p>
        <?php endif; ?>
        <!-- affiche l email du coach -->
        <p><strong>email :</strong> <?= htmlspecialchars($email) ?></p>

        <!-- liste des specialites -->
        <h3>specialites</h3>
        <?php foreach ($specialites as $s): ?>
            <p><?= htmlspecialchars($s) ?></p>
        <?php endforeach; ?>

        <!-- section video de presentation -->
        <h3>video de presentation</h3>
        <?php if (!empty($video)) : ?>
            <!-- intègre la video dans un iframe si le lien est present -->
            <div class="video-wrapper">
                <iframe width="560" height="315" src="<?= htmlspecialchars($video) ?>" frameborder="0" allowfullscreen></iframe>
            </div>
        <?php else : ?>
            <!-- texte si aucune video disponible -->
            <p>pas de video disponible.</p>
        <?php endif; ?>

        <!-- liste des formations -->
        <h3>formations</h3>
        <?php foreach ($formations as $f): ?>
            <!-- affiche titre, ecole et annee de chaque formation -->
            <p><?= htmlspecialchars($f->titre) ?> a <?= htmlspecialchars($f->ecole) ?> en <?= htmlspecialchars($f->annee) ?></p>
        <?php endforeach; ?>

        <!-- liste des experiences professionnelles -->
        <h3>experiences</h3>
        <?php foreach ($experiences as $exp): ?>
            <!-- affiche poste, lieu et duree de chaque experience -->
            <p><?= htmlspecialchars($exp->poste) ?> a <?= htmlspecialchars($exp->lieu) ?> (<?= htmlspecialchars($exp->duree) ?>)</p>
        <?php endforeach; ?>

        <!-- tableau des disponibilites du coach -->
        <h3>disponibilites</h3>
        <table>
            <tr><th>jour</th><th>matin</th><th>apres-midi</th></tr>
            <?php foreach ($dispos as $jour): ?>
                <tr>
                    <!-- affiche le nom du jour et coche selon la disponibilite -->
                    <td><?= htmlspecialchars($jour['nom']) ?></td>
                    <td><?= ($jour->matin == "true") ? "✔" : "✖" ?></td>
                    <td><?= ($jour->apresmidi == "true") ? "✔" : "✖" ?></td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- zone liens pour telecharger le cv pdf et bouton retour -->
        <div style="text-align: center; margin-top: 30px;">
            <?php if ($cv_pdf && file_exists($cv_pdf)): ?>
                <!-- lien pour telecharger le cv pdf du coach si present -->
                <p><a href="<?= htmlspecialchars($cv_pdf) ?>" target="_blank">telecharger le cv (pdf)</a></p>
            <?php endif; ?>
            <!-- bouton pour revenir a la page precedente -->
            <button class="nav-btn" onclick="window.history.back()">Retour</button>
        </div>
    </section>

    <!-- footer avec contact et carte google maps -->
    <footer class="footer">
        <h3>Contactez-nous</h3>
        <p>Email : <a href="mailto:contact@sportify.fr">contact@sportify.fr</a></p>
        <p>Telephone : +33 1 23 45 67 89</p>
        <p>Adresse : 10 rue sextius michel, 75015 paris, france</p>
        <div class="map">
            <!-- intègre la carte google maps pour l adresse du site -->
            <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2625.3724386130675!2d2.2859626761368914!3d48.851108001219515!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x47e6701b486bb253%3A0x61e9cc6979f93fae!2s10%20Rue%20Sextius%20Michel%2C%2075015%20Paris!5e0!3m2!1sfr!2sfr!4v1748272681968!5m2!1sfr!2sfr"
                    width="100%"
                    height="250"
                    style="border:0;"
                    allowfullscreen
                    loading="lazy">
            </iframe>
        </div>
    </footer>

</div>

<!-- inclusion du script javascript commun pour le site -->
<script src="../../js/bases.js"></script>
</body>
</html>
