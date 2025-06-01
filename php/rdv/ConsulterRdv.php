<?php
// demarre la session pour utiliser les variables de session
session_start();
// inclut le fichier de connexion a la base de donnees pour pouvoir utiliser pdo
require_once __DIR__ . '/../connexion.php';

// verifie si l utilisateur n est pas connecte, si oui redirige vers la page de connexion
if (empty($_SESSION['user_id'])) {
    header('location: ../authentification/votre_compte.php');
    exit;
}

// recupere l id, le nom, le prenom et le role de l utilisateur depuis la session
$user_id = (int) $_SESSION['user_id'];
$nom     = $_SESSION['nom'] ?? '';
$prenom  = $_SESSION['prenom'] ?? '';
$role    = $_SESSION['role'] ?? '';

// selon le role, on prepare la requete pour recuperer les rendez-vous
switch ($role) {
    case 'admin':
        // pour l admin, on recupere tous les rendez-vous avec coach et client
        $sql = "select r.date, r.heure, r.statut, uc.nom as coach_nom, uc.prenom as coach_prenom, uu.nom as client_nom, uu.prenom as client_prenom
                from rendezvous as r
                join users as uc on uc.id = r.id_coach
                join users as uu on uu.id = r.id_client
                order by r.date, r.heure";
        $params = [];
        break;

    case 'coach':
        // pour le coach, on recupere ses propres rendez-vous
        $sql = "select r.date, r.heure, r.statut, uc.nom as coach_nom, uc.prenom as coach_prenom, uu.nom as client_nom, uu.prenom as client_prenom
                from rendezvous as r
                join users as uc on uc.id = r.id_coach
                join users as uu on uu.id = r.id_client
                where r.id_coach = ?
                order by r.date, r.heure";
        $params = [$user_id];
        break;

    case 'client':
        // pour le client, on recupere ses propres rendez-vous
        $sql = "select r.date, r.heure, r.statut, uc.nom as coach_nom, uc.prenom as coach_prenom, uu.nom as client_nom, uu.prenom as client_prenom
                from rendezvous as r
                join users as uc on uc.id = r.id_coach
                join users as uu on uu.id = r.id_client
                where r.id_client = ?
                order by r.date, r.heure";
        $params = [$user_id];
        break;

    default:
        // si role invalide, on affiche un message et on quitte
        echo "role invalide.";
        exit;
}

// prepare et execute la requete pour recuperer les rendez-vous selon le contexte
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
// recupere tous les rendez-vous sous forme de tableau associatif
$rdvs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>sportify - rendez-vous</title>
    <!-- inclusion des styles de base et de la page rendez_vous -->
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/rendez_vous.css" />
</head>
<body>
<div class="wrapper">
    <!-- header avec titre et logo -->
    <header class="header">
        <div class="title">
            <h1><span class="red">sportify:</span> <span class="blue">consultation sportive</span></h1>
        </div>
        <div class="logo">
            <!-- lien vers la page d accueil avec logo -->
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
        <button onclick="window.location.href='ConsulterRdv.php'">Rendez-vous</button>
        <button onclick="window.location.href='../authentification/votre_compte.php'">Votre compte</button>
    </nav>

    <!-- menu deroule tout parcourir -->
    <div class="parcourir-dropdown" id="parcourirLinks">
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

    <section class="main-section">
        <h2>Mes rendez-vous</h2>
        <!-- affiche les infos de l utilisateur connecte -->
        <p>Connecte : <strong>id <?= $user_id ?> – <?= htmlspecialchars("$nom $prenom") ?> (<?= htmlspecialchars($role) ?>)</strong></p>

        <?php if (empty($rdvs)): ?>
            <!-- si aucun rendez-vous trouve, affiche un message informatif -->
            <p>Aucun rendez-vous trouvé.</p>
        <?php else: ?>
            <!-- sinon, affiche un tableau avec les rendez-vous -->
            <table>
                <thead>
                <tr>
                    <th>date</th>
                    <th>heure</th>
                    <th>coach</th>
                    <th>client</th>
                    <th>statut</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rdvs as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['date']) ?></td>
                        <td><?= htmlspecialchars($r['heure']) ?></td>
                        <td><?= htmlspecialchars($r['coach_nom'] . ' ' . $r['coach_prenom']) ?></td>
                        <td><?= htmlspecialchars($r['client_nom'] . ' ' . $r['client_prenom']) ?></td>
                        <td><?= htmlspecialchars($r['statut']) ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- boutons pour ajouter ou supprimer un rendez-vous -->
        <div style="margin-top: 20px;">
            <button onclick="window.location.href='../../html/rdv/PrendreRdv.html'" class="btn-action"> Ajouter un rendez-vous</button>
            <button onclick="window.location.href='../../html/rdv/SupprimerRdv.html'" class="btn-action" style="background-color: #e74c3c;"> Supprimer un rendez-vous</button>
        </div>
    </section>

    <!-- footer avec contact et carte google maps -->
    <footer class="footer">
        <h3>Contactez-nous</h3>
        <p>Email : <a href="mailto:contact@sportify.fr">contact@sportify.fr</a></p>
        <p>Telephone : +33 1 23 45 67 89</p>
        <p>Adresse : 10 rue sextius michel, 75015 paris, france</p>
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

<script src="../../js/bases.js"></script>
</body>
</html>
