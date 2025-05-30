<?php
session_start();
require_once __DIR__ . '/../connexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Étape 1 : Création utilisateur
    $nom       = htmlspecialchars(trim($_POST['nom']));
    $prenom    = htmlspecialchars(trim($_POST['prenom']));
    $email     = htmlspecialchars(trim($_POST['email']));
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirm   = $_POST['confirm_password'];
    $role      = 'coach';

    $adresse        = !empty($_POST['adresse']) ? htmlspecialchars(trim($_POST['adresse'])) : null;
    $telephone      = !empty($_POST['telephone']) ? htmlspecialchars(trim($_POST['telephone'])) : null;
    $carte_etudiant = !empty($_POST['carte_etudiant']) ? htmlspecialchars(trim($_POST['carte_etudiant'])) : null;

    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($confirm)) {
        echo "Tous les champs obligatoires doivent être remplis.";
        exit;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "Email invalide.";
        exit;
    } elseif ($mot_de_passe !== $confirm) {
        echo "Les mots de passe ne correspondent pas.";
        exit;
    }

    try {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);

        if ($stmt->fetch()) {
            echo "Cet email est déjà utilisé.";
            exit;
        }

        $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, role, adresse, telephone, carte_etudiant)
            VALUES (:nom, :prenom, :email, :mot_de_passe, :role, :adresse, :telephone, :carte_etudiant)");

        $stmt->execute([
            'nom'            => $nom,
            'prenom'         => $prenom,
            'email'          => $email,
            'mot_de_passe'   => $mot_de_passe_hash,
            'role'           => $role,
            'adresse'        => $adresse,
            'telephone'      => $telephone,
            'carte_etudiant' => $carte_etudiant
        ]);

        $id_user = $pdo->lastInsertId();
    } catch (PDOException $e) {
        echo "Erreur serveur (utilisateur) : " . $e->getMessage();
        exit;
    }

    // Étape 2 : Enregistrement du coach (ID identique à users.id)
    $specialite = $_POST['specialite'];
    $bureau = $_POST['bureau'];
    $video_url = $_POST['video_url'];
    $dispo = $_POST['dispo'];

    $photo_path = '';
    $cv_pdf_path = '';

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $photo_path = '../../uploads/photos/' . basename($_FILES['photo']['name']);
        move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
    }

    if (isset($_FILES['cv_pdf']) && $_FILES['cv_pdf']['error'] == 0) {
        $cv_pdf_path = '../../uploads/cvs/' . basename($_FILES['cv_pdf']['name']);
        move_uploaded_file($_FILES['cv_pdf']['tmp_name'], $cv_pdf_path);
    }

    try {
        $sql = "INSERT INTO coachs (id, specialite, bureau, photo, cv_xml, video_url)
                VALUES (:id, :specialite, :bureau, :photo, :cv_pdf, :video_url)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':id' => $id_user, // même ID que l'utilisateur
            ':specialite' => $specialite,
            ':bureau' => $bureau,
            ':photo' => $photo_path,
            ':cv_pdf' => $cv_pdf_path,
            ':video_url' => $video_url
        ]);
    } catch (PDOException $e) {
        echo "Erreur serveur (coach) : " . $e->getMessage();
        exit;
    }


// ——— NOUVEAU : insertion des disponibilités ———
    try {
        // ID unique du coach = même que l'utilisateur
        $stmtDisp = $pdo->prepare("
        INSERT INTO disponibilites (id_coach, jour, debut, fin, disponible)
        VALUES (:id_coach, :jour, :debut, :fin, 1)
    ");

        // Jours et créneaux fixes
        $jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
        foreach ($jours as $jour) {
            $cleForm = ucfirst($jour);           // ex. "Lundi" pour $_POST['dispo']
            $jourBdd = strtolower($jour);        // ex. "lundi" pour l'ENUM

            // Matin : 07:00–12:00
            if (!empty($dispo[$cleForm]['matin'])) {
                $stmtDisp->execute([
                    ':id_coach' => $id_user,
                    ':jour'     => $jourBdd,
                    ':debut'    => '07:00:00',
                    ':fin'      => '12:00:00',
                ]);
            }

            // Après-midi : 13:00–22:00
            if (!empty($dispo[$cleForm]['apresmidi'])) {
                $stmtDisp->execute([
                    ':id_coach' => $id_user,
                    ':jour'     => $jourBdd,
                    ':debut'    => '13:00:00',
                    ':fin'      => '22:00:00',
                ]);
            }
        }
    } catch (PDOException $e) {
        echo "Erreur serveur (disponibilités) : " . $e->getMessage();
        exit;
    }


    // Étape 3 : Génération du XML
    $xml = new SimpleXMLElement('<coach></coach>');
    $xml->addChild('id', $id_user);
    $xml->addChild('nom', $nom);
    $xml->addChild('prenom', $prenom);
    $xml->addChild('email', $email);

    $specialites = $xml->addChild('specialites');
    $specialites->addChild('specialite', $specialite);

    $xml->addChild('photo', $photo_path);
    $xml->addChild('cv_pdf', $cv_pdf_path);

    // Formations (2)
    $formations = $xml->addChild('formations');
    for ($i = 1; $i <= 2; $i++) {
        $titre = $_POST["formation_titre_$i"];
        $ecole = $_POST["formation_ecole_$i"];
        $annee = $_POST["formation_annee_$i"];
        if (!empty($titre) && !empty($ecole) && !empty($annee)) {
            $formation = $formations->addChild('formation');
            $formation->addChild('titre', $titre);
            $formation->addChild('ecole', $ecole);
            $formation->addChild('annee', $annee);
        }
    }

    // Expériences (2)
    $experiences = $xml->addChild('experiences');
    for ($i = 1; $i <= 2; $i++) {
        $poste = $_POST["experience_poste_$i"];
        $lieu = $_POST["experience_lieu_$i"];
        $duree = $_POST["experience_duree_$i"];
        if (!empty($poste) && !empty($lieu) && !empty($duree)) {
            $experience = $experiences->addChild('experience');
            $experience->addChild('poste', $poste);
            $experience->addChild('lieu', $lieu);
            $experience->addChild('duree', $duree);
        }
    }

    // Certifications (2)
    $certifications = $xml->addChild('certifications');
    for ($i = 1; $i <= 2; $i++) {
        $certif = $_POST["certification_$i"];
        if (!empty($certif)) {
            $certifications->addChild('certification', $certif);
        }
    }

    // Disponibilités dans le XML
    $disponibilite = $xml->addChild('disponibilite');
    $jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
    foreach ($jours as $jour) {
        $jourNode = $disponibilite->addChild('jour');
        $jourNode->addAttribute('nom', $jour);
        $jourNode->addChild('matin', isset($dispo[$jour]['matin']) ? 'true' : 'false');
        $jourNode->addChild('apresmidi', isset($dispo[$jour]['apresmidi']) ? 'true' : 'false');
    }

    $xml_filename = '../../xml/coachs/' . strtolower($prenom . '_' . $nom) . '.xml';
    $xml->asXML($xml_filename);

    echo "Coach ajouté avec succès. Fichier XML généré : $xml_filename";
}
?>
