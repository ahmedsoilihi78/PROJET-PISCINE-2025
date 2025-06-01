<?php
// demarre la session pour utiliser les variables de session
session_start();
// inclut le fichier de connexion a la base de donnees pour utiliser pdo
require_once __DIR__ . '/../connexion.php';

// fonction pour envoyer une reponse texte avec code http et arreter l execution
function sendresponse($message, $status = 200) {
    http_response_code($status);
    header('content-type: text/plain');
    echo $message;
    exit;
}

// verifie que la methode http soit bien POST sinon renvoie une erreur 405
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendresponse("methode non autorisee.", 405);
}

// recupere et nettoie les champs obligatoires du formulaire
$nom            = htmlspecialchars(trim($_POST['nom']));
$prenom         = htmlspecialchars(trim($_POST['prenom']));
$email          = htmlspecialchars(trim($_POST['email']));
$mot_de_passe   = $_POST['mot_de_passe'];
$confirm        = $_POST['confirm_password'];
// definit le role fixe a coach
$role           = 'coach';

// recupere les champs facultatifs ou les initialise a null
$adresse        = $_POST['adresse'] ?? null;
$telephone      = $_POST['telephone'] ?? null;
$carte_etudiant = $_POST['carte_etudiant'] ?? null;

// verifie que tous les champs obligatoires sont remplis
if (!$nom || !$prenom || !$email || !$mot_de_passe || !$confirm) {
    sendresponse("tous les champs obligatoires doivent etre remplis.", 400);
}
// verifie que l email est dans un format valide
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendresponse("email invalide.", 400);
}
// verifie que les mots de passe correspondent
if ($mot_de_passe !== $confirm) {
    sendresponse("les mots de passe ne correspondent pas.", 400);
}

// verifie que le fichier cv_pdf est present dans le televersement
if (empty($_FILES['cv_pdf']['tmp_name'])) {
    sendresponse("le cv (pdf) est obligatoire.", 400);
}

try {
    // verifie que l email n est pas deja utilise dans la table users
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        sendresponse("cet email est deja utilise.", 409);
    }

    // cree le hash du mot de passe pour le stocker en base
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    // prepare la requete pour inserer l utilisateur dans la table users
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

    // recupere l id du nouvel utilisateur insere
    $id_user = $pdo->lastInsertId();
} catch (PDOException $e) {
    // en cas d erreur lors de l insertion de l utilisateur, renvoie le message d erreur
    sendresponse("erreur serveur (utilisateur) : " . $e->getMessage(), 500);
}

// recupere les champs specifiques au coach
$specialite = $_POST['specialite'];
$bureau     = $_POST['bureau'];
$video_url  = $_POST['video_url'];
// recupere les disponibilites sous forme de tableau ou initialise a tableau vide
$dispo = $_POST['dispo'] ?? [];

// initialise les chemins pour la photo et le cv_pdf
$photo_path  = '';
$cv_pdf_path = '';

// gerer le televersement de la photo si presente
if (!empty($_FILES['photo']['tmp_name'])) {
    // definit le chemin de destination pour la photo
    $photo_path = '../../uploads/photos/' . basename($_FILES['photo']['name']);
    move_uploaded_file($_FILES['photo']['tmp_name'], $photo_path);
}

// gerer le televersement du cv_pdf (obligatoire)
$cv_pdf_path = '../../uploads/cvs/' . basename($_FILES['cv_pdf']['name']);
if (!move_uploaded_file($_FILES['cv_pdf']['tmp_name'], $cv_pdf_path)) {
    sendresponse("erreur lors de l enregistrement du cv pdf.", 500);
}

// creation d un noeud xml principal pour stocker les donnees du coach
$xml = new SimpleXMLElement('<coach></coach>');
$xml->addChild('id', $id_user);
$xml->addChild('nom', $nom);
$xml->addChild('prenom', $prenom);
$xml->addChild('email', $email);
// ajoute les specialites dans un noeud dédié
$specialites = $xml->addChild('specialites');
$specialites->addChild('specialite', $specialite);
// ajoute le chemin de la photo et du cv_pdf dans l xml
$xml->addChild('photo', $photo_path);
$xml->addChild('cv_pdf', $cv_pdf_path);

