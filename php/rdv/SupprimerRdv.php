<?php
// demarre la session pour utiliser les variables de session
session_start();
// inclut le fichier de connexion a la base de donnees pour pouvoir acceder a pdo
require_once __DIR__ . '/../connexion.php';
// indique que la sortie sera en json
header('Content-Type: application/json');

// 1) verifie si l utilisateur est connecte via la session sinon envoie une erreur 401
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'non authentifie']);
    exit;
}

// 2) si l action getUser est demandee en get, renvoie les infos de l utilisateur connecte
if (isset($_GET['action']) && $_GET['action'] === 'getUser') {
    echo json_encode([
        'success' => true,
        'user' => [
            'id'     => (int) $_SESSION['user_id'],
            'nom'    => $_SESSION['nom'],
            'prenom' => $_SESSION['prenom'],
            'role'   => $_SESSION['role']
        ]
    ]);
    exit;
}

// 3) determine l action selon la methode http et le parametre action
$action = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['action'] ?? '')
    : ($_GET['action']  ?? '');

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? '';

// 4) liste des rdv selon le role
if ($action === 'list') {
    switch ($role) {
        case 'admin':
            // si admin on recupere tous les rdv avec infos du coach et du client
            $sql = "
                select r.id, r.date, r.heure, r.statut,
                       uc.nom   as coach_nom,   uc.prenom   as coach_prenom,
                       uu.nom   as client_nom,  uu.prenom   as client_prenom
                from rendezvous as r
                join users as uc on uc.id = r.id_coach
                join users as uu on uu.id = r.id_client
                order by r.date, r.heure
            ";
            $params = [];
            break;

        case 'coach':
            // si coach on recupere ses propres rdv
            $sql = "
                select r.id, r.date, r.heure, r.statut,
                       uc.nom   as coach_nom,   uc.prenom   as coach_prenom,
                       uu.nom   as client_nom,  uu.prenom   as client_prenom
                from rendezvous as r
                join users as uc on uc.id = r.id_coach
                join users as uu on uu.id = r.id_client
                where r.id_coach = ?
                order by r.date, r.heure
            ";
            $params = [$user_id];
            break;

        case 'client':
            // si client on recupere ses propres rdv
            $sql = "
                select r.id, r.date, r.heure, r.statut,
                       uc.nom   as coach_nom,   uc.prenom   as coach_prenom,
                       uu.nom   as client_nom,  uu.prenom   as client_prenom
                from rendezvous as r
                join users as uc on uc.id = r.id_coach
                join users as uu on uu.id = r.id_client
                where r.id_client = ?
                order by r.date, r.heure
            ";
            $params = [$user_id];
            break;

        default:
            // si role non valide on renvoie une erreur 403
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'role invalide']);
            exit;
    }

    // execute la requete pour recuperer les rdv
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    // renvoie le resultat en json
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// 5) suppression d un rdv
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // recupere l id du rdv a supprimer depuis le post
    $rdv_id = (int) ($_POST['id'] ?? 0);
    if (!$rdv_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'id manquant']);
        exit;
    }

    // verifie que le rdv existe et recupere les champs id_coach et id_client
    $chk = $pdo->prepare("select id_coach, id_client from rendezvous where id = ?");
    $chk->execute([$rdv_id]);
    $row = $chk->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        // si aucun rdv trouve, renvoie erreur 404
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'rdv introuvable']);
        exit;
    }

    // verifie si l utilisateur a le droit de supprimer (admin ou proprietaire)
    $isAllowed =
        $role === 'admin'
        || ($role === 'coach'  && (int)$row['id_coach']  === $user_id)
        || ($role === 'client' && (int)$row['id_client'] === $user_id);

    if (!$isAllowed) {
        // si droit refuse, renvoie 403
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'acces refuse']);
        exit;
    }

    // suppression du rdv en base
    $del = $pdo->prepare("delete from rendezvous where id = ?");
    $del->execute([$rdv_id]);

    // renvoie confirmation de suppression
    echo json_encode(['success' => true, 'message' => 'rdv supprime']);
    exit;
}

// action non reconnue
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'action invalide']);
exit;
