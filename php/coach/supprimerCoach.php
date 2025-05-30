<?php
session_start();
require_once __DIR__ . '/../connexion.php';

if (!isset($_GET['id'])) {
    echo "Aucun identifiant fourni.";
    exit;
}

$id_coach = intval($_GET['id']);

try {
    // Vérifie que le coach existe
    $stmt = $pdo->prepare("SELECT * FROM coachs WHERE id = :id");
    $stmt->execute([':id' => $id_coach]);
    $coach = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$coach) {
        echo "Aucun coach trouvé avec cet identifiant.";
        exit;
    }

    // Supprimer les fichiers (si existent)
    if (!empty($coach['photo']) && file_exists($coach['photo'])) {
        unlink($coach['photo']);
    }
    if (!empty($coach['cv_xml']) && file_exists($coach['cv_xml'])) {
        unlink($coach['cv_xml']);
    }

    // Supprimer le fichier XML (chemin reconstruit à partir du prénom/nom si besoin)
    $stmtUser = $pdo->prepare("SELECT prenom, nom FROM users WHERE id = :id");
    $stmtUser->execute([':id' => $id_coach]);
    $user = $stmtUser->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        $xmlPath = '../../xml/coachs/' . strtolower($user['prenom'] . '_' . $user['nom']) . '.xml';
        if (file_exists($xmlPath)) {
            unlink($xmlPath);
        }
    }

    // Supprimer les disponibilités liées
    $stmt = $pdo->prepare("DELETE FROM disponibilites WHERE id_coach = :id");
    $stmt->execute([':id' => $id_coach]);

    // Supprimer les rendez-vous du coach
    $stmt = $pdo->prepare("DELETE FROM rendezvous WHERE id_coach = :id");
    $stmt->execute([':id' => $id_coach]);

    // Supprimer le coach
    $stmt = $pdo->prepare("DELETE FROM coachs WHERE id = :id");
    $stmt->execute([':id' => $id_coach]);

    // Supprimer l'utilisateur
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
    $stmt->execute([':id' => $id_coach]);

    echo "Le coach et ses données associées ont été supprimés avec succès.";
} catch (PDOException $e) {
    echo "Erreur serveur : " . $e->getMessage();
}
?>
