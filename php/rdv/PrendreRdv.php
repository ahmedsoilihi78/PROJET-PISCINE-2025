<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../connexion.php';

// On suppose que la session contient l’id du client
if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Vous devez être connecté pour prendre un RDV.'
    ]);
    exit;
}
$client_id = (int) $_SESSION['user_id'];

$action = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['action']  ?? '')
    : ($_GET['action']   ?? '');

switch ($action) {

    // 1) Liste des coachs
    case 'getCoaches':
        $sql = "
            SELECT c.id, u.nom, u.prenom
            FROM coachs AS c
            JOIN users AS u ON u.id = c.id
            WHERE u.role = 'coach'
            ORDER BY u.nom, u.prenom
        ";
        echo json_encode(
            $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC)
        );
        exit;

    // 2) Infos coach sélectionné
    case 'getCoachInfo':
        $coach_id = (int)($_GET['coach_id'] ?? 0);
        $stmt = $pdo->prepare("
            SELECT u.nom, u.prenom, c.specialite
            FROM coachs AS c
            JOIN users AS u ON c.id = u.id
            WHERE c.id = :id
        ");
        $stmt->execute([':id' => $coach_id]);
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        exit;

    // 3) Planning de la semaine
    case 'getCalendar':
        $coach_id = (int)($_GET['coach_id'] ?? 0);
        if (!$coach_id) {
            echo json_encode(['error'=>'coach_id manquant']);
            exit;
        }

        $start = new DateTime('monday this week');
        $fmt = new IntlDateFormatter(
            'fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE,
            'Europe/Paris', IntlDateFormatter::GREGORIAN, 'EEEE'
        );
        $weekDates = [];
        for ($i = 0; $i < 6; $i++) {
            $d = (clone $start)->modify("+{$i} days");
            $label = mb_convert_case($fmt->format($d), MB_CASE_TITLE, 'UTF-8');
            $weekDates[] = ['label'=>$label, 'date'=>$d->format('Y-m-d')];
        }

        $stm = $pdo->prepare("
            SELECT jour, debut, fin
            FROM disponibilites
            WHERE id_coach = :cid AND disponible = 1
        ");
        $stm->execute([':cid'=>$coach_id]);
        $map = ['lundi'=>1,'mardi'=>2,'mercredi'=>3,'jeudi'=>4,'vendredi'=>5,'samedi'=>6];
        $dispoByDay = [];
        foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (isset($map[$r['jour']])) {
                $dow = $map[$r['jour']];
                $dispoByDay[$dow][] = [
                    'debut'=>substr($r['debut'],0,5),
                    'fin'  =>substr($r['fin'],0,5)
                ];
            }
        }

        $dates = array_column($weekDates,'date');
        $d1 = $dates[0]; $d2 = end($dates);
        $stm2 = $pdo->prepare("
            SELECT date, heure
            FROM rendezvous
            WHERE id_coach = :cid
              AND statut   = 'confirmé'
              AND date BETWEEN :d1 AND :d2
        ");
        $stm2->execute([':cid'=>$coach_id, ':d1'=>$d1, ':d2'=>$d2]);
        $booked = [];
        foreach ($stm2->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $h = substr($r['heure'], 0, 5);
            $booked[$r['date']][$h] = true;
        }

        $slots = [];
        for ($h=7; $h<=21; $h++) {
            $slots[] = sprintf('%02d:00',$h);
        }

        $matrix = [];
        foreach ($dates as $dt) {
            $dow = (int)date('N', strtotime($dt));
            foreach ($slots as $slot) {
                if ($slot === '12:00') {
                    $status = 'unavailable';
                } else {
                    $ok = false;
                    if (!empty($dispoByDay[$dow])) {
                        foreach ($dispoByDay[$dow] as $r) {
                            if ($slot >= $r['debut'] && $slot < $r['fin']) {
                                $ok = true;
                                break;
                            }
                        }
                    }
                    if (!$ok) {
                        $status = 'unavailable';
                    } elseif (!empty($booked[$dt][$slot])) {
                        $status = 'booked';
                    } else {
                        $status = 'free';
                    }
                }
                $matrix[$dt][$slot] = $status;
            }
        }

        echo json_encode([
            'weekDates'=>$weekDates,
            'slots'    =>$slots,
            'matrix'   =>$matrix
        ]);
        exit;

    // 4) Réservation d'un créneau
    case 'bookSlot':
        $coach_id = (int)($_POST['coach_id'] ?? 0);
        $date     = $_POST['date']  ?? '';
        $heure    = $_POST['heure'] ?? '';
        $client_id = $_SESSION['user_id'];

        $chk = $pdo->prepare("
            SELECT COUNT(*) FROM rendezvous
            WHERE id_coach=:cid AND id_client=:clid AND date=:d AND heure=:h AND statut='confirmé'
        ");
        $chk->execute([':cid'=>$coach_id,':clid'=>$client_id,':d'=>$date,':h'=>$heure]);
        if ($chk->fetchColumn()>0) {
            echo json_encode([
                'success'=>false,
                'message'=>"Ce créneau est déjà pris pour ce coach."
            ]);
            exit;
        }

        try {
            $ins = $pdo->prepare("
                INSERT INTO rendezvous (id_coach,id_client,date,heure,statut)
                VALUES (:cid,:clid,:d,:h,'confirmé')
            ");
            $ins->execute([':cid'=>$coach_id,':clid'=>$client_id,':d'=>$date,':h'=>$heure]);
            echo json_encode(['success'=>true]);
        } catch (\Exception $e) {
            echo json_encode([
                'success'=>false,
                'message'=>"Erreur lors de la réservation : ".$e->getMessage()
            ]);
        }
        exit;

    default:
        echo json_encode(['success'=>false,'message'=>'Action invalide']);
        exit;
}
