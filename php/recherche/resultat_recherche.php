<?php
// demarre la session pour utiliser les variables de session
session_start();
// inclut le fichier de connexion a la base de donnees
require_once '../connexion.php';

// initialise le mot cle recherche
$keyword = '';
// recupere le mot cle passe en get ou post
if (isset($_GET['q'])) {
    $keyword = trim($_GET['q']);
} elseif (isset($_POST['q'])) {
    $keyword = trim($_POST['q']);
}

// prepare les modeles de recherche en sql et en minuscule
$keyword_like = "%$keyword%";
$keyword_lower = mb_strtolower($keyword);

// initialise les resultats, correspondances de colonnes et disponibilites des coachs
$results = [];
$column_matches = [];
$coachs_dispos = [];

// dictionnaire des colonnes a prendre en compte pour chaque table
$table_columns = [
    'coachs' => ['specialite', 'bureau', 'photo', 'cv_xml', 'cv_pdf', 'video_url'],
    'disponibilites' => ['jour', 'debut', 'fin'],
    'users' => ['nom', 'prenom', 'email', 'adresse'],
    'salle_services' => ['jour', 'ouverture', 'fermeture']
];

// pour chaque table, on verifie si le mot cle correspond a une colonne
foreach ($table_columns as $table => $columns) {
    foreach ($columns as $col) {
        // si le mot cle est trouve dans le nom de colonne
        if (strpos($col, $keyword_lower) === 0 || strpos($col, $keyword_lower) !== false) {
            $column_matches[$table][] = $col;
        }
    }
}

// dictionnaire de mots speciaux pour afficher des boutons d action
$specials = [
    'chat'      => ['chat', 'mess', 'message'],
    'edit'      => ['edit', 'edit', 'mod', 'modifier', 'editer'],
    'rdv'       => ['rdv', 'rendez', 'rendezvous', 'rendez-vous'],
    'supprimer' => ['supp', 'supprim', 'delete', 'supprimer']
];
$found_specials = [];

// si le mot cle n est pas vide, on cherche les mots speciaux
if ($keyword !== '') {
    foreach ($specials as $action => $list) {
        foreach ($list as $partial) {
            // si le mot cle commence par un terme special ou inverse
            if (strpos($keyword_lower, $partial) === 0 || strpos($partial, $keyword_lower) === 0) {
                $found_specials[$action] = true;
                break;
            }
        }
    }
}

