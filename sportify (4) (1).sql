-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : sam. 31 mai 2025 à 12:34
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sportify`
--

-- --------------------------------------------------------

--
-- Structure de la table `coachs`
--

DROP TABLE IF EXISTS `coachs`;
CREATE TABLE IF NOT EXISTS `coachs` (
  `id` int NOT NULL,
  `specialite` enum('Musculation/Fitness','Cardio-Training','Biking','Fitness','Cours collectifs','Basketball','Football','Tennis','Rugby','Natation / Plongeon') NOT NULL,
  `bureau` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `cv_xml` varchar(255) DEFAULT NULL,
  `cv_pdf` varchar(255) DEFAULT NULL,
  `video_url` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `coachs`
--

INSERT INTO `coachs` (`id`, `specialite`, `bureau`, `photo`, `cv_xml`, `cv_pdf`, `video_url`) VALUES
(36, 'Musculation/Fitness', 'B101', '../../uploads/photos/tibo_inshape.png', '../../xml/coachs/tibo_inshape.xml', '../../uploads/cvs/tibo_inshape.pdf', 'https://www.youtube.com/embed/h4eYpDz0yP8'),
(38, 'Cardio-Training', 'B102', '../../uploads/photos/christopher_hawa.png', '../../xml/coachs/christopher_hawa.xml', '../../uploads/cvs/christopher_hawa.pdf', 'https://www.youtube.com/embed/pDrqxAPPI1g'),
(39, 'Biking', 'B103', '../../uploads/photos/lucas_hiksan.png', '../../xml/coachs/lucas_hiksanhigrec.xml', '../../uploads/cvs/lucas_hiksan.pdf', 'https://www.youtube.com/embed/1oFwi-06dHw'),
(40, 'Tennis', 'B104', '../../uploads/photos/yasko_deyaski.png', '../../xml/coachs/yasko_deyaski.xml', '../../uploads/cvs/yasko_deyaskin.pdf', 'https://www.youtube.com/embed/1oFwi-06dHw'),
(41, 'Cours collectifs', 'B105', '../../uploads/photos/kostas_mitroglou.png', '../../xml/coachs/konstantinos_mitroglou.xml', '../../uploads/cvs/kostas_mitroglou.pdf', NULL),
(42, 'Basketball', 'B106', '../../uploads/photos/lebran_james.png', '../../xml/coachs/lebran_james.xml', '../../uploads/cvs/lebran_james.pdf', 'https://www.youtube.com/embed/E_IC3yECsyI'),
(43, 'Football', 'B107', '../../uploads/photos/kabe_ocho.png', '../../xml/coachs/kabe_ocho.xml', '../../uploads/cvs/kabe_ocho.pdf', 'https://www.youtube.com/embed/yJdQXen824k'),
(44, 'Rugby', 'EM220', '../../uploads/photos/antoine_dupont.png', '../../xml/coachs/antoine_dupont.xml', '../../uploads/cvs/antoine_dupont.pdf', NULL),
(45, 'Natation / Plongeon', 'EM323', '../../uploads/photos/leon_marchand.png', '../../xml/coachs/leon_marchand.xml', '../../uploads/cvs/leon_marchand.pdf', 'https://www.youtube.com/embed/E0JsuT0NTmE'),
(46, 'Natation / Plongeon', 'G002', '../../uploads/photos/michou_manaudou.png', '../../xml/coachs/michou_manaudou.xml', '../../uploads/cvs/michou_manaudou.pdf', 'https://www.youtube.com/embed/anffHYiUyqg');

-- --------------------------------------------------------

--
-- Structure de la table `disponibilites`
--

DROP TABLE IF EXISTS `disponibilites`;
CREATE TABLE IF NOT EXISTS `disponibilites` (
  `id_coach` int NOT NULL,
  `jour` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL,
  `debut` time NOT NULL,
  `fin` time NOT NULL,
  `disponible` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_coach`,`jour`,`debut`,`fin`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `disponibilites`
