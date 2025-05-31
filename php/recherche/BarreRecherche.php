<?php
require_once '../connexion.php';

$keyword = '';
if (isset($_GET['q'])) {
    $keyword = trim($_GET['q']);
} elseif (isset($_POST['q'])) {
    $keyword = trim($_POST['q']);
}

$keyword_like = "%$keyword%";
$keyword_lower = mb_strtolower($keyword);

$results = [];
$column_matches = [];

// Dictionnaire des colonnes intéressantes pour chaque table (colonne => table)
$table_columns = [
    'coachs' => ['specialite', 'bureau', 'photo', 'cv_xml', 'cv_pdf', 'video_url'],
    'disponibilites' => ['jour', 'debut', 'fin'],
    'users' => ['nom', 'prenom', 'email', 'adresse'],
    'salle_services' => ['jour', 'ouverture', 'fermeture']
];

// Pour chaque colonne, si le keyword correspond au début du nom, on marque cette colonne/table
foreach ($table_columns as $table => $columns) {
    foreach ($columns as $col) {
        if (strpos($col, $keyword_lower) === 0 || strpos($col, $keyword_lower) !== false) {
            $column_matches[$table][] = $col;
        }
    }
}

// Dictionnaire de mots-clés spéciaux (chat, edit, etc.)
$specials = [
    'chat'      => ['chat', 'mess', 'message'],
    'edit'      => ['edit', 'édit', 'mod', 'modifier', 'éditer'],
    'rdv'       => ['rdv', 'rendez', 'rendezvous', 'rendez-vous'],
    'supprimer' => ['supp', 'supprim', 'delete', 'supprimer']
];
$found_specials = [];

if ($keyword !== '') {
    foreach ($specials as $action => $list) {
        foreach ($list as $partial) {
            if (strpos($keyword_lower, $partial) === 0 || strpos($partial, $keyword_lower) === 0) {
                $found_specials[$action] = true;
                break;
            }
        }
    }
}