// ajoute les formations academiques au format xml
$formations = $xml->addChild('formations');
for ($i = 1; $i <= 2; $i++) {
    $titre = $_POST["formation_titre_$i"];
    $ecole = $_POST["formation_ecole_$i"];
    $annee = $_POST["formation_annee_$i"];
    if ($titre && $ecole && $annee) {
        $formation = $formations->addChild('formation');
        $formation->addChild('titre', $titre);
        $formation->addChild('ecole', $ecole);
        $formation->addChild('annee', $annee);
    }
}

// ajoute les experiences professionnelles au format xml
$experiences = $xml->addChild('experiences');
for ($i = 1; $i <= 2; $i++) {
    $poste = $_POST["experience_poste_$i"];
    $lieu  = $_POST["experience_lieu_$i"];
    $duree = $_POST["experience_duree_$i"];
    if ($poste && $lieu && $duree) {
        $experience = $experiences->addChild('experience');
        $experience->addChild('poste', $poste);
        $experience->addChild('lieu', $lieu);
        $experience->addChild('duree', $duree);
    }
}

// ajoute les certifications au format xml
$certifications = $xml->addChild('certifications');
for ($i = 1; $i <= 2; $i++) {
    $certif = $_POST["certification_$i"];
    if ($certif) {
        $certifications->addChild('certification', $certif);
    }
}

// liste des jours de la semaine pour gerer les disponibilites
$jours = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
$disponibilite = $xml->addChild('disponibilite');
// parcourt chaque jour pour ajouter matin et apresmidi selon le choix de l utilisateur
foreach ($jours as $jour) {
    $jourNode = $disponibilite->addChild('jour');
    $jourNode->addAttribute('nom', $jour);
    $jourNode->addChild('matin', isset($dispo[$jour]['matin']) ? 'true' : 'false');
    $jourNode->addChild('apresmidi', isset($dispo[$jour]['apresmidi']) ? 'true' : 'false');
}

// definit le nom et le chemin du fichier xml genere pour ce coach
$xml_filename = '../../xml/coachs/' . strtolower($prenom . '_' . $nom) . '.xml';
// ecrit le contenu xml dans le fichier
$xml->asXML($xml_filename);

try {
    // prepare la requete pour inserer le coach dans la table coachs
    $stmt = $pdo->prepare("INSERT INTO coachs (id, specialite, bureau, photo, cv_xml, cv_pdf, video_url)
        VALUES (:id, :specialite, :bureau, :photo, :cv_xml, :cv_pdf, :video_url)");
    $stmt->execute([
        ':id'           => $id_user,
        ':specialite'   => $specialite,
        ':bureau'       => $bureau,
        ':photo'        => $photo_path,
        ':cv_xml'       => $xml_filename,
        ':cv_pdf'       => $cv_pdf_path,
        ':video_url'    => $video_url
    ]);
} catch (PDOException $e) {
    // en cas d erreur lors de l insertion du coach, renvoie un message d erreur
    sendresponse("erreur serveur (coach) : " . $e->getMessage(), 500);
}

// enregistrement des disponibilites en base si l utilisateur a selectionne des plages horaires
try {
    $stmtdisp = $pdo->prepare("INSERT INTO disponibilites (id_coach, jour, debut, fin, disponible)
        VALUES (:id_coach, :jour, :debut, :fin, 1)");
    foreach ($jours as $jour) {
        // cle pour recuperer les disponibilites depuis le tableau POST (premiere lettre majuscule)
        $cleform = ucfirst($jour);
        // nom du jour pour la base de donnees en minuscules
        $jourbdd = strtolower($jour);
        // si case matin cochee, insere une plage de 07:00 a 12:00
        if (!empty($dispo[$cleform]['matin'])) {
            $stmtdisp->execute([
                ':id_coach' => $id_user,
                ':jour'     => $jourbdd,
                ':debut'    => '07:00:00',
                ':fin'      => '12:00:00'
            ]);
        }
        // si case apresmidi cochee, insere une plage de 13:00 a 22:00
        if (!empty($dispo[$cleform]['apresmidi'])) {
            $stmtdisp->execute([
                ':id_coach' => $id_user,
                ':jour'     => $jourbdd,
                ':debut'    => '13:00:00',
                ':fin'      => '22:00:00'
            ]);
        }
    }
} catch (PDOException $e) {
    // en cas d erreur lors de l enregistrement des disponibilites, renvoie un message d erreur
    sendresponse("erreur serveur (disponibilites) : " . $e->getMessage(), 500);
}

// envoie une reponse finale indiquant que le coach a ete ajoute avec succes
sendresponse("le coach a ete ajoute avec succes.", 200);
