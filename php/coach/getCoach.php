<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['file'])) {
    echo "Aucun coach spécifié.";
    exit;
}

$filename = basename($_GET['file']);
$xmlPath = "../../xml/coachs/$filename";

if (!file_exists($xmlPath)) {
    echo "<h2>Erreur : fichier XML introuvable à l'emplacement : $xmlPath</h2>";
    exit;
}

$xml = @simplexml_load_file($xmlPath);
if (!$xml) {
    echo "<h2>Erreur : impossible de charger le fichier XML.</h2>";
    exit;
}

$prenom = $xml->prenom;
$nom = $xml->nom;
$email = $xml->email;
$photo = $xml->photo;
$video = $xml->video;
$cv_pdf = $xml->cv_pdf ?? "../../uploads/cvs/" . strtolower($prenom) . "_" . strtolower($nom) . ".pdf";

$specialites = $xml->specialites->specialite ?? [];
$formations = $xml->formations->formation ?? [];
$experiences = $xml->experiences->experience ?? [];
$dispos = $xml->disponibilite->jour ?? [];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Profil du coach <?= "$prenom $nom" ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="../../css/bases.css">
  <link rel="stylesheet" href="../../css/getcoach.css">
</head>
<body>
  <div class="wrapper">

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

    <nav class="navigation">
      <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
      <button onclick="toggleParcourir()" aria-expanded="false" aria-controls="parcourirLinks">Tout Parcourir</button>
      <button onclick="toggleRecherche()">Recherche</button>
      <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-vous</button>
      <button onclick="window.location.href='../authentification/votre_compte.php'">Votre Compte</button>
    </nav>

    <div class="parcourir-dropdown" id="parcourirLinks" style="display: none;">
      <a href="../../html/activites_sportives.html">Activités sportives</a>
      <a href="../../html/sports_competition.html">Les Sports de compétition</a>
      <a href="../../html/salle_de_sport.html">Salle de sport Omnes</a>
    </div>

    <div id="rechercheContainer" class="recherche-form">
      <form>
        <input type="text" placeholder="Rechercher..." />
        <button type="submit">Rechercher</button>
      </form>
    </div>

    <section class="main-section coach-container">
      <h2><?= "$prenom $nom" ?></h2>
      <img src="../../<?= $photo ?>" alt="Photo de <?= $prenom ?>" class="coach-photo">
      <p><strong>Email :</strong> <?= $email ?></p>

     <h3>Spécialités</h3>
<?php foreach ($specialites as $s): ?>
  <p><?= $s ?></p>
<?php endforeach; ?>

 <h3>Vidéo de présentation</h3>
      <?php if (!empty($video)) : ?>
        <div class="video-wrapper">
          <iframe width="560" height="315" src="<?= $video ?>" frameborder="0" allowfullscreen></iframe>
        </div>
      <?php else : ?>
        <p>Pas de vidéo disponible.</p>
      <?php endif; ?>

<h3>Formations</h3>
<?php foreach ($formations as $f): ?>
  <p><?= "$f->titre à $f->ecole en $f->annee" ?></p>
<?php endforeach; ?>

<h3>Expériences</h3>
<?php foreach ($experiences as $exp): ?>
  <p><?= "$exp->poste à $exp->lieu ($exp->duree)" ?></p>
<?php endforeach; ?>

      <h3>Disponibilités</h3>
      <table>
        <tr><th>Jour</th><th>Matin</th><th>Après-midi</th></tr>
        <?php foreach ($dispos as $jour): ?>
          <tr>
            <td><?= $jour['nom'] ?></td>
            <td><?= ($jour->matin == "true") ? "✔" : "✖" ?></td>
            <td><?= ($jour->apresmidi == "true") ? "✔" : "✖" ?></td>
          </tr>
        <?php endforeach; ?>
      </table>

      <div style="text-align: center; margin-top: 30px;">
        <p><a href="../../<?= $cv_pdf ?>" target="_blank">Télécharger le CV (PDF)</a></p>
        <button class="nav-btn" onclick="location.href='../../html/sports_competition.html'">← Retour à la liste des coachs</button>
      </div>
    </section>

    <footer class="footer">
      <h3>Contactez-nous</h3>
      <p>Email : <a href="mailto:contact@sportify.fr">contact@sportify.fr</a></p>
      <p>Téléphone : +33 1 23 45 67 89</p>
      <p>Adresse : 10 Rue Sextius Michel, 75015 Paris, France</p>
      <div class="map">
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

  <script src="../../js/bases.js"></script>
</body>
</html>