// 1. Recherche coachs + users (coachs uniquement)
if ($keyword !== '') {
    // Recherche classique dans les coachs (données)
    $sql = "SELECT c.id, u.nom, u.prenom, u.email, c.specialite, c.bureau, c.photo
            FROM coachs c
            JOIN users u ON u.id = c.id
            WHERE (u.nom LIKE ? OR u.prenom LIKE ? OR u.email LIKE ? 
                OR c.specialite LIKE ? OR c.bureau LIKE ?)
            OR (? LIKE 'coach%' OR ? LIKE 'coa%')";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $keyword_like, $keyword_like, $keyword_like, $keyword_like, $keyword_like,
        $keyword, $keyword
    ]);
    $results['coachs'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si recherche sur colonne coachs
    if (!empty($column_matches['coachs'])) {
        $sql = "SELECT c.id, u.nom, u.prenom, u.email, c.specialite, c.bureau, c.photo, c.cv_xml, c.cv_pdf, c.video_url
                FROM coachs c
                JOIN users u ON u.id = c.id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results['coachs_column'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Disponibilités (données)
    $sql = "SELECT d.*, u.nom, u.prenom
            FROM disponibilites d
            JOIN users u ON u.id = d.id_coach
            WHERE d.jour LIKE ? OR ? LIKE 'disponibilit%' OR ? LIKE 'dispo%'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $keyword_like, $keyword, $keyword
    ]);
    $results['disponibilites'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si recherche sur colonne disponibilites
    if (!empty($column_matches['disponibilites'])) {
        $sql = "SELECT d.*, u.nom, u.prenom
                FROM disponibilites d
                JOIN users u ON u.id = d.id_coach";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results['disponibilites_column'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Salle_services (données)
    $sql = "SELECT * FROM salle_services 
            WHERE jour LIKE ? OR ? LIKE 'salle%' OR ? LIKE 'serv%'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $keyword_like, $keyword, $keyword
    ]);
    $results['salle_services'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Si recherche sur colonne salle_services
    if (!empty($column_matches['salle_services'])) {
        $sql = "SELECT * FROM salle_services";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results['salle_services_column'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Recherche Sportify</title>
    <style>
        body { font-family: Arial; }
        .result-block { border:1px solid #eee; padding:1em; margin:1em 0; border-radius:10px;}
        .label { font-weight:bold; color:#234;}
        .search-bar { margin-bottom:2em; }
        input[type="text"] { font-size:1.1em; padding:0.4em; border-radius:5px; border:1px solid #bbb; }
        input[type="submit"] { font-size:1.1em; padding:0.4em 1.5em; border-radius:5px; border:1px solid #ddd; background:#234; color:#fff; cursor:pointer;}
        .highlight { background: #f2ffc2; }
        .action-block { background: #e8e8fc; border: 1px solid #aab; padding: 1em; margin-bottom: 1em; border-radius: 8px;}
        .action-btn { margin: .4em; padding: .4em 1.2em; background: #234; color: #fff; border: none; border-radius: 5px; cursor: pointer;}
        .action-btn:hover { background: #567;}
    </style>
</head>
<body>
<h1>Recherche Sportify</h1>
<form method="get" class="search-bar" action="">
    <input type="text" name="q" placeholder="Tapez un mot-clé, ex: dispo, coach, special..." value="<?= htmlspecialchars($keyword) ?>">
    <input type="submit" value="Rechercher">
</form>

<h2>Résultat pour "<?= htmlspecialchars($keyword) ?>"</h2>

<?php if ($keyword === ''): ?>
    <p>Veuillez saisir un mot-clé.</p>
<?php else: ?>

    <!-- BLOC D'ACTIONS SPÉCIALES -->
    <?php if (!empty($found_specials)): ?>
        <div class="action-block">
            <?php if (!empty($found_specials['chat'])): ?>
                <button class="action-btn" onclick="window.location.href='../../php/chat/chat_select.php'">Ouvrir la Chatbox</button>
            <?php endif; ?>
            <?php if (!empty($found_specials['edit'])): ?>
                <button class="action-btn" onclick="window.location.href='../../html/authentification/EditerUser.html'">Éditer le profil</button>
            <?php endif; ?>
            <?php if (!empty($found_specials['rdv'])): ?>
                <button class="action-btn" onclick="window.location.href='../../html/rdv/PrendreRdv.html'">Prendre Rendez-vous</button>
                <button class="action-btn" onclick="window.location.href='../../html/rdv/SupprimerRdv.html'">Supprimer Rendez-vous</button>
            <?php endif; ?>
            <?php if (!empty($found_specials['supprimer']) && empty($found_specials['rdv'])): ?>
                <button class="action-btn" onclick="window.location.href='../../html/rdv/SupprimerRdv.html'">Supprimer Rendez-vous</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Résultat colonne coachs -->
    <?php if (!empty($column_matches['coachs'])): ?>
        <div class="result-block highlight">
            <div class="label">Affichage de toute la colonne :
                <?php foreach($column_matches['coachs'] as $col) echo strtoupper($col).' '; ?>
            </div>
            <?php foreach($results['coachs_column'] as $coach): ?>
                <div>
                    <b><?= htmlspecialchars($coach['prenom']) ?> <?= htmlspecialchars($coach['nom']) ?></b>
                    | Email: <?= htmlspecialchars($coach['email']) ?>
                    <?php foreach($column_matches['coachs'] as $col): ?>
                        <?php if (!empty($coach[$col])): ?>
                            <br><?= ucfirst($col) ?> : <?= htmlspecialchars($coach[$col]) ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <br>
                    <!-- Boutons action coach -->
                    <button class="action-btn" onclick="window.location.href='../../php/coach/getCoach.php?id=<?= urlencode($coach['id']) ?>'">Voir profil</button>
                </div>
                <hr>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <!-- Résultat coachs sur data -->
    <?php if (!empty($results['coachs'])): ?>
        <div class="result-block">
            <div class="label">Coach(s) correspondant :</div>
            <?php foreach($results['coachs'] as $coach): ?>
                <div>
                    <b><?= htmlspecialchars($coach['prenom']) ?> <?= htmlspecialchars($coach['nom']) ?></b>
                    | Email: <?= htmlspecialchars($coach['email']) ?>
                    | Spécialité: <?= htmlspecialchars($coach['specialite']) ?>
                    <?php if (!empty($coach['photo'])): ?>
                        <br><img src="<?= htmlspecialchars($coach['photo']) ?>" width="80" alt="photo">
                    <?php endif; ?>
                    <br>Bureau: <?= htmlspecialchars($coach['bureau']) ?>
                    <br>
                    <!-- Boutons action coach -->
                    <button class="action-btn" onclick="window.location.href='../../php/coach/getCoach.php?id=<?= urlencode($coach['id']) ?>'">Voir profil</button>
                </div><hr>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <!-- Résultat colonne disponibilites -->
    <?php if (!empty($column_matches['disponibilites'])): ?>
        <div class="result-block highlight">
            <div class="label">Affichage de toute la colonne :
                <?php foreach($column_matches['disponibilites'] as $col) echo strtoupper($col).' '; ?>
            </div>
            <?php foreach($results['disponibilites_column'] as $d): ?>
                <div>
                    Coach : <b><?= htmlspecialchars($d['prenom']) ?> <?= htmlspecialchars($d['nom']) ?></b>
                    <?php foreach($column_matches['disponibilites'] as $col): ?>
                        <br><?= ucfirst($col) ?> : <?= htmlspecialchars($d[$col]) ?>
                    <?php endforeach; ?>
                </div>
                <hr>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Résultat disponibilités sur data -->
    <?php if (!empty($results['disponibilites'])): ?>
        <div class="result-block">
            <div class="label">Disponibilités trouvées :</div>
            <?php foreach($results['disponibilites'] as $d): ?>
                <div>
                    Coach : <b><?= htmlspecialchars($d['prenom']) ?> <?= htmlspecialchars($d['nom']) ?></b> <br>
                    Jour : <?= htmlspecialchars($d['jour']) ?> |
                    de <?= htmlspecialchars($d['debut']) ?> à <?= htmlspecialchars($d['fin']) ?>
                    <?= $d['disponible'] ? "(disponible)" : "(indisponible)" ?>
                </div><hr>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Résultat colonne salle_services -->
    <?php if (!empty($column_matches['salle_services'])): ?>
        <div class="result-block highlight">
            <div class="label">Affichage de toute la colonne :
                <?php foreach($column_matches['salle_services'] as $col) echo strtoupper($col).' '; ?>
            </div>
            <?php foreach($results['salle_services_column'] as $s): ?>
                <div>
                    <?php
                    // Si on cherche ouverture/fermeture, on affiche aussi le jour
                    $affiche_jour = in_array('ouverture', $column_matches['salle_services']) || in_array('fermeture', $column_matches['salle_services']);
                    ?>
                    <?php foreach($column_matches['salle_services'] as $col): ?>
                        <?php if ($col === 'ouverture' || $col === 'fermeture'): ?>
                            <br><?= ucfirst($col) ?> : <?= htmlspecialchars($s[$col]) ?>
                            <?php if ($affiche_jour): ?>
                                (Jour : <?= htmlspecialchars($s['jour']) ?>)
                            <?php endif; ?>
                        <?php else: ?>
                            <br><?= ucfirst($col) ?> : <?= htmlspecialchars($s[$col]) ?>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
                <hr>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>


    <!-- Résultat salle_services sur data -->
    <?php if (!empty($results['salle_services'])): ?>
        <div class="result-block">
            <div class="label">Services salle trouvés :</div>
            <?php foreach($results['salle_services'] as $s): ?>
                <div>
                    Jour : <?= htmlspecialchars($s['jour']) ?>
                    | Ouverture : <?= htmlspecialchars($s['ouverture']) ?>
                    | Fermeture : <?= htmlspecialchars($s['fermeture']) ?>
                </div><hr>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php
    // Aucun résultat
    if (
        empty($results['coachs']) && empty($results['coachs_column'])
        && empty($results['disponibilites']) && empty($results['disponibilites_column'])
        && empty($results['salle_services']) && empty($results['salle_services_column'])
        && empty($found_specials)
    ): ?>
        <p>Aucun résultat trouvé.</p>
    <?php endif; ?>
<?php endif; ?>
</body>
</html>
