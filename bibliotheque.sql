-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 06 août 2025 à 10:27
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `bibliotheque`
--

-- --------------------------------------------------------

--
-- Structure de la table `livres`
--

CREATE TABLE `livres` (
  `id` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `auteur` varchar(255) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `stock_total` int(11) DEFAULT 1,
  `stock_disponible` int(11) DEFAULT 1,
  `image_couverture` varchar(255) DEFAULT NULL,
  `date_ajout` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `livres`
--

INSERT INTO `livres` (`id`, `titre`, `auteur`, `isbn`, `genre`, `description`, `stock_total`, `stock_disponible`, `image_couverture`, `date_ajout`) VALUES
(1, 'Le Petit Prince', 'Antoine de Saint-Exupéry', '9782070408504', 'Littérature', 'Un conte philosophique et poétique', 3, 2, NULL, '2025-07-17 23:36:40'),
(2, '1984', 'George Orwell', '9782070368228', 'Science-fiction', 'Roman dystopique emblématique', 2, 2, NULL, '2025-07-17 23:36:40'),
(3, 'L\'Étranger', 'Albert Camus', '9782070360024', 'Littérature', 'Roman existentialiste', 2, 2, NULL, '2025-07-17 23:36:40'),
(4, 'Harry Potter à l\'école des sorciers', 'J.K. Rowling', '9782070518425', 'Fantasy', 'Premier tome de la saga Harry Potter', 4, 4, NULL, '2025-07-17 23:36:40'),
(5, 'Vakivakim-piainana', 'Patrique ANDRIAMANGATINA', '47894551256258780023', 'Comedie', 'boky mitantara-piainan&#039;olona iray, zay niezaka nioitra teo amin fiainana, ka tafita tamin fianarany, saingy refa tonga teny ambaravarany lalana makany ivrlany madagasikara izy dia voasolokin&#039;ireo panondrana harempirenena antsokosoko.', 8, 7, NULL, '2025-07-17 23:43:48'),
(6, 'Sikajy n&#039;i DADABE', 'Rafanoharana', '452-7894-652-456-001', 'Roman', 'tatara na Raiamandreny na tovolahy anakiray zay napita fahendrena taminy io zanany io taminy alalany fikajiana lolova miafina ho any zanay rahatrizay raha manam-pahendrena.', 20, 19, NULL, '2025-07-18 05:46:50');

-- --------------------------------------------------------

--
-- Structure de la table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) DEFAULT NULL,
  `livre_id` int(11) DEFAULT NULL,
  `date_reservation` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('en_attente','validee','annulee','terminee') DEFAULT 'en_attente',
  `date_validation` timestamp NULL DEFAULT NULL,
  `date_retour_prevue` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reservations`
--

INSERT INTO `reservations` (`id`, `utilisateur_id`, `livre_id`, `date_reservation`, `statut`, `date_validation`, `date_retour_prevue`) VALUES
(1, 1, 5, '2025-07-17 23:48:40', 'validee', '2025-07-25 09:06:30', NULL),
(2, 2, 1, '2025-07-18 07:46:42', 'en_attente', NULL, NULL),
(3, 3, 6, '2025-07-25 08:53:47', 'validee', '2025-07-25 09:06:06', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` enum('utilisateur','admin') DEFAULT 'utilisateur',
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateurs`
--

INSERT INTO `utilisateurs` (`id`, `nom`, `email`, `mot_de_passe`, `role`, `date_creation`) VALUES
(1, 'Administrateur', 'ralphonsehaja@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2025-07-17 23:36:40'),
(2, 'Dadtoua', 'dadtou@mail.com', '$2y$10$dYtKUi5C7E.b7yOL6q75GOZIg1BWrMzUNuDrIQ1EK9iRgoNhcU22u', 'utilisateur', '2025-07-18 07:46:01'),
(3, 'bASTA', 'toussaint@gmail.com', '$2y$10$N/q1L/Xp6Sob0oICiECbYufCJ9P9AsoxTpCDBLlZ7cWsE/mEPAye2', 'utilisateur', '2025-07-25 08:52:58');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `livres`
--
ALTER TABLE `livres`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`);

--
-- Index pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `livre_id` (`livre_id`);

--
-- Index pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `livres`
--
ALTER TABLE `livres`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`livre_id`) REFERENCES `livres` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
