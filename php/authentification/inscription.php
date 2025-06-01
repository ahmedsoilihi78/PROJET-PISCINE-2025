<?php
// demarre la session pour stocker les messages d erreur et de succes
session_start();
// inclut le fichier de connexion a la base de donnees
require_once __DIR__ . '/../connexion.php';

// si la requete est en methode post, on traite les donnees du formulaire
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // recupere et nettoie les informations envoyees par le formulaire
    $nom           = htmlspecialchars(trim($_POST['nom']));
    $prenom        = htmlspecialchars(trim($_POST['prenom']));
    $email         = htmlspecialchars(trim($_POST['email']));
    $mot_de_passe  = $_POST['mot_de_passe'];
    $confirm       = $_POST['confirm_password'];
    // definit le role par defaut pour l utilisateur
    $role          = 'client';

    // recupere les donnees facultatives ou initialise a null si vide
    $adresse        = !empty($_POST['adresse']) ? htmlspecialchars(trim($_POST['adresse'])) : null;
    $telephone      = !empty($_POST['telephone']) ? htmlspecialchars(trim($_POST['telephone'])) : null;
    $carte_etudiant = !empty($_POST['carte_etudiant']) ? htmlspecialchars(trim($_POST['carte_etudiant'])) : null;

    // verifie que les champs obligatoires ne sont pas vides
    if (empty($nom) || empty($prenom) || empty($email) || empty($mot_de_passe) || empty($confirm)) {
        $_SESSION['error'] = "tous les champs obligatoires doivent etre remplis.";
    }
    // verifie que l email a un format valide
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "email invalide.";
    }
    // verifie que les mots de passe correspondent
    elseif ($mot_de_passe !== $confirm) {
        $_SESSION['error'] = "les mots de passe ne correspondent pas.";
    } else {
        try {
            // prepare la requete pour verifier si l email existe deja dans la table users
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);

            // si l email est deja utilise, on renvoie vers le formulaire avec un message d erreur
            if ($stmt->fetch()) {
                header("Location: FormulaireInscription.php");
                $_SESSION['error'] = "cet email est deja utilise.";
            } else {
                // si l email est libre, on genere le hash du mot de passe
                $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);

                // prepare la requete pour inserer le nouvel utilisateur dans la base
                $stmt = $pdo->prepare("
                    INSERT INTO users (nom, prenom, email, mot_de_passe, role, adresse, telephone, carte_etudiant)
                    VALUES (:nom, :prenom, :email, :mot_de_passe, :role, :adresse, :telephone, :carte_etudiant)
                ");

                // execute la requete en passant les valeurs liees
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

                // definit le message de succes dans la session
                $_SESSION['success'] = "inscription reussie ! vous pouvez maintenant vous connecter.";
                // redirige vers la page de compte apres succes
                header("Location: votre_compte.php");
                exit();
            }
        } catch (PDOException $e) {
            // en cas d erreur serveur, stocke le message d erreur dans la session
            $_SESSION['error'] = "erreur serveur : " . $e->getMessage();
        }
    }
}
?>