--

INSERT INTO `disponibilites` (`id_coach`, `jour`, `debut`, `fin`, `disponible`) VALUES
(36, 'lundi', '13:00:00', '22:00:00', 1),
(36, 'mercredi', '07:00:00', '12:00:00', 1),
(36, 'mercredi', '13:00:00', '22:00:00', 1),
(36, 'vendredi', '07:00:00', '12:00:00', 1),
(36, 'vendredi', '13:00:00', '22:00:00', 1),
(37, 'lundi', '13:00:00', '22:00:00', 1),
(37, 'mardi', '13:00:00', '22:00:00', 1),
(37, 'mercredi', '13:00:00', '22:00:00', 1),
(38, 'lundi', '07:00:00', '12:00:00', 1),
(38, 'lundi', '13:00:00', '22:00:00', 1),
(38, 'mardi', '07:00:00', '12:00:00', 1),
(38, 'mercredi', '13:00:00', '22:00:00', 1),
(38, 'jeudi', '07:00:00', '12:00:00', 1),
(38, 'jeudi', '13:00:00', '22:00:00', 1),
(38, 'vendredi', '13:00:00', '22:00:00', 1),
(39, 'lundi', '07:00:00', '12:00:00', 1),
(39, 'lundi', '13:00:00', '22:00:00', 1),
(39, 'mardi', '13:00:00', '22:00:00', 1),
(39, 'mercredi', '07:00:00', '12:00:00', 1),
(39, 'jeudi', '13:00:00', '22:00:00', 1),
(39, 'vendredi', '07:00:00', '12:00:00', 1),
(39, 'vendredi', '13:00:00', '22:00:00', 1),
(40, 'lundi', '13:00:00', '22:00:00', 1),
(40, 'mardi', '07:00:00', '12:00:00', 1),
(40, 'mercredi', '07:00:00', '12:00:00', 1),
(40, 'mercredi', '13:00:00', '22:00:00', 1),
(40, 'jeudi', '13:00:00', '22:00:00', 1),
(40, 'vendredi', '07:00:00', '12:00:00', 1),
(41, 'lundi', '13:00:00', '22:00:00', 1),
(41, 'mardi', '07:00:00', '12:00:00', 1),
(41, 'mardi', '13:00:00', '22:00:00', 1),
(41, 'mercredi', '13:00:00', '22:00:00', 1),
(41, 'jeudi', '07:00:00', '12:00:00', 1),
(41, 'vendredi', '07:00:00', '12:00:00', 1),
(41, 'vendredi', '13:00:00', '22:00:00', 1),
(41, 'samedi', '13:00:00', '22:00:00', 1),
(42, 'lundi', '07:00:00', '12:00:00', 1),
(42, 'lundi', '13:00:00', '22:00:00', 1),
(42, 'mardi', '07:00:00', '12:00:00', 1),
(42, 'mercredi', '13:00:00', '22:00:00', 1),
(42, 'jeudi', '13:00:00', '22:00:00', 1),
(42, 'vendredi', '07:00:00', '12:00:00', 1),
(42, 'vendredi', '13:00:00', '22:00:00', 1),
(43, 'lundi', '07:00:00', '12:00:00', 1),
(43, 'mardi', '13:00:00', '22:00:00', 1),
(43, 'mercredi', '07:00:00', '12:00:00', 1),
(43, 'mercredi', '13:00:00', '22:00:00', 1),
(43, 'jeudi', '07:00:00', '12:00:00', 1),
(43, 'jeudi', '13:00:00', '22:00:00', 1),
(43, 'vendredi', '07:00:00', '12:00:00', 1),
(44, 'lundi', '07:00:00', '12:00:00', 1),
(44, 'lundi', '13:00:00', '22:00:00', 1),
(44, 'mardi', '13:00:00', '22:00:00', 1),
(44, 'mercredi', '07:00:00', '12:00:00', 1),
(44, 'jeudi', '07:00:00', '12:00:00', 1),
(44, 'jeudi', '13:00:00', '22:00:00', 1),
(44, 'vendredi', '07:00:00', '12:00:00', 1),
(45, 'lundi', '07:00:00', '12:00:00', 1),
(45, 'lundi', '13:00:00', '22:00:00', 1),
(45, 'mardi', '13:00:00', '22:00:00', 1),
(45, 'mercredi', '07:00:00', '12:00:00', 1),
(45, 'jeudi', '07:00:00', '12:00:00', 1),
(45, 'jeudi', '13:00:00', '22:00:00', 1),
(45, 'vendredi', '07:00:00', '12:00:00', 1),
(45, 'vendredi', '13:00:00', '22:00:00', 1),
(46, 'lundi', '07:00:00', '12:00:00', 1),
(46, 'lundi', '13:00:00', '22:00:00', 1),
(46, 'mardi', '13:00:00', '22:00:00', 1),
(46, 'mercredi', '07:00:00', '12:00:00', 1),
(46, 'mercredi', '13:00:00', '22:00:00', 1),
(46, 'vendredi', '07:00:00', '12:00:00', 1),
(46, 'vendredi', '13:00:00', '22:00:00', 1),
(46, 'samedi', '07:00:00', '12:00:00', 1);

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `type` enum('texte','audio','video','email') NOT NULL,
  `contenu` text NOT NULL,
  `date_envoi` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

