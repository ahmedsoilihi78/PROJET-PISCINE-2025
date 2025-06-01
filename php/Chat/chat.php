<?php
// demarre la session pour utiliser les variables de session
session_start();
// inclut le fichier de connexion a la base de donnees pour acceder a $pdo
require_once __DIR__ . '/../connexion.php';

// verifie si l utilisateur n est pas connecte, redirige vers la page de connexion
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// recupere l id de l utilisateur courant depuis la session
$current_user_id = $_SESSION['user_id'];

// verifie si l id de l autre utilisateur a discuter n est pas fourni dans l url
if (!isset($_GET['user'])) {
    // si l id du destinataire n est pas present, redirige vers la selection de chat
    header("Location: chat_select.php");
    exit();
}
// convertit la valeur recuperee en entier pour securite
$other_user_id = intval($_GET['user']);

// prépare une requete pour verifier que le destinataire existe et n est pas l utilisateur courant
$stmt = $pdo->prepare("SELECT nom, prenom FROM users WHERE id = ? AND id != ?");
$stmt->execute([$other_user_id, $current_user_id]);
// recupere le nom et prenom du destinataire si trouve
$dest = $stmt->fetch();
// si aucun destinataire trouve, arrete le script et affiche un message
if (!$dest) {
    die("destinataire introuvable ou non autorise.");
}

// si le formulaire est soumis en methode POST et que le contenu n est pas vide, on enregistre le message
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['contenu'])) {
    // nettoie le contenu du message pour eviter les injections
    $contenu = htmlspecialchars($_POST['contenu']);
    // recupere la date et l heure actuelles au format yyyy-mm-dd hh:mm:ss
    $date_envoi = date('Y-m-d H:i:s');
    // definit le type de message a texte
    $type = 'texte';
    // prepare la requete pour inserer le message dans la table messages
    $sql = "INSERT INTO messages (sender_id, receiver_id, type, contenu, date_envoi) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    // execute la requete en passant les parametres necessaires
    $stmt->execute([$current_user_id, $other_user_id, $type, $contenu, $date_envoi]);
    // apres insertion, redirige vers la meme page pour actualiser la liste des messages
    header("Location: chat.php?user=$other_user_id");
    exit();
}

// prépare la requete pour recuperer tous les messages entre l utilisateur courant et le destinataire
$sql = "SELECT m.*, u.nom, u.prenom 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (sender_id = ? AND receiver_id = ?)
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY date_envoi ASC";
$stmt = $pdo->prepare($sql);
// execute la requete en passant l id de l utilisateur courant et du destinataire pour les deux conditions
$stmt->execute([$current_user_id, $other_user_id, $other_user_id, $current_user_id]);
// recupere tous les messages trouves sous forme de tableau
$messages = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <!-- titre de la page dynamique avec le nom et prenom du destinataire -->
    <title>chat avec <?= htmlspecialchars($dest['prenom'] . ' ' . $dest['nom']) ?> - sportify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <!-- inclusion des styles de base et du style du chat -->
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/chat.css" />
</head>
<body>
<div class="wrapper">

    <!-- HEADER -->
    <header class="header">
        <div class="title">
            <!-- titre principal avec mise en forme par classes css -->
            <h1><span class="red">Sportify:</span> <span class="blue">Consultation sportive</span></h1>
        </div>
        <div class="logo">
            <!-- lien vers la page d accueil avec le logo -->
            <a href="../../html/accueil.html">
                <img src="../../images_accueil/Logo_sportify.png" alt="logo sportify" />
            </a>
        </div>
    </header>

    <!-- NAVIGATION -->
    <nav class="navigation">
        <!-- bouton pour aller a l accueil -->
        <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
        <!-- bouton pour derouler le menu tout parcourir -->
        <button onclick="toggleParcourir()" aria-expanded="false" aria-controls="parcourirLinks">Tout Parcourir</button>
        <!-- bouton redirigeant vers la page de recherche -->
        <button onclick="window.location.href='../../html/recherche/barre_recherche.html'">Recherche</button>
        <!-- bouton redirigeant vers la page de consultation des rdv -->
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-vous</button>
        <!-- bouton vers la page de connexion/compte -->
        <button onclick="window.location.href='../authentification/votre_compte.php'">Votre compte</button>
    </nav>

    <!-- LIENS DÉROULÉS POUR LE MENU 'TOUT PARCOURIR' -->
    <div class="parcourir-dropdown" id="parcourirLinks" style="display: none;">
        <!-- lien vers la page activites sportives -->
        <a href="../tout parcourir/activites_sportives.php">Activités Sportives</a>
        <!-- lien vers la page sports de competition -->
        <a href="../tout parcourir/sports_competition.php">Les sports de compétition</a>
        <!-- lien vers la page salle de sport omnes -->
        <a href="../../html/salle_de_sport.html">Salle de sport omnes</a>
    </div>

    <!-- FORMULAIRE DE RECHERCHE DANS LE MENU -->
    <div id="rechercheContainer" class="recherche-form">
        <form method="get" action="../../html/recherche/barre_recherche.html">
            <!-- champ de recherche texte -->
            <input type="text" name="q" placeholder="rechercher..." />
            <button type="submit">rechercher</button>
        </form>
    </div>

    <!-- SECTION PRINCIPALE DU CHAT -->
    <section class="main-section">
        <!-- titre de la section incluant le nom du destinataire -->
        <h2>chat avec <?= htmlspecialchars($dest['prenom'] . ' ' . $dest['nom']) ?></h2>
        <div class="chat-box">
            <?php if (count($messages) == 0): ?>
                <!-- si aucun message n existe, on affiche un message informatif -->
                <div>aucun message pour l instant.</div>
            <?php else: ?>
                <!-- parcourt chaque message pour l afficher -->
                <?php foreach ($messages as $msg): ?>
                    <!-- ajoute une classe differente selon si le message vient de l utilisateur courant ou non -->
                    <div class="msg <?= $msg['sender_id'] == $current_user_id ? 'from-me' : 'from-other' ?>">
                        <!-- affiche le nom et prenom de l expediteur -->
                        <strong><?= $msg['prenom'] ?> <?= $msg['nom'] ?>:</strong>
                        <!-- affiche le contenu du message avec les sauts de ligne conserves -->
                        <?= nl2br(htmlspecialchars($msg['contenu'])) ?><br>
                        <!-- affiche la date et l heure d envoi du message -->
                        <small><?= $msg['date_envoi'] ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <!-- formulaire pour envoyer un nouveau message -->
        <form method="post" class="chat-form">
            <!-- zone de texte pour saisir le message, obligatoire -->
            <textarea name="contenu" rows="2" required placeholder="tapez votre message ici..."></textarea>
            <!-- bouton pour envoyer le message -->
            <button type="submit">envoyer</button>
        </form>
        <!-- bouton pour revenir a la liste des chats disponibles -->
        <div class="retour-button">
            <button onclick="window.location.href='chat_select.php'">Retour</button>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer">
        <h3>Contactez-nous</h3>
        <!-- adresse email de contact -->
        <p>Email : <a href="mailto:contact@sportify.fr">contact@sportify.fr</a></p>
        <!-- numero de telephone de contact -->
        <p>Telephone : +33 1 23 45 67 89</p>
        <!-- adresse postale -->
        <p>Adresse : 10 rue sextius michel, 75015 paris, france</p>
        <div class="map">
            <!-- integration de google maps pour afficher la position -->
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
