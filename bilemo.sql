-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le :  jeu. 20 juin 2019 à 13:48
-- Version du serveur :  5.7.23
-- Version de PHP :  7.2.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `bilemo`
--

-- --------------------------------------------------------

--
-- Structure de la table `client`
--

DROP TABLE IF EXISTS `client`;
CREATE TABLE IF NOT EXISTS `client` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `roles` json NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `client`
--

INSERT INTO `client` (`id`, `name`, `email`, `password`, `roles`) VALUES
(3, 'client_1', 'client.1@gmail.com', '$argon2i$v=19$m=1024,t=2,p=2$RjJTODVsVUg0ZFpQc214cw$C1t3w28my033qS51QeeAM4997sQ+7l9IK5EMVREM15g', 'null'),
(4, 'bilemo', 'bilemo@gmail.com', '$argon2i$v=19$m=1024,t=2,p=2$L29CUnBqaHlCLjEuSS5xbQ$GKlRgA6RGSGJW/+Wanh/rSXDTmsQfog2RLTS4bF6ftc', '{\"roles\": \"ROLE_ADMIN\"}');

-- --------------------------------------------------------

--
-- Structure de la table `migration_versions`
--

DROP TABLE IF EXISTS `migration_versions`;
CREATE TABLE IF NOT EXISTS `migration_versions` (
  `version` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `executed_at` datetime NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `migration_versions`
--

INSERT INTO `migration_versions` (`version`, `executed_at`) VALUES
('20190425140811', '2019-04-25 14:08:48'),
('20190501091516', '2019-05-01 09:16:11'),
('20190506152804', '2019-05-06 15:31:44'),
('20190506153038', '2019-05-06 15:36:36'),
('20190506153232', '2019-05-06 15:37:24'),
('20190506153346', '2019-05-06 15:37:38'),
('20190509102035', '2019-05-09 10:21:06');

-- --------------------------------------------------------

--
-- Structure de la table `mobile`
--

DROP TABLE IF EXISTS `mobile`;
CREATE TABLE IF NOT EXISTS `mobile` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(65,2) UNSIGNED NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `mobile`
--

INSERT INTO `mobile` (`id`, `name`, `price`, `description`) VALUES
(1, 'Iphone 5', '300.00', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam iaculis pretium ipsum, dictum vestibulum eros eleifend eget. Fusce magna nisl, eleifend in finibus et, pellentesque non nisi. Donec at gravida felis.'),
(2, 'Samsung 6', '450.00', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam iaculis pretium ipsum, dictum vestibulum eros eleifend eget. Fusce magna nisl, eleifend in finibus et, pellentesque non nisi. Donec at gravida felis.'),
(3, 'Windows Lumia', '199.99', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam iaculis pretium ipsum, dictum vestibulum eros eleifend eget. Fusce magna nisl, eleifend in finibus et, pellentesque non nisi. Donec at gravida felis.');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client_id` int(11) NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_8D93D64919EB6921` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `client_id`, `username`, `email`, `created_at`, `updated_at`) VALUES
(15, 3, 'User_1', 'user.1@gmail.com', '2019-06-15 15:43:00', NULL),
(17, 3, 'User_2', 'user.2@gmail.com', '2019-06-20 00:00:00', NULL),
(18, 3, 'User_3', 'user.3@gmail.com', '2019-06-20 08:00:00', NULL);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `FK_8D93D64919EB6921` FOREIGN KEY (`client_id`) REFERENCES `client` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
