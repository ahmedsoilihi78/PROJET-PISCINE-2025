<?php
session_start();
require_once __DIR__ . '/../connexion.php';

// Traitement du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nom       = htmlspecialchars(trim($_POST['nom']));
    $prenom    = htmlspecialchars(trim($_POST['prenom']));
    $email     = htmlspecialchars(trim($_POST['email']));
    $mot_de_passe = $_POST['mot_de_passe'];
    $confirm   = $_POST['confirm_password'];
    $role      = 'client';

    $adresse        = !empty($_POST['adresse']) ? htmlspecialchars(trim($_POST['adresse'])) : null;
    $telephone      = !empty($_POST['telephone']) ? htmlspecialchars(trim($_POST['telephone'])) : null;
    $carte_etudiant = !empty($_POST['carte_etudiant']) ? htmlspecialchars(trim($_POST['carte_etudiant'])) : null;

    // Vérification des champs obligatoires
    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($confirm)) {
        $_SESSION['error'] = "Tous les champs obligatoires doivent être remplis.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Email invalide.";
    } elseif ($mot_de_passe !== $confirm) {
        $_SESSION['error'] = "Les mots de passe ne correspondent pas.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);

            if ($stmt->fetch()) {
                header("Location: ../../php/authentification/FormulaireInscription.php");
                $_SESSION['error'] = "Cet email est déjà utilisé.";

            } else {
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

                $_SESSION['success'] = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
                header("Location: login.php"); // redirection vers le page souhaiter
                exit();
            }
        } catch (PDOException $e) {
            $_SESSION['error'] = "Erreur serveur : " . $e->getMessage();
        }
    }
}
?>