DROP TABLE IF EXISTS `paiements`;
CREATE TABLE IF NOT EXISTS `paiements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `service` varchar(100) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `statut` enum('validé','refusé') NOT NULL DEFAULT 'validé',
  `date_paiement` date NOT NULL,
  PRIMARY KEY (`id`),
  KEY `id_client` (`id_client`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rendezvous`
--

DROP TABLE IF EXISTS `rendezvous`;
CREATE TABLE IF NOT EXISTS `rendezvous` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_client` int NOT NULL,
  `id_coach` int NOT NULL,
  `date` date NOT NULL,
  `heure` time NOT NULL,
  `statut` enum('confirmé','annulé') NOT NULL DEFAULT 'confirmé',
  PRIMARY KEY (`id`),
  KEY `id_client` (`id_client`),
  KEY `id_coach` (`id_coach`)
) ENGINE=MyISAM AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `salle_services`
--

DROP TABLE IF EXISTS `salle_services`;
CREATE TABLE IF NOT EXISTS `salle_services` (
  `jour` enum('lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche') NOT NULL,
  `ouverture` time DEFAULT NULL,
  `fermeture` time DEFAULT NULL,
  PRIMARY KEY (`jour`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `salle_services`
--

INSERT INTO `salle_services` (`jour`, `ouverture`, `fermeture`) VALUES
('lundi', '07:00:00', '22:00:00'),
('mardi', '07:00:00', '22:00:00'),
('mercredi', '07:00:00', '22:00:00'),
('jeudi', '07:00:00', '22:00:00'),
('vendredi', '07:00:00', '22:00:00'),
('samedi', '07:00:00', '22:00:00');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('admin','coach','client') NOT NULL,
  `adresse` text,
  `telephone` varchar(20) DEFAULT NULL,
  `carte_etudiant` varchar(30) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `role`, `adresse`, `telephone`, `carte_etudiant`) VALUES
(1, 'Soilihi', 'Ahmed', 'ahmed@sportify.fr', '$2y$10$Qjnmxj.Jf.fDDU3MOFmRy.lUJvLBUcGOurC4MZwpdnvkXe7zomi0G', 'admin', '93300, 50 allées des pipis', '0762030867', NULL),
(2, 'Giovanni', 'Oscar', 'oscar@sportify.fr', '$2y$10$FOcrW6D4Ju.Aj.HKRx003OrO2b4kL0XvAimxIJ1ygKRMJPRJPKOtG', 'admin', '10 rue sextius michel, 75015', '0761513484', NULL),
(3, 'Kozman', 'Mathias', 'mathias@sportify.fr', '$2y$10$IgzPLjnvvvowq2EtTe4jxeXdeta0KXmVEKoKoCqMzoSM82TEhjgeW', 'admin', '10 rue sextius michel, 75015', '0783445471', NULL),
(4, 'Caret', 'Milan', 'milan@sportify.fr', '$2y$10$WQVQpN/Qqm/DbffR95WJA.1ZzwKbuP1ca8u6f145FU7kpHMjksece', 'admin', '10 rue sextius michel, 75015', '0695250818', NULL),
(36, 'Inshape', 'Tibo', 'tibz.inshape@sportify.omnes', '$2y$10$vxpV3LfdSAzFfun6.EQqU.R9DZo0IJOloz69uBNN15/fW4DZBZ5mG', 'coach', NULL, NULL, NULL),
(47, 'Mourali', 'Eddy Monster', 'eddy.monster@gmail.com', '$2y$10$dzJ/lo/3TQEWCGyQEmdwyOEh48kIZ5XYVpERULL86sHH7TpjWjLLe', 'client', 'Hotel des Montres', '0768385657', 'B123456789'),
(38, 'Hawa', 'Christopher', 'christopher.hawa@sportify.omnes', '$2y$10$F6DLrLQ1CqzudQLeNcwBD.pe39aE/5gfh3MWEzXdeTo9Jx7Wqu2h.', 'coach', NULL, NULL, NULL),
(32, 'Sportify', 'Entreprise', 'ahmed_d.soilihi@outlook.com', '$2y$10$LNmI8FgKZN1l2D1AWqwhBenx.FOWyBPGpkMlyVS9556kdUnfdU9a6', 'admin', NULL, NULL, NULL),
(39, 'Hiksen-Higrec', 'Lucas', 'lucas.hiksan@sportify.omnes', '$2y$10$14nlzdnqA6xCWtBULA5CWu40enA9HFSCbhu4UgD5rc0MxFsUCVvaC', 'coach', NULL, NULL, NULL),
(40, 'Yaski', 'Yasko', 'yasko.yaski@sportify.omnes', '$2y$10$.bQAyq3vqBLbr91UkNfPgO3KIKoZ13EqqWgu1RSlWhIMNC9DNGoca', 'coach', NULL, NULL, NULL),
(41, 'Mitroglou', 'Konstantinos', 'konstantinos.mitroglou@sportify.omnes', '$2y$10$opce8HoZboUfmW26hFdMZuqg5A3mw930Y/OAfSqGOfcShfIjI43tq', 'coach', NULL, NULL, NULL),
(42, 'James', 'Lebran', 'lebran.james@sportify.omnes', '$2y$10$Ds3DJuze9idICId8lts0Eu21Zf2qKBotquKm3DZUw59DKhUhFxuLi', 'coach', NULL, NULL, NULL),
(43, 'Ocho', 'Kabe', 'kabe.ocho@sportify.omnes', '$2y$10$k6NYajkfOlwG9qrfMIxGlumA3p7aXAOmCW187dxxnqZ8g2u4vweWa', 'coach', NULL, NULL, NULL),
(44, 'Dupont', 'Antoine', 'antoine.dupont@sportify.omnes', '$2y$10$BBD0qXXTHweRFVTXx.KUFeirRQvG8nCReHaJU1OlAbpeXyMi1P6ym', 'coach', NULL, NULL, NULL),
(45, 'Marchand', 'Leon', 'leon.marchand@sportify.omnes', '$2y$10$jKyWAUlrNPZYhmPaRp2EGuyCGPMDogLPYjvVEmC2H2d9k6v2DRQdW', 'coach', NULL, NULL, NULL),
(46, 'Manaudou', 'Michou', 'michou.manaudou@sportify.omnes', '$2y$10$ysUQo8TjmqhDRFbLt2uonu3ZMw1HyUte3K/vVJkrtqOrcmetxXRYG', 'coach', NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
