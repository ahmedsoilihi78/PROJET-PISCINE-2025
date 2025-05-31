<?php
session_start();
require_once __DIR__ . '/../connexion.php';

// Vérifie si l'utilisateur est connecté
if (empty($_SESSION['user_id'])) {
    header('Location: /../connexion.php');
    exit;
}
$current_user_id = (int) $_SESSION['user_id'];
$current_user_role = $_SESSION['role'] ?? 'client';

if ($current_user_role === 'admin') {
    // Liste des clients pour l'admin
    $clients = $pdo->query("SELECT id, nom, prenom FROM users WHERE role='client' ORDER BY nom, prenom")->fetchAll(PDO::FETCH_ASSOC);
}

$stmtUser = $pdo->prepare("SELECT nom, prenom, adresse, telephone, carte_etudiant FROM users WHERE id = ?");
$stmtUser->execute([$current_user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC) ?: [];

$coach_id = isset($_GET['coach_id']) ? (int) $_GET['coach_id'] : null;
$date     = $_GET['date']      ?? '';
$heure    = $_GET['heure']     ?? '';

$success = false;
$error   = '';

function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $coach_id       = isset($_POST['coach_id']) ? (int) $_POST['coach_id'] : null;
    $date           = $_POST['date']        ?? '';
    $heure          = $_POST['heure']       ?? '';
    $client_id      = (int)($_POST['client_id'] ?? $current_user_id);
    $nom            = trim($_POST['nom'] ?? '');
    $prenom         = trim($_POST['prenom'] ?? '');
    $adresse1       = trim($_POST['adresse1'] ?? '');
    $adresse2       = trim($_POST['adresse2'] ?? '');
    $ville          = trim($_POST['ville'] ?? '');
    $cp             = trim($_POST['cp'] ?? '');
    $pays           = trim($_POST['pays'] ?? '');
    $telephone      = trim($_POST['telephone'] ?? '');
    $carte_etudiant = trim($_POST['carte_etudiant'] ?? '');
    $type_carte     = $_POST['type_carte'] ?? '';
    $num_carte      = preg_replace('/\D/', '', $_POST['num_carte'] ?? '');
    $nom_carte      = trim($_POST['nom_carte'] ?? '');
    $exp_carte      = $_POST['exp_carte'] == '' ? '' : $_POST['exp_carte'];
    $cvc            = preg_replace('/\D/', '', $_POST['cvc'] ?? '');

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO paiements (id_client, service, montant, statut, date_paiement)
                               VALUES (:cid, :service, :montant, 'validé', NOW())");
        $service = "RDV coach #{$coach_id} le {$date} à {$heure}";
        $montant = 50.00;
        $stmt->execute([
            ':cid'     => $client_id,
            ':service' => $service,
            ':montant' => $montant,
        ]);

        $ins = $pdo->prepare("INSERT INTO rendezvous (id_coach, id_client, date, heure, statut)
                              VALUES (:coach, :client, :date, :heure, 'confirmé')");
        $ins->execute([
            ':coach'  => $coach_id,
            ':client' => $client_id,
            ':date'   => $date,
            ':heure'  => $heure,
        ]);

        $admin_id = 32;
        $contenu = "Nouvelle demande de RDV :\nClient ID : {$client_id}\nNom/Prénom : {$nom} {$prenom}\nAdresse : {$adresse1} " .
            ($adresse2 ? "{$adresse2} " : '') . "- {$ville}, {$cp}, {$pays}\nTéléphone : {$telephone}\nCarte étudiante : {$carte_etudiant}\n" .
            "Paiement : {$type_carte} (numéro ****" . substr($num_carte, -4) . ")\nNom sur carte : {$nom_carte}, Exp : {$exp_carte}, CVC : ***\n";

        $msg = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, type, contenu, date_envoi)
                              VALUES (:sender, :receiver, 'texte', :contenu, NOW())");
        $msg->execute([
            ':sender'   => $client_id,
            ':receiver' => $admin_id,
            ':contenu'  => $contenu,
        ]);

        $pdo->commit();
        $success = true;

    } catch (Exception $e) {
        $pdo->rollBack();
        $error = 'Erreur lors du paiement : ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - Sportify</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/paiement.css" />
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
        <button onclick="toggleParcourir()">Tout Parcourir</button>
        <button onclick="toggleRecherche()">Recherche</button>
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-Vous</button>
        <button onclick="window.location.href='../authentification/votre_compte.php'">Votre Compte</button>
    </nav>

    <!-- LISTES DÉROULANTES -->
    <div class="parcourir-dropdown" id="parcourirLinks">
        <a href="../../html/activites_sportives.html">Activités sportives</a>
        <a href="../../html/sports_competition.html">Les Sports de compétition</a>
        <a href="../../html/salle_de_sport.html">Salle de sport Omnes</a>
    </div>

    <!-- BARRE DE RECHERCHE -->
    <div id="rechercheContainer" class="recherche-form">
        <form>
            <input type="text" placeholder="Rechercher..." />
            <button type="submit">Rechercher</button>
        </form>
    </div>

    <!-- FORMULAIRE DE PAIEMENT -->
    <div class="container">
        <h2>Paiement et confirmation RDV</h2>

        <?php if ($error): ?>
            <div class="error"><?= h($error) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success">
                Votre paiement a été pris en compte.<br>
                RDV confirmé le <?= h($date) ?> à <?= h($heure) ?>.
            </div>
        <?php else: ?>
            <form method="post" autocomplete="off">
                <h3>Coordonnées du client</h3>

                <?php if ($current_user_role === 'admin'): ?>
                    <label>Choisir le client :</label>
                    <select name="client_id" required>
                        <option value="">-- Sélectionnez --</option>
                        <?php foreach ($clients as $cl): ?>
                            <option value="<?= $cl['id'] ?>" <?= (($_POST['client_id'] ?? '') == $cl['id'] ? 'selected' : '') ?>>
                                <?= htmlspecialchars($cl['nom']) ?> <?= htmlspecialchars($cl['prenom']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="hidden" name="client_id" value="<?= $current_user_id ?>">
                <?php endif; ?>

                <div class="row">
                    <div>
                        <label>Nom</label>
                        <input type="text" name="nom" required value="<?= h($_POST['nom'] ?? $user['nom'] ?? '') ?>">
                    </div>
                    <div>
                        <label>Prénom</label>
                        <input type="text" name="prenom" required value="<?= h($_POST['prenom'] ?? $user['prenom'] ?? '') ?>">
                    </div>
                </div>
                <label>Adresse ligne 1</label>
                <input type="text" name="adresse1" required value="<?= h($_POST['adresse1'] ?? $user['adresse'] ?? '') ?>">
                <label>Adresse ligne 2</label>
                <input type="text" name="adresse2" value="<?= h($_POST['adresse2'] ?? '') ?>">
                <div class="row">
                    <div>
                        <label>Ville</label>
                        <input type="text" name="ville" value="<?= h($_POST['ville'] ?? '') ?>">
                    </div>
                    <div>
                        <label>Code postal</label>
                        <input type="text" name="cp" value="<?= h($_POST['cp'] ?? '') ?>">
                    </div>
                </div>
                <label>Pays</label>
                <input type="text" name="pays" value="<?= h($_POST['pays'] ?? '') ?>">
                <label>Téléphone</label>
                <input type="text" name="telephone" required value="<?= h($_POST['telephone'] ?? $user['telephone'] ?? '') ?>">
                <label>Carte étudiante</label>
                <input type="text" name="carte_etudiant" value="<?= h($_POST['carte_etudiant'] ?? $user['carte_etudiant'] ?? '') ?>">

                <h3>Informations de paiement</h3>
                <label>Type de carte de paiement</label>
                <select name="type_carte" required>
                    <option value="">Sélectionnez</option>
                    <option <?= ($_POST['type_carte'] ?? '') === 'Visa' ? 'selected' : '' ?>>Visa</option>
                    <option <?= ($_POST['type_carte'] ?? '') === 'MasterCard' ? 'selected' : '' ?>>MasterCard</option>
                    <option <?= ($_POST['type_carte'] ?? '') === 'American Express' ? 'selected' : '' ?>>American Express</option>
                    <option <?= ($_POST['type_carte'] ?? '') === 'PayPal' ? 'selected' : '' ?>>PayPal</option>
                </select>
                <label>Numéro de la carte</label>
                <input type="text" name="num_carte" required maxlength="19" pattern="\d{12,19}" value="<?= h($_POST['num_carte'] ?? '') ?>">
                <label>Nom affiché sur la carte</label>
                <input type="text" name="nom_carte" required value="<?= h($_POST['nom_carte'] ?? '') ?>">
                <div class="row">
                    <div>
                        <label>Date d’expiration</label>
                        <input type="month" name="exp_carte" required value="<?= h($_POST['exp_carte'] ?? '') ?>">
                    </div>
                    <div>
                        <label>Code de sécurité (CVC)</label>
                        <input type="text" name="cvc" required pattern="\d{3,4}" maxlength="4" value="<?= h($_POST['cvc'] ?? '') ?>">
                    </div>
                </div>

                <!-- CHAMPS CACHÉS POUR CONTEXTE RDV -->
                <input type="hidden" name="coach_id" value="<?= h($coach_id) ?>">
                <input type="hidden" name="date" value="<?= h($date) ?>">
                <input type="hidden" name="heure" value="<?= h($heure) ?>">

                <button type="submit">Valider le paiement</button>
            </form>
        <?php endif; ?>

    </div>

    <div class="retour-button">
        <button onclick="window.location.href='../../html/rdv/PrendreRdv.html'">Retour</button>
    </div>


    <!-- FOOTER -->
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
                    allowfullscreen=""
                    loading="lazy">
            </iframe>
        </div>
    </footer>
</div>

<!-- SCRIPTS -->
<script src="../../js/bases.js"></script>
</body>
</html>
