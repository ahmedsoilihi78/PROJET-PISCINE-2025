<?php
session_start();
require_once __DIR__ . '/../connexion.php';

// Si pas connecté → redirige vers login
if (!empty($_SESSION['user_id']) === false) {
    header('Location: /../connexion.php');
    exit;
}
$current_user_id = (int) $_SESSION['user_id'];

// Récupère les infos du client pour pré‑remplissage
$stmtUser = $pdo->prepare("SELECT nom, prenom, adresse, telephone, carte_etudiant FROM users WHERE id = ?");
$stmtUser->execute([$current_user_id]);
$user = $stmtUser->fetch(PDO::FETCH_ASSOC) ?: [];

// Récupération des paramètres de RDV depuis GET (redirection du calendrier)
$coach_id = isset($_GET['coach_id']) ? (int) $_GET['coach_id'] : null;
$date     = $_GET['date']      ?? '';
$heure    = $_GET['heure']     ?? '';

$success = false;
$error   = '';

// fonction d'échappement HTML
function h($str) {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // on sécurise et re‑récupère
    $coach_id       = isset($_POST['coach_id']) ? (int) $_POST['coach_id'] : null;
    $date           = $_POST['date']        ?? '';
    $heure          = $_POST['heure']       ?? '';
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

        // 1) Enregistrement du paiement
        $stmt = $pdo->prepare(
            "INSERT INTO paiements (id_client, service, montant, statut, date_paiement)
             VALUES (:cid, :service, :montant, 'validé', NOW())"
        );
        $service = "RDV coach #{$coach_id} le {$date} à {$heure}";
        $montant = 50.00; // adapter selon tarif
        $stmt->execute([
            ':cid'     => $current_user_id,
            ':service' => $service,
            ':montant' => $montant,
        ]);

        // 2) Création du RDV
        $ins = $pdo->prepare(
            "INSERT INTO rendezvous (id_coach, id_client, date, heure, statut)
             VALUES (:coach, :client, :date, :heure, 'confirmé')"
        );
        $ins->execute([
            ':coach'  => $coach_id,
            ':client' => $current_user_id,
            ':date'   => $date,
            ':heure'  => $heure,
        ]);

        // 3) Envoi d'un message à l'admin (user_id = 32)
        $admin_id = 32;
        $contenu  = "Nouvelle demande de RDV :\n"
            . "Client ID : {$current_user_id}\n"
            . "Nom/Prénom : {$nom} {$prenom}\n"
            . "Adresse : {$adresse1} " . ($adresse2 ? "{$adresse2} " : '') . "- {$ville}, {$cp}, {$pays}\n"
            . "Téléphone : {$telephone}\n"
            . "Carte étudiante : {$carte_etudiant}\n"
            . "Paiement : {$type_carte} (numéro ****" . substr($num_carte, -4) . ")\n"
            . "Nom sur carte : {$nom_carte}, Exp : {$exp_carte}, CVC : ***\n";
        $msg = $pdo->prepare(
            "INSERT INTO messages (sender_id, receiver_id, type, contenu, date_envoi)
             VALUES (:sender, :receiver, 'texte', :contenu, NOW())"
        );
        $msg->execute([
            ':sender'   => $current_user_id,
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
    <style>
        body { font-family: Arial, sans-serif; background: #f9fbfd; padding: 32px; }
        .container { max-width: 550px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 7px #dbe3ee; padding: 32px; }
        h2 { text-align: center; color: #154cc8; }
        form { display: flex; flex-direction: column; gap: 16px; }
        label { font-weight: bold; }
        input, select { width:100%; padding:8px; border-radius:5px; border:1px solid #b4c1d1; }
        .row { display:flex; gap:14px; }
        .row > div { flex:1; }
        button { background:#154cc8; color:#fff; padding:12px; border:none; border-radius:5px; cursor:pointer; font-weight:bold; }
        button:hover { background:#0b3ea8; }
        .success { text-align:center; color:green; font-size:1.2em; margin-top:30px; }
        .error { text-align:center; color:red; margin-top:20px; }
    </style>
</head>
<body>
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
            <option>Visa</option>
            <option>MasterCard</option>
            <option>American Express</option>
            <option>PayPal</option>
        </select>
        <label>Numéro de la carte</label>
        <input type="text" name="num_carte" required maxlength="19" pattern="\d{12,19}">
        <label>Nom affiché sur la carte</label>
        <input type="text" name="nom_carte" required>
        <div class="row">
            <div>
                <label>Date d’expiration</label>
                <input type="month" name="exp_carte" required>
            </div>
            <div>
                <label>Code de sécurité (CVC)</label>
                <input type="text" name="cvc" required pattern="\d{3,4}" maxlength="4">
            </div>
        </div>

        <!-- CHAMPS CACHÉS POUR TRANSMETTRE LE RDV -->
        <input type="hidden" name="coach_id" value="<?= h($coach_id) ?>">
        <input type="hidden" name="date"      value="<?= h($date) ?>">
        <input type="hidden" name="heure"     value="<?= h($heure) ?>">

        <button type="submit">Valider le paiement</button>
    </form>
    <?php endif; ?>
</div>
</body>
</html>