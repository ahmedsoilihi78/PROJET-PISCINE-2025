<?php
// php/rdv/SupprimerRdvAPI.php
session_start();
require_once __DIR__ . '/../connexion.php';
header('Content-Type: application/json');

// 1) Authentification
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    exit;
}

// 2) Renvoyer les infos de l’utilisateur
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

// 3) Déterminer l’action list/delete
$action = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['action'] ?? '')
    : ($_GET['action']  ?? '');

$user_id = (int) $_SESSION['user_id'];
$role    = $_SESSION['role'] ?? '';

// 4) Lister les RDV
if ($action === 'list') {
    switch ($role) {
        case 'admin':
            $sql = "
                SELECT r.id, r.date, r.heure, r.statut,
                       uc.nom   AS coach_nom,   uc.prenom   AS coach_prenom,
                       uu.nom   AS client_nom,  uu.prenom   AS client_prenom
                FROM rendezvous AS r
                JOIN users AS uc ON uc.id = r.id_coach
                JOIN users AS uu ON uu.id = r.id_client
                ORDER BY r.date, r.heure
            ";
            $params = [];
            break;

        case 'coach':
            $sql = "
                SELECT r.id, r.date, r.heure, r.statut,
                       uc.nom   AS coach_nom,   uc.prenom   AS coach_prenom,
                       uu.nom   AS client_nom,  uu.prenom   AS client_prenom
                FROM rendezvous AS r
                JOIN users AS uc ON uc.id = r.id_coach
                JOIN users AS uu ON uu.id = r.id_client
                WHERE r.id_coach = ?
                ORDER BY r.date, r.heure
            ";
            $params = [$user_id];
            break;

        case 'client':
            $sql = "
                SELECT r.id, r.date, r.heure, r.statut,
                       uc.nom   AS coach_nom,   uc.prenom   AS coach_prenom,
                       uu.nom   AS client_nom,  uu.prenom   AS client_prenom
                FROM rendezvous AS r
                JOIN users AS uc ON uc.id = r.id_coach
                JOIN users AS uu ON uu.id = r.id_client
                WHERE r.id_client = ?
                ORDER BY r.date, r.heure
            ";
            $params = [$user_id];
            break;

        default:
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Rôle invalide']);
            exit;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
    exit;
}

// 5) Supprimer un RDV
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $rdv_id = (int) ($_POST['id'] ?? 0);
    if (!$rdv_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID manquant']);
        exit;
    }

    // Vérifier l'existence et propriétaire/admin
    $chk = $pdo->prepare("SELECT id_coach, id_client FROM rendezvous WHERE id = ?");
    $chk->execute([$rdv_id]);
    $row = $chk->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'RDV introuvable']);
        exit;
    }

    $isAllowed =
        $role === 'admin'
        || ($role === 'coach'  && (int)$row['id_coach']  === $user_id)
        || ($role === 'client' && (int)$row['id_client'] === $user_id);

    if (!$isAllowed) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Accès refusé']);
        exit;
    }

    // Suppression
    $del = $pdo->prepare("DELETE FROM rendezvous WHERE id = ?");
    $del->execute([$rdv_id]);

    echo json_encode(['success' => true, 'message' => 'RDV supprimé']);
    exit;
}

// Action non reconnue
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Action invalide']);
exit;
