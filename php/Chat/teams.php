<?php
session_start();
require_once __DIR__ . '/../connexion.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /../connexion.php");
    exit();
}
$current_user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$stmt->execute([$current_user_id]);
$current_user = $stmt->fetch();
$current_role = $current_user ? $current_user['role'] : '';

if ($current_role === 'client') {
    $stmt = $pdo->prepare("SELECT id, nom, prenom, email, role FROM users WHERE id != ? AND role != 'client'");
    $stmt->execute([$current_user_id]);
} else {
    $stmt = $pdo->prepare("SELECT id, nom, prenom, email, role FROM users WHERE id != ?");
    $stmt->execute([$current_user_id]);
}
$users = $stmt->fetchAll();

$body = "Bonjour, je vous propose une visioconférence via Teams.\n\n";
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Visioconférence Teams - Sportify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/mail.css" />
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
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-Vous</button>
        <button onclick="window.location.href='../authentification/votre_compte.php'">Votre Compte</button>
    </nav>

    <!-- LIENS DÉROULÉS -->
    <div class="parcourir-dropdown" id="parcourirLinks" style="display: none;">
        <a href="../../html/activites_sportives.html">Activités sportives</a>
        <a href="../../html/sports_competition.html">Les Sports de compétition</a>
        <a href="../../html/salle_de_sport.html">Salle de sport Omnes</a>
    </div>

    <!-- FORMULAIRE DE RECHERCHE -->
    <div id="rechercheContainer" class="recherche-form" style="display: none;">
        <form>
            <input type="text" placeholder="Rechercher..." />
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- MAIN -->
    <section class="main-section">
        <h2>Inviter à une visioconférence Teams</h2>
        <div class="users-list">
            <?php foreach ($users as $user):
                // Pré-remplir l'email dans la fenêtre Teams via le lien d'invitation à une réunion
                // On ne peut pas lancer un vrai meeting direct, mais on ouvre la page d'invitation avec email du user à copier/coller.
                $teams_url = "https://teams.microsoft.com/l/meeting/new?subject=" . urlencode("Réunion Sportify") . "&attendees=" . urlencode($user['email']) . "&body=" . urlencode($body);
                ?>
                <div class="user-block">
                    <div>
            <span class="user-info"><?= htmlspecialchars($user['prenom'] . " " . $user['nom']) ?>
              <span class="role">(<?= $user['role'] ?>)</span>
            </span>
                        <span class="user-email"><?= htmlspecialchars($user['email']) ?></span>
                    </div>
                    <div class="buttons">
                        <a class="button" href="<?= $teams_url ?>" target="_blank">Teams</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="retour-button">
            <button onclick="window.location.href='../authentification/votre_compte.php'">Retour</button>
        </div>
    </section>

    <!-- FOOTER -->
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

