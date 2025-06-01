<?php
// demarre la session pour utiliser les variables de session
session_start();
// inclut le fichier de connexion a la base de donnees pour pouvoir executer des requetes
require_once __DIR__ . '/../connexion.php';

// verifie que l identifiant du coach est bien fourni dans le formulaire
if (!isset($_POST['id'])) {
    echo "aucun identifiant fourni.";
    exit;
}
// convertit l identifiant en entier pour securite
$id_coach = intval($_POST['id']);

try {
    // verifie que le coach existe dans la table coachs
    $stmt = $pdo->prepare("SELECT * FROM coachs WHERE id = :id");
    $stmt->execute([':id' => $id_coach]);
    $coach = $stmt->fetch(PDO::FETCH_ASSOC);

    // si aucun coach trouve avec cet identifiant, affiche un message et quitte
    if (!$coach) {
        echo "aucun coach trouve avec cet identifiant.";
        exit;
    }

    // supprime la photo du coach si elle existe
    if (!empty($coach['photo']) && file_exists($coach['photo'])) {
        unlink($coach['photo']);
    }
    // supprime le fichier xml du cv si il existe
    if (!empty($coach['cv_xml']) && file_exists($coach['cv_xml'])) {
        unlink($coach['cv_xml']);
    }

    // reconstruit le chemin du fichier xml a partir du prenom et nom de l utilisateur
    $stmtUser = $pdo->prepare("SELECT prenom, nom FROM users WHERE id = :id");
    $stmtUser->execute([':id' => $id_coach]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $xmlPath = '../../xml/coachs/' . strtolower($user['prenom'] . '_' . $user['nom']) . '.xml';
        // supprime le fichier xml reconstruit si il existe
        if (file_exists($xmlPath)) {
            unlink($xmlPath);
        }
    }

    // supprime toutes les disponibilites liees a ce coach
    $stmt = $pdo->prepare("DELETE FROM disponibilites WHERE id_coach = :id");
    $stmt->execute([':id' => $id_coach]);

    // supprime tous les rendezvous du coach pour eviter les donnees orphanes
    $stmt = $pdo->prepare("DELETE FROM rendezvous WHERE id_coach = :id");
    $stmt->execute([':id' => $id_coach]);

    // supprime l enregistrement du coach dans la table coachs
    $stmt = $pdo->prepare("DELETE FROM coachs WHERE id = :id");
    $stmt->execute([':id' => $id_coach]);

    // supprime l utilisateur correspondant dans la table users
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $id_coach]);

    // confirme la suppression reussie
    echo "le coach et ses donnees associees ont ete supprimes avec succes.";
} catch (PDOException $e) {
    // en cas d erreur serveur, affiche le message d erreur
    echo "erreur serveur : " . $e->getMessage();
}
?>