// si on a un mot cle non vide, on effectue la recherche dans plusieurs tables
if ($keyword !== '') {
    // recherche dans les coachs pour les champs utilises
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

    // pour chaque coach trouve, recupere ses disponibilites
    if (!empty($results['coachs'])) {
        foreach ($results['coachs'] as $coach) {
            $id_coach = $coach['id'];
            $sql = "SELECT jour, debut, fin, disponible FROM disponibilites WHERE id_coach = ?";
            $stmt_dispo = $pdo->prepare($sql);
            $stmt_dispo->execute([$id_coach]);
            $coachs_dispos[$id_coach] = $stmt_dispo->fetchAll(PDO::FETCH_ASSOC);
        }
    }

    // affichage de toute la colonne coachs si la colonne est correspondante
    if (!empty($column_matches['coachs'])) {
        $sql = "SELECT c.id, u.nom, u.prenom, u.email, c.specialite, c.bureau, c.photo, c.cv_xml, c.cv_pdf, c.video_url
                FROM coachs c
                JOIN users u ON u.id = c.id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results['coachs_column'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // recherche dans les disponibilites liees aux coachs
    $sql = "SELECT d.*, u.nom, u.prenom
            FROM disponibilites d
            JOIN users u ON u.id = d.id_coach
            WHERE d.jour LIKE ? OR ? LIKE 'disponibilit%' OR ? LIKE 'dispo%'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $keyword_like, $keyword, $keyword
    ]);
    $results['disponibilites'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // affichage de toute la colonne disponibilites si la colonne est correspondante
    if (!empty($column_matches['disponibilites'])) {
        $sql = "SELECT d.*, u.nom, u.prenom
                FROM disponibilites d
                JOIN users u ON u.id = d.id_coach";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results['disponibilites_column'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // recherche dans la table salle_services pour les disponibilites de la salle
    $sql = "SELECT * FROM salle_services 
            WHERE jour LIKE ? OR ? LIKE 'salle%' OR ? LIKE 'serv%'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $keyword_like, $keyword, $keyword
    ]);
    $results['salle_services'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // affichage de toute la colonne salle_services si la colonne est correspondante
    if (!empty($column_matches['salle_services'])) {
        $sql = "SELECT * FROM salle_services";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $results['salle_services_column'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>rechercher - sportify</title>
    <!-- inclusion des styles de base et de la page resultat_recherche -->
    <link rel="stylesheet" href="../../css/bases.css" />
    <link rel="stylesheet" href="../../css/resultat_recherche.css" />
</head>
<body>
<div class="wrapper">

    <!-- header avec titre et logo -->
    <header class="header">
        <div class="title">
            <h1><span class="red">Sportify:</span> <span class="blue">Consultation sportive</span></h1>
        </div>
        <div class="logo">
            <!-- lien vers la page accueil -->
            <a href="../../html/accueil.html">
                <img src="../../images_accueil/Logo_sportify.png" alt="logo sportify" />
            </a>
        </div>
    </header>

    <!-- navigation principale -->
    <nav class="navigation">
        <button onclick="window.location.href='../../html/accueil.html'">Accueil</button>
        <button onclick="toggleParcourir()">Tout Parcourir</button>
        <button onclick="window.location.href='../../html/recherche/barre_recherche.html'">Recherche</button>
        <button onclick="window.location.href='../rdv/ConsulterRdv.php'">Rendez-vous</button>
        <button onclick="window.location.href='../authentification/votre_compte.php'">Votre compte</button>
    </nav>

    <!-- menu deroule pour tout parcourir -->
    <div class="parcourir-dropdown" id="parcourirLinks">
        <a href="../tout parcourir/activites_sportives.php">Activités Sportives</a>
        <a href="../tout parcourir/sports_competition.php">Les sports de compétition</a>
        <a href="../../html/salle_de_sport.html">Salle de sport omnes</a>
    </div>

    <!-- formulaire de recherche -->
    <div id="rechercheContainer" class="recherche-form" style="margin-top: 32px; margin-bottom: 25px;">
        <form method="get" action="../../html/recherche/barre_recherche.html" style="display: flex; justify-content: center; gap:18px;">
            <input type="text" name="q" placeholder="rechercher..." style="font-size:1.15em; border-radius: 9px; border:1px solid #2091fa; padding: 0.45em 1.2em; min-width:280px;">
            <button type="submit" style="font-size:1.13em; border-radius: 9px; background: #2091fa; color:#fff; border:none; padding:0.45em 2.1em; font-weight: bold; cursor:pointer;">rechercher</button>
        </form>
    </div>

    <!-- affiche le mot cle recherche -->
    <h1 class="center-message">Résultat pour "<?= htmlspecialchars($keyword) ?>"</h1>

    <?php if ($keyword === ''): ?>
        <!-- si aucun mot cle saisie, invite a saisir -->
        <div class="center-message"><p>Veuillez saisir un mot clé.</p></div>
    <?php else: ?>

        <!-- bloc des actions specifiques si des mots speciaux sont trouves -->
        <?php if (!empty($found_specials)): ?>
            <div class="result-block highlight center-message">
                <?php if (!empty($found_specials['chat'])): ?>
                    <button class="action-btn" onclick="window.location.href='../../php/chat/chat_select.php'">ouvrir la chatbox</button>
                <?php endif; ?>
                <?php if (!empty($found_specials['edit'])): ?>
                    <button class="action-btn" onclick="window.location.href='../../html/authentification/EditerUser.html'">editer le profil</button>
                <?php endif; ?>
                <?php if (!empty($found_specials['rdv'])): ?>
                    <button class="action-btn" onclick="window.location.href='../../html/rdv/PrendreRdv.html'">prendre rendez-vous</button>
                    <button class="action-btn" onclick="window.location.href='../../html/rdv/SupprimerRdv.html'">supprimer rendez-vous</button>
                <?php endif; ?>
                <?php if (!empty($found_specials['supprimer']) && empty($found_specials['rdv'])): ?>
                    <button class="action-btn" onclick="window.location.href='../../html/rdv/SupprimerRdv.html'">supprimer rendez-vous</button>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- affichage de toute la colonne coachs si correspondance de colonne -->
        <?php if (!empty($column_matches['coachs'])): ?>
            <div class="result-block highlight center-message">
                <div class="label">affichage de toute la colonne:
                    <?php foreach($column_matches['coachs'] as $col) echo strtoupper($col).' '; ?>
                </div>
                <?php foreach($results['coachs_column'] as $coach): ?>
                    <div>
                        <b><?= htmlspecialchars($coach['prenom']) ?> <?= htmlspecialchars($coach['nom']) ?></b>
                        | email: <?= htmlspecialchars($coach['email']) ?>
                        <?php foreach($column_matches['coachs'] as $col): ?>
                            <?php if (!empty($coach[$col])): ?>
                                <br><?= ucfirst($col) ?>: <?= htmlspecialchars($coach[$col]) ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <br>
                        <button class="action-btn" onclick="window.location.href='../../php/coach/getCoach.php?id=<?= urlencode($coach['id']) ?>'">voir profil</button>
                    </div>
                    <hr>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- affichage des coachs trouves sur les donnees -->
        <?php if (!empty($results['coachs'])): ?>
            <?php foreach($results['coachs'] as $coach): ?>
                <div class="result-block highlight center-message">
                    <div class="label">coach(s) correspondant :</div>
                    <div>
                        <b><?= htmlspecialchars($coach['prenom']) ?> <?= htmlspecialchars($coach['nom']) ?></b>
                        | email: <?= htmlspecialchars($coach['email']) ?>
                        | specialite: <?= htmlspecialchars($coach['specialite']) ?>
                        <?php if (!empty($coach['photo'])): ?>
                            <br><img src="<?= htmlspecialchars($coach['photo']) ?>" width="80" alt="photo">
                        <?php endif; ?>
                        <br>bureau: <?= htmlspecialchars($coach['bureau']) ?>
                        <br>
                        <button class="action-btn" onclick="window.location.href='../../php/coach/getCoach.php?id=<?= urlencode($coach['id']) ?>'">voir profil</button>
                    </div>
                </div>
                <!-- affichage des disponibilites du coach si presentes -->
                <?php if (!empty($coachs_dispos[$coach['id']])): ?>
                    <div class="result-block highlight center-message">
                        <div class="label">creneaux de disponibilite de <?= htmlspecialchars($coach['prenom']) ?> <?= htmlspecialchars($coach['nom']) ?> :</div>
                        <?php foreach($coachs_dispos[$coach['id']] as $dispo): ?>
                            <div>
                                <?= htmlspecialchars($dispo['jour']) ?> : de <?= htmlspecialchars($dispo['debut']) ?> a <?= htmlspecialchars($dispo['fin']) ?>
                                <?= isset($dispo['disponible']) ? ($dispo['disponible'] ? "(disponible)" : "(indisponible)") : "" ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- affichage de toute la colonne disponibilites si correspondance de colonne -->
        <?php if (!empty($column_matches['disponibilites'])): ?>
            <div class="result-block highlight center-message">
                <div class="label">affichage de toute la colonne:
                    <?php foreach($column_matches['disponibilites'] as $col) echo strtoupper($col); ?>
                </div>
                <?php foreach($results['disponibilites_column'] as $d): ?>
                    <div>
                        coach: <b><?= htmlspecialchars($d['prenom']) ?> <?= htmlspecialchars($d['nom']) ?></b>
                        <?php foreach($column_matches['disponibilites'] as $col): ?>
                            <br><?= ucfirst($col) ?>: <?= htmlspecialchars($d[$col]) ?>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- affichage des disponibilites trouvees sur les donnees -->
        <?php if (!empty($results['disponibilites'])): ?>
            <div class="result-block highlight center-message">
                <div class="label">disponibilites trouvees :</div>
                <?php foreach($results['disponibilites'] as $d): ?>
                    <div>
                        coach : <b><?= htmlspecialchars($d['prenom']) ?> <?= htmlspecialchars($d['nom']) ?></b> <br>
                        jour : <?= htmlspecialchars($d['jour']) ?> |
                        de <?= htmlspecialchars($d['debut']) ?> a <?= htmlspecialchars($d['fin']) ?>
                        <?= $d['disponible'] ? "(disponible)" : "(indisponible)" ?>
                    </div><hr>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- affichage de toute la colonne salle_services si correspondance de colonne -->
        <?php if (!empty($column_matches['salle_services'])): ?>
            <div class="result-block highlight center-message">
                <div class="label">affichage de toute la colonne:
                    <?php foreach($column_matches['salle_services'] as $col) echo strtoupper($col).' '; ?>
                </div>
                <?php foreach($results['salle_services_column'] as $s): ?>
                    <div>
                        <?php
                        // si on cherche ouverture/fermeture, on affiche aussi le jour
                        $affiche_jour = in_array('ouverture', $column_matches['salle_services']) || in_array('fermeture', $column_matches['salle_services']);
                        ?>
                        <?php foreach($column_matches['salle_services'] as $col): ?>
                            <?php if ($col === 'ouverture' || $col === 'fermeture'): ?>
                                <br><?= ucfirst($col) ?>: <?= htmlspecialchars($s[$col]) ?>
                                <?php if ($affiche_jour): ?>
                                    (jour : <?= htmlspecialchars($s['jour']) ?>)
                                <?php endif; ?>
                            <?php else: ?>
                                <br><?= ucfirst($col) ?>: <?= htmlspecialchars($s[$col]) ?>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- affichage des services salle trouves sur les donnees -->
        <?php if (!empty($results['salle_services'])): ?>
            <div class="result-block highlight center-message">
                <div class="label">services salle trouves :</div>
                <?php foreach($results['salle_services'] as $s): ?>
                    <div>
                        jour : <?= htmlspecialchars($s['jour']) ?>
                        | ouverture : <?= htmlspecialchars($s['ouverture']) ?>
                        | fermeture : <?= htmlspecialchars($s['fermeture']) ?>
                    </div><hr>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php
        // si aucun resultat dans toutes les categories, affiche message aucun resultat
        if (
            empty($results['coachs']) && empty($results['coachs_column'])
            && empty($results['disponibilites']) && empty($results['disponibilites_column'])
            && empty($results['salle_services']) && empty($results['salle_services_column'])
            && empty($found_specials)
        ): ?>
            <div class="center-message"><p>Aucun resultat trouvé.</p></div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- bouton retour vers la page de recherche -->
    <div class="retour-button">
        <button onclick="window.location.href='../../html/recherche/barre_recherche.html'">Retour</button>
    </div>

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

<!-- inclusion du script js de base -->
<script src="../../js/bases.js"></script>
</body>
</html>
