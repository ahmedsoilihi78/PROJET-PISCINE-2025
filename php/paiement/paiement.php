<?php
// demarre la session pour garder les informations entre les pages
session_start();
// inclut le fichier de connexion a la base de donnees pour utiliser pdo
require_once __DIR__ . '/../connexion.php';

// si lutilisateur n est pas connecte, on le redirige vers la page de connexion
if (empty($_SESSION['user_id'])) {
    header('location: /../connexion.php');
    exit;
}
// recupere l id et le role de lutilisateur courant depuis la session
$current_user_id   = (int) $_SESSION['user_id'];
$current_user_role = $_SESSION['role'] ?? 'client';

// si lutilisateur est un admin, on recupere la liste des clients pour afficher un select
if ($current_user_role === 'admin') {
    // recuperation des utilisateurs de role client ordonnes par nom et prenom
    $clients = $pdo->query("select id, nom, prenom from users where role='client' order by nom, prenom")
        ->fetchAll(PDO::FETCH_ASSOC);
}

// recupere les informations de lutilisateur courant pour pre remplir le formulaire
$stmtUser = $pdo->prepare("select nom, prenom, adresse, telephone, carte_etudiant from users where id = ?");
$stmtUser->execute([$current_user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC) ?: [];

// recupere les parametres passés dans lurl pour le coach, la date et lheure du rdv
$coach_id = isset($_GET['coach_id']) ? (int) $_GET['coach_id'] : null;
$date     = $_GET['date']  ?? '';
$heure    = $_GET['heure'] ?? '';

$success = false;
$error   = '';

// fonction pour echapper les chaines avant affichage html
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// traitement du formulaire quand il est soumis en methode post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // recupere les valeurs envoyees pour le rdv
    $coach_id       = isset($_POST['coach_id']) ? (int) $_POST['coach_id'] : null;
    $date           = $_POST['date']    ?? '';
    $heure          = $_POST['heure']   ?? '';
    // si lutilisateur est admin, on prend le client selectionne sinon c est le client courant
    $client_id      = (int)($_POST['client_id'] ?? $current_user_id);
    // recupere les informations personnelles du client pour le paiement
    $nom            = trim($_POST['nom'] ?? '');
    $prenom         = trim($_POST['prenom'] ?? '');
    $adresse1       = trim($_POST['adresse1'] ?? '');
    $adresse2       = trim($_POST['adresse2'] ?? '');
    $ville          = trim($_POST['ville'] ?? '');
    $cp             = trim($_POST['cp'] ?? '');
    $pays           = trim($_POST['pays'] ?? '');
    $telephone      = trim($_POST['telephone'] ?? '');
    $carte_etudiant = trim($_POST['carte_etudiant'] ?? '');
    // recupere les informations de carte bancaire
    $type_carte     = $_POST['type_carte'] ?? '';
    // ne conserve que les chiffres du numero de carte
    $num_carte      = preg_replace('/\D/', '', $_POST['num_carte'] ?? '');
    $nom_carte      = trim($_POST['nom_carte'] ?? '');
    // recupere la date d expiration ou vide
    $exp_carte      = $_POST['exp_carte'] == '' ? '' : $_POST['exp_carte'];
    // ne conserve que les chiffres du cvc
    $cvc            = preg_replace('/\D/', '', $_POST['cvc'] ?? '');

    try {
        // demarre une transaction pour garantir la coherence des operations
        $pdo->beginTransaction();

        // insertion d un enregistrement de paiement correspondant au rdv
        $stmt = $pdo->prepare("insert into paiements (id_client, service, montant, statut, date_paiement)
                               values (:cid, :service, :montant, 'valide', now())");
        // description du service avec id du coach, date et heure
        $service = "rdv coach #{$coach_id} le {$date} a {$heure}";
        // montant fixe du rdv
        $montant = 50.00;
        $stmt->execute([
            ':cid'     => $client_id,
            ':service' => $service,
            ':montant' => $montant,
        ]);

        // insertion du rdv dans la table rendezvous avec statut confirme
        $ins = $pdo->prepare("insert into rendezvous (id_coach, id_client, date, heure, statut)
                              values (:coach, :client, :date, :heure, 'confirme')");
        $ins->execute([
            ':coach'  => $coach_id,
            ':client' => $client_id,
            ':date'   => $date,
            ':heure'  => $heure,
        ]);

        // id fixe de l administrateur recevant la notification interne
        $admin_id = 32;
        // construction du contenu du message pour notifier ladministrateur
        $contenu = "nouvelle demande de rdv :\nclient id : {$client_id}\nnom/prenom : {$nom} {$prenom}\nadresse : {$adresse1} " .
            ($adresse2 ? "{$adresse2} " : '') . "- {$ville}, {$cp}, {$pays}\ntel : {$telephone}\ncarte etudiante : {$carte_etudiant}\n" .
            "paiement : {$type_carte} (numero ****" . substr($num_carte, -4) . ")\nnom sur carte : {$nom_carte}, exp : {$exp_carte}, cvc : ***\n";

        // envoie d un message interne a ladministrateur
        $msg = $pdo->prepare("insert into messages (sender_id, receiver_id, type, contenu, date_envoi)
                              values (:sender, :receiver, 'texte', :contenu, now())");
        $msg->execute([
            ':sender'   => $client_id,
            ':receiver' => $admin_id,
            ':contenu'  => $contenu,
        ]);

        // validation de la transaction
        $pdo->commit();
        $success = true;

    } catch (Exception $e) {
        // en cas derreur, annule la transaction et stocke le message derreur
        $pdo->rollBack();
        $error = 'erreur lors du paiement : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <!-- titre de la page pour le paiement -->
    <title>paiement - sportify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- inclusion des feuilles de style -->
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/paiement.css" />
</head>
<body>
<div class="wrapper">

    <!-- HEADER avec titre et logo -->
    <header class="header">
        <div class="title">
            <h1><span class="red">Sportify:</span> <span class="blue">consultation Sportive</span></h1>
        </div>
        <div class="logo">
            <!-- lien vers la page d accueil -->
            <a href="../../html/accueil.html">
                <img src="../../images_accueil/Logo_sportify.png" alt="logo sportify" />
            </a>
        </div>
    </header>

    <!-- NAVIGATION principale -->
    <nav class="navigation">
        <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
        <button onclick="toggleParcourir()">Tout Parcourir</button>
        <button onclick="window.location.href='../../html/recherche/barre_recherche.html'">Recherche</button>
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-vous</button>
        <button onclick="window.location.href='../authentification/votre_compte.php'">Votre compte</button>
    </nav>

    <!-- LIENS DEROULES pour le menu tout parcourir -->
    <div class="parcourir-dropdown" id="parcourirLinks">
        <a href="../tout parcourir/activites_sportives.php">Activités Sportives</a>
        <a href="../tout parcourir/sports_competition.php">Les sports de compétition</a>
        <a href="../../html/salle_de_sport.html">Salle de sport omnes</a>
    </div>

    <!-- FORMULAIRE DE RECHERCHE rapide -->
    <div id="rechercheContainer" class="recherche-form">
        <form method="get" action="../../html/recherche/barre_recherche.html">
            <input type="text" name="q" placeholder="rechercher..." />
            <button type="submit">rechercher</button>
        </form>
    </div>

    <!-- CONTENEUR DU FORMULAIRE DE PAIEMENT -->
    <div class="container">
        <h2>paiement et confirmation rdv</h2>

        <!-- affiche un message derreur si besoin -->
        <?php if ($error): ?>
            <div class="error"><?= h($error) ?></div>
        <?php endif; ?>

        <!-- si le paiement a reussi, on affiche le message de confirmation -->
        <?php if ($success): ?>
            <div class="success">
                votre paiement a ete pris en compte.<br>
                rdv confirme le <?= h($date) ?> a <?= h($heure) ?>.
            </div>
        <?php else: ?>
            <!-- sinon on affiche le formulaire de paiement -->
            <form method="post" autocomplete="off">
                <h3>coordonnees du client</h3>

                <!-- si lutilisateur est admin, on propose un select pour choisir le client -->
                <?php if ($current_user_role === 'admin'): ?>
                    <label>choisir le client :</label>
                    <select name="client_id" required>
                        <option value="">-- selectionnez --</option>
                        <?php foreach ($clients as $cl): ?>
                            <option value="<?= $cl['id'] ?>" <?= (($_POST['client_id'] ?? '') == $cl['id'] ? 'selected' : '') ?>>
                                <?= htmlspecialchars($cl['nom']) ?> <?= htmlspecialchars($cl['prenom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <!-- sinon client_id est fixe a l utilisateur courant -->
                    <input type="hidden" name="client_id" value="<?= $current_user_id ?>">
                <?php endif; ?>

                <div class="row">
                    <div>
                        <label>nom</label>
                        <input type="text" name="nom" required value="<?= h($_POST['nom'] ?? $user['nom'] ?? '') ?>">
                    </div>
                    <div>
                        <label>prenom</label>
                        <input type="text" name="prenom" required value="<?= h($_POST['prenom'] ?? $user['prenom'] ?? '') ?>">
                    </div>
                </div>
                <label>adresse ligne 1</label>
                <input type="text" name="adresse1" required value="<?= h($_POST['adresse1'] ?? $user['adresse'] ?? '') ?>">
                <label>adresse ligne 2</label>
                <input type="text" name="adresse2" value="<?= h($_POST['adresse2'] ?? '') ?>">
                <div class="row">
                    <div>
                        <label>ville</label>
                        <input type="text" name="ville" value="<?= h($_POST['ville'] ?? '') ?>">
                    </div>
                    <div>
                        <label>code postal</label>
                        <input type="text" name="cp" value="<?= h($_POST['cp'] ?? '') ?>">
                    </div>
                </div>
                <label>pays</label>
                <input type="text" name="pays" value="<?= h($_POST['pays'] ?? '') ?>">
                <label>telephone</label>
                <input type="text" name="telephone" required value="<?= h($_POST['telephone'] ?? $user['telephone'] ?? '') ?>">
                <label>carte etudiante</label>
                <input type="text" name="carte_etudiant" value="<?= h($_POST['carte_etudiant'] ?? $user['carte_etudiant'] ?? '') ?>">

                <h3>informations de paiement</h3>
                <label>type de carte de paiement</label>
                <select name="type_carte" required>
                    <option value="">selectionnez</option>
                    <option <?= ($_POST['type_carte'] ?? '') === 'Visa' ? 'selected' : '' ?>>Visa</option>
                    <option <?= ($_POST['type_carte'] ?? '') === 'MasterCard' ? 'selected' : '' ?>>MasterCard</option>
                    <option <?= ($_POST['type_carte'] ?? '') === 'American Express' ? 'selected' : '' ?>>American Express</option>
                    <option <?= ($_POST['type_carte'] ?? '') === 'PayPal' ? 'selected' : '' ?>>PayPal</option>
                </select>
                <label>numero de la carte</label>
                <input type="text" name="num_carte" required maxlength="19" pattern="\d{12,19}" value="<?= h($_POST['num_carte'] ?? '') ?>">
                <label>nom affiche sur la carte</label>
                <input type="text" name="nom_carte" required value="<?= h($_POST['nom_carte'] ?? '') ?>">
                <div class="row">
                    <div>
                        <label>date d expiration</label>
                        <input type="month" name="exp_carte" required value="<?= h($_POST['exp_carte'] ?? '') ?>">
                    </div>
                    <div>
                        <label>code de securite (cvc)</label>
                        <input type="text" name="cvc" required pattern="\d{3,4}" maxlength="4" value="<?= h($_POST['cvc'] ?? '') ?>">
                    </div>
                </div>

                <!-- champs caches pour garder le contexte du rdv -->
                <input type="hidden" name="coach_id" value="<?= h($coach_id) ?>">
                <input type="hidden" name="date"     value="<?= h($date) ?>">
                <input type="hidden" name="heure"    value="<?= h($heure) ?>">

                <button type="submit">valider le paiement</button>
            </form>
        <?php endif; ?>

    </div>

    <!-- bouton pour revenir a la page precedente de prise de rdv -->
    <div class="retour-button">
        <button onclick="window.location.href='../../html/rdv/PrendreRdv.html'">Retour</button>
    </div>


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