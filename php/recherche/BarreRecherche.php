<?php
// php/search.php
session_start();
require_once __DIR__ . '/../connexion.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$coaches = [];
$clients = [];

if ($q !== '') {
    $tText = '%' . strtolower($q) . '%';
    $tNum  = '%' . $q . '%'; // pour la recherche numérique (ID)

    // 1) Rechercher les coachs avec leurs informations utilisateur et disponibilités
    $sql = "
      SELECT 
        c.id,
        u.nom,
        u.prenom,
        u.email,
        u.adresse,
        u.telephone,
        c.specialite,
        GROUP_CONCAT(
          CONCAT(d.jour,' ',d.debut,'-',d.fin)
          SEPARATOR ', '
        ) AS disponibilites
      FROM coachs AS c
      JOIN users AS u ON u.id = c.id
      LEFT JOIN disponibilites AS d ON d.id_coach = c.id
      WHERE (
        CAST(c.id AS CHAR)            LIKE ? OR
        LOWER(u.nom)                  LIKE ? OR
        LOWER(u.prenom)              LIKE ? OR
        LOWER(u.email)               LIKE ? OR
        LOWER(u.adresse)             LIKE ? OR
        LOWER(u.telephone)           LIKE ? OR
        LOWER(c.specialite)          LIKE ? OR
        LOWER(d.jour)                LIKE ? OR
        LOWER(d.debut)               LIKE ? OR
        LOWER(d.fin)                 LIKE ?
      )
      GROUP BY c.id, u.nom, u.prenom, u.email, u.adresse, u.telephone, c.specialite
      ORDER BY u.nom, u.prenom
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array_merge(
        [$tNum],
        array_fill(0, 9, $tText)
    ));
    $coaches = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2) Rechercher les clients
    $sql2 = "
      SELECT 
        u.id,
        u.nom,
        u.prenom,
        u.email,
        u.adresse,
        u.telephone
      FROM users AS u
      WHERE u.role = 'client' AND (
        CAST(u.id AS CHAR)        LIKE ? OR
        LOWER(u.nom)              LIKE ? OR
        LOWER(u.prenom)          LIKE ? OR
        LOWER(u.email)           LIKE ? OR
        LOWER(u.adresse)         LIKE ? OR
        LOWER(u.telephone)       LIKE ?
      )
      ORDER BY u.nom, u.prenom
    ";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->execute(array_merge(
        [$tNum],
        array_fill(0, 5, $tText)
    ));
    $clients = $stmt2->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Recherche globale</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        form { margin-bottom: 20px; }
        input[type="text"] { width: 300px; padding: 6px; }
        button { padding: 6px 12px; }
        table { border-collapse: collapse; width:100%; margin-bottom: 30px; }
        th, td { border:1px solid #ccc; padding:8px; text-align:left; }
        th { background:#f4f4f4; }
        caption { font-weight: bold; margin-bottom: 8px; }
    </style>
</head>
<body>

<h1>Recherche globale</h1>

<form method="get" action="">
    <input
        type="text"
        name="q"
        placeholder="Rechercher..."
        value="<?= htmlspecialchars($q) ?>"
        autofocus
    >
    <button type="submit">Rechercher</button>
</form>

<?php if ($q === ''): ?>
    <p>Entrez un mot-clé pour lancer la recherche.</p>
<?php else: ?>

    <h2>Résultats pour « <?= htmlspecialchars($q) ?> »</h2>

    <!-- Coaches -->
    <table>
        <caption>Coachs (<?= count($coaches) ?>)</caption>
        <thead>
        <tr>
            <th>ID</th><th>Nom</th><th>Prénom</th>
            <th>Email</th><th>Adresse</th><th>Téléphone</th>
            <th>Spécialité</th><th>Disponibilités</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($coaches)): ?>
            <tr><td colspan="8">Aucun coach trouvé.</td></tr>
        <?php else: ?>
            <?php foreach ($coaches as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['id'] ?? '') ?></td>
                    <td><?= htmlspecialchars($c['nom'] ?? '') ?></td>
                    <td><?= htmlspecialchars($c['prenom'] ?? '') ?></td>
                    <td><?= htmlspecialchars($c['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($c['adresse'] ?? '') ?></td>
                    <td><?= htmlspecialchars($c['telephone'] ?? '') ?></td>
                    <td><?= htmlspecialchars($c['specialite'] ?? '') ?></td>
                    <td><?= htmlspecialchars($c['disponibilites'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

    <!-- Clients -->
    <table>
        <caption>Clients (<?= count($clients) ?>)</caption>
        <thead>
        <tr>
            <th>ID</th><th>Nom</th><th>Prénom</th>
            <th>Email</th><th>Adresse</th><th>Téléphone</th>
        </tr>
        </thead>
        <tbody>
        <?php if (empty($clients)): ?>
            <tr><td colspan="6">Aucun client trouvé.</td></tr>
        <?php else: ?>
            <?php foreach ($clients as $u): ?>
                <tr>
                    <td><?= htmlspecialchars($u['id'] ?? '') ?></td>
                    <td><?= htmlspecialchars($u['nom'] ?? '') ?></td>
                    <td><?= htmlspecialchars($u['prenom'] ?? '') ?></td>
                    <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                    <td><?= htmlspecialchars($u['adresse'] ?? '') ?></td>
                    <td><?= htmlspecialchars($u['telephone'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
    </table>

<?php endif; ?>

</body>
</html>