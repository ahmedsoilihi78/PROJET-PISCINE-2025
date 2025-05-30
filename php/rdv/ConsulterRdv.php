<?php
// php/rdv/ConsulterRdv.php
session_start();
require_once __DIR__ . '/../connexion.php';

// 1) Vérifier que l'utilisateur est connecté
if (empty($_SESSION['user_id'])) {
    header('Location: ../../php/authentification/login.php');
    exit;
}

// Récupération des infos de session pour affichage
$user_id = (int) $_SESSION['user_id'];
$nom     = $_SESSION['nom']    ?? '';
$prenom  = $_SESSION['prenom'] ?? '';
$role    = $_SESSION['role']   ?? '';

// 2) Choisir la requête selon le rôle
switch ($role) {
    case 'admin':
        // tous les RDV
        $sql = "
          SELECT 
            r.id,
            r.date,
            r.heure,
            r.statut,
            uc.nom   AS coach_nom,
            uc.prenom AS coach_prenom,
            uu.nom   AS client_nom,
            uu.prenom AS client_prenom
          FROM rendezvous AS r
          JOIN users AS uc ON uc.id = r.id_coach
          JOIN users AS uu ON uu.id = r.id_client
          ORDER BY r.date, r.heure
        ";
        $params = [];
        break;

    case 'coach':
        // uniquement les RDV de ce coach
        $sql = "
          SELECT 
            r.id,
            r.date,
            r.heure,
            r.statut,
            uc.nom   AS coach_nom,
            uc.prenom AS coach_prenom,
            uu.nom   AS client_nom,
            uu.prenom AS client_prenom
          FROM rendezvous AS r
          JOIN users AS uc ON uc.id = r.id_coach
          JOIN users AS uu ON uu.id = r.id_client
          WHERE r.id_coach = ?
          ORDER BY r.date, r.heure
        ";
        $params = [$user_id];
        break;

    case 'client':
        // uniquement les RDV de ce client
        $sql = "
          SELECT 
            r.id,
            r.date,
            r.heure,
            r.statut,
            uc.nom   AS coach_nom,
            uc.prenom AS coach_prenom,
            uu.nom   AS client_nom,
            uu.prenom AS client_prenom
          FROM rendezvous AS r
          JOIN users AS uc ON uc.id = r.id_coach
          JOIN users AS uu ON uu.id = r.id_client
          WHERE r.id_client = ?
          ORDER BY r.date, r.heure
        ";
        $params = [$user_id];
        break;

    default:
        echo "Rôle invalide.";
        exit;
}

// 3) Exécuter la requête et récupérer les résultats
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rdvs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Consultation des RDV</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width:100%; margin-top:20px; }
        th, td { border:1px solid #ccc; padding:8px; text-align:left; }
        th { background:#f4f4f4; }
        tr:nth-child(even) { background:#fafafa; }
    </style>
</head>
<body>
<h1>Mes rendez-vous</h1>
<p>
    Connecté :
    <strong>
        ID <?= $user_id ?> – <?= htmlspecialchars($nom . ' ' . $prenom) ?>
        (<?= htmlspecialchars($role) ?>)
    </strong>
</p>

<?php if (empty($rdvs)): ?>
    <p>Aucun rendez-vous trouvé.</p>
<?php else: ?>
    <table>
        <thead>
        <tr>
            <th>Date</th>
            <th>Heure</th>
            <th>Coach</th>
            <th>Client</th>
            <th>Statut</th>
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

</body>
</html>
// AJOUTER UN BOUTON POUR RETOUR VERS LA PAGE VOTRE COMPTE