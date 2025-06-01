<?php

$host   = 'localhost';
$dbname = 'sportify';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8",
        $user, $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );

    // 1) Création de la table si besoin
    $sqlCreate = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        mot_de_passe VARCHAR(255) NOT NULL,
        role ENUM('admin','coach','client') NOT NULL,
        adresse TEXT,
        telephone VARCHAR(20),
        carte_etudiant VARCHAR(30)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";
    $pdo->exec($sqlCreate);
    echo "Table users prête.\n";

    // 2) Préparation des données du nouvel utilisateur
    $nom     = 'Caret';
    $prenom  = 'Milan';
    $email   = 'milan@sportify.fr';
    $passclr = 'secret';
    $role    = 'admin';


    // 3) Hachage du mot de passe
    $hash = password_hash($passclr, PASSWORD_DEFAULT);

    // 4) Insertion
    $sqlInsert = "
      INSERT INTO users (nom, prenom, email, mot_de_passe, role)
      VALUES (:nom, :prenom, :email, :mdp, :role)
    ";
    $stmt = $pdo->prepare($sqlInsert);
    $stmt->execute([
        ':nom'    => $nom,
        ':prenom' => $prenom,
        ':email'  => $email,
        ':mdp'    => $hash,
        ':role'   => $role
    ]);

    echo "Utilisateur ajouté avec l'ID " . $pdo->lastInsertId() . ".\n";

} catch (PDOException $e) {
    die("Erreur PDO : " . $e->getMessage());
}
