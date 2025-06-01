<?php
// demarre la session pour garder les informations entre les pages
session_start();
// inclut le fichier de connexion a la base de donnees pour utiliser pdo
require_once __DIR__ . '/../connexion.php';

// verifie si l utilisateur est connecte sinon renvoie un message d erreur en json
if (empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'vous devez etre connecte pour prendre un rdv.'
    ]);
    exit;
}
// recupere l id du client depuis la session
$client_id = (int) $_SESSION['user_id'];

// determine l action selon la methode http et l index 'action'
$action = $_SERVER['REQUEST_METHOD'] === 'POST'
    ? ($_POST['action']  ?? '')
    : ($_GET['action']   ?? '');

// selon l action on execute le bloc correspondant
switch ($action) {

    // 1) recuperation de la liste des coachs
    case 'getCoaches':
        // requete pour recuperer l id, le nom et le prenom de tous les coachs
        $sql = "
            select c.id, u.nom, u.prenom
            from coachs as c
            join users as u on u.id = c.id
            where u.role = 'coach'
            order by u.nom, u.prenom
        ";
        // execute la requete et renvoie le resultat en json
        echo json_encode(
            $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC)
        );
        exit;

    // 2) recuperation des informations sur un coach selectionne
    case 'getCoachInfo':
        // recupere l id du coach depuis les parametres get
        $coach_id = (int)($_GET['coach_id'] ?? 0);
        // prepare la requete pour obtenir le nom, prenom et specialite du coach
        $stmt = $pdo->prepare("
            select u.nom, u.prenom, c.specialite
            from coachs as c
            join users as u on c.id = u.id
            where c.id = :id
        ");
        $stmt->execute([':id' => $coach_id]);
        // renvoie les informations du coach au format json
        echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        exit;

    // 3) recuperation du planning de la semaine pour un coach
    case 'getCalendar':
        // recupere l id du coach depuis les parametres get
        $coach_id = (int)($_GET['coach_id'] ?? 0);
        if (!$coach_id) {
            echo json_encode(['error'=>'coach_id manquant']);
            exit;
        }

        // calcule la date du lundi de la semaine courante
        $start = new DateTime('monday this week');
        // prepare un formateur pour obtenir le nom du jour en francais
        $fmt = new IntlDateFormatter(
            'fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE,
            'Europe/Paris', IntlDateFormatter::GREGORIAN, 'EEEE'
        );
        $weekDates = [];
        // boucle pour creer un tableau de 6 jours (lundi a samedi)
        for ($i = 0; $i < 6; $i++) {
            $d = (clone $start)->modify("+{$i} days");
            // formate le nom du jour en premiere lettre majuscule sans accent
            $label = mb_convert_case($fmt->format($d), MB_CASE_TITLE, 'UTF-8');
            $weekDates[] = ['label'=>$label, 'date'=>$d->format('Y-m-d')];
        }

        // recupere les disponibilites du coach dans la semaine courante
        $stm = $pdo->prepare("
            select jour, debut, fin
            from disponibilites
            where id_coach = :cid and disponible = 1
        ");
        $stm->execute([':cid'=>$coach_id]);
        // map pour convertir nom de jour en index (lundi=1, ..., samedi=6)
        $map = ['lundi'=>1,'mardi'=>2,'mercredi'=>3,'jeudi'=>4,'vendredi'=>5,'samedi'=>6];
        $dispoByDay = [];
        // organise les disponibilites par jour de la semaine
        foreach ($stm->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (isset($map[$r['jour']])) {
                $dow = $map[$r['jour']];
                $dispoByDay[$dow][] = [
                    'debut'=>substr($r['debut'],0,5),
                    'fin'  =>substr($r['fin'],0,5)
                ];
            }
        }

        // extrait les dates du monday this week au samedi
        $dates = array_column($weekDates,'date');
        $d1 = $dates[0]; $d2 = end($dates);
        // recupere les rdvs confirmes entre d1 et d2 pour ce coach
        $stm2 = $pdo->prepare("
            select date, heure
            from rendezvous
            where id_coach = :cid
              and statut   = 'confirmé'
              and date between :d1 and :d2
        ");
        $stm2->execute([':cid'=>$coach_id, ':d1'=>$d1, ':d2'=>$d2]);
        $booked = [];
        // organise les heures deja reservees par date
        foreach ($stm2->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $h = substr($r['heure'], 0, 5);
            $booked[$r['date']][$h] = true;
        }

        // cree un tableau d heures de 07:00 a 21:00
        $slots = [];
        for ($h=7; $h<=21; $h++) {
            $slots[] = sprintf('%02d:00',$h);
        }

        $matrix = [];
        // boucle sur chaque date et chaque slot pour definir le statut
        foreach ($dates as $dt) {
            $dow = (int)date('N', strtotime($dt));
            foreach ($slots as $slot) {
                if ($slot === '12:00') {
                    // midi est toujours indisponible
                    $status = 'unavailable';
                } else {
                    $ok = false;
                    // verifie si l heure se trouve dans une plage de dispo
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
                        // si deja reserve, statut booked
                        $status = 'booked';
                    } else {
                        // sinon libre
                        $status = 'free';
                    }
                }
                $matrix[$dt][$slot] = $status;
            }
        }

        // renvoie en json les dates, les slots et la matrice des statuts
        echo json_encode([
            'weekDates'=>$weekDates,
            'slots'    =>$slots,
            'matrix'   =>$matrix
        ]);
        exit;

    // 4) reservation d un créneau pour un rdv
    case 'bookSlot':
        // recupere le coach_id, la date, l heure et l client depuis la session
        $coach_id  = (int)($_POST['coach_id'] ?? 0);
        $date      = $_POST['date']  ?? '';
        $heure     = $_POST['heure'] ?? '';
        $client_id = $_SESSION['user_id'];

        // verifie si ce client a deja un rdv confirme a cette date et heure pour ce coach
        $chk = $pdo->prepare("
            select count(*) from rendezvous
            where id_coach=:cid and id_client=:clid and date=:d and heure=:h and statut='confirmé'
        ");
        $chk->execute([':cid'=>$coach_id,':clid'=>$client_id,':d'=>$date,':h'=>$heure]);
        if ($chk->fetchColumn()>0) {
            echo json_encode([
                'success'=>false,
                'message'=>"ce créneau est deja pris pour ce coach."
            ]);
            exit;
        }

        try {
            // insertion du rendez-vous en base avec statut confirme
            $ins = $pdo->prepare("
                insert into rendezvous (id_coach,id_client,date,heure,statut)
                values (:cid,:clid,:d,:h,'confirmé')
            ");
            $ins->execute([':cid'=>$coach_id,':clid'=>$client_id,':d'=>$date,':h'=>$heure]);
            echo json_encode(['success'=>true]);
        } catch (\Exception $e) {
            // en cas d erreur, renvoie un message d erreur en json
            echo json_encode([
                'success'=>false,
                'message'=>"erreur lors de la reservation : ".$e->getMessage()
            ]);
        }
        exit;

    default:
        // si action non reconnue, renvoie un message d erreur en json
        echo json_encode(['success'=>false,'message'=>'action invalide']);
        exit;
}
