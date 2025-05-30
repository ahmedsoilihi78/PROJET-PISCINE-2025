<?php

$host   = 'localhost';
$dbname = 'sportify';
$user   = 'root';
$pass   = '';

// TENTE LA CONNEXION A LA BASE DE DONNEE
try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8", $user, $pass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
    // MSG SUCCES -> CONNEXION RÉUSSIT
} catch (PDOException $e) {
    // MSG ERREUR -> CONNEXION ÉCHOUE
    die('Connexion échouée : ' . $e->getMessage());
}

