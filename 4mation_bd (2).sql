-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : lun. 04 mai 2026 à 10:23
-- Version du serveur : 8.0.45
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `4mation_bd`
--

-- --------------------------------------------------------

--
-- Structure de la table `admins`
--

DROP TABLE IF EXISTS `admins`;
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `email`, `nom`, `prenom`) VALUES
(1, 'lucienmbida12', '$2y$10$ADh/2hvNhNGShMo.eGMKPuezMwjXWJfWNGF.MqT7eTgnNYU/bp08i', 'root@gmail.com', 'mbida', 'lucien'),
(2, 'suzanlirus47', '$2y$10$qvcOb5gYeDF0.E6crAmS0exjKuLoVxXWqN3aW54D9LIbI.zK9G8iS', 'suz@gmail.com', 'lirus', 'suzan'),
(3, 'pablorius95', '$2y$10$f5bdVrWvppg2eOIOL1p3y.ry7pv242X.zBLcyCwMhcpKl1BzL5q.O', 'padR@gmail.com', 'rius', 'pablo');

-- --------------------------------------------------------

--
-- Structure de la table `candidatures`
--

DROP TABLE IF EXISTS `candidatures`;
CREATE TABLE IF NOT EXISTS `candidatures` (
  `id` int NOT NULL AUTO_INCREMENT,
  `offre_id` int NOT NULL,
  `sexe` enum('homme','femme') NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `telephone` varchar(20) NOT NULL,
  `cv_path` varchar(255) NOT NULL,
  `lettre_option` enum('upload','redigee') NOT NULL,
  `lettre_path` varchar(255) DEFAULT NULL,
  `lettre_redigee` text,
  `date_soumission` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `score_ats` decimal(5,2) DEFAULT '0.00',
  `analyse_ia` text,
  `statut` enum('nouveau','evalue','shortlist','rejete') DEFAULT 'nouveau',
  PRIMARY KEY (`id`),
  KEY `fk_offre_candidature` (`offre_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprises`
--

DROP TABLE IF EXISTS `entreprises`;
CREATE TABLE IF NOT EXISTS `entreprises` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `logo_url` varchar(255) DEFAULT NULL,
  `site_web` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `entreprises`
--

INSERT INTO `entreprises` (`id`, `nom`, `logo_url`, `site_web`, `description`) VALUES
(1, 'Capgemini', 'https://logo.clearbit.com/capgemini.com', 'https://www.capgemini.com', 'Leader mondial du conseil, des services informatiques et de la transformation numérique.'),
(2, 'Airbus', 'https://logo.clearbit.com/airbus.com', 'https://www.airbus.com', 'Pionnier de l\'industrie aéronautique et spatiale.'),
(3, 'L\'Oréal', 'https://logo.clearbit.com/loreal.com', 'https://www.loreal.com', 'Leader mondial de la beauté et des cosmétiques.'),
(4, 'Sanofi', 'https://logo.clearbit.com/sanofi.com', 'https://www.sanofi.fr', 'Entreprise mondiale de santé spécialisée dans les vaccins et les médicaments.'),
(5, 'Decathlon', 'https://logo.clearbit.com/decathlon.fr', 'https://www.decathlon.fr', 'Concepteur et distributeur d\'articles de sport.'),
(6, 'BNP Paribas', 'https://logo.clearbit.com/bnpparibas.com', 'https://group.bnpparibas', 'Première banque de l\'Union européenne.'),
(7, 'EDF', 'https://logo.clearbit.com/edf.fr', 'https://www.edf.fr', 'Premier producteur et fournisseur d\'électricité en France.'),
(8, 'Thales', 'https://logo.clearbit.com/thalesgroup.com', 'https://www.thalesgroup.com', 'Leader en cybersécurité, défense et technologies spatiales.'),
(9, 'Renault Group', 'https://logo.clearbit.com/renaultgroup.com', 'https://www.renaultgroup.com', 'Constructeur automobile français emblématique.'),
(10, 'Hermès', 'https://logo.clearbit.com/hermes.com', 'https://www.hermes.com', 'Maison de luxe française spécialisée dans la maroquinerie et la mode.');

-- --------------------------------------------------------

--
-- Structure de la table `metiers`
--

DROP TABLE IF EXISTS `metiers`;
CREATE TABLE IF NOT EXISTS `metiers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom_metier` varchar(100) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `metiers`
--

INSERT INTO `metiers` (`id`, `nom_metier`) VALUES
(1, 'Développement Web'),
(2, 'Cybersécurité'),
(3, 'Data Analysis'),
(4, 'Marketing Digital'),
(5, 'Ressources Humaines'),
(6, 'Logistique & Supply Chain'),
(7, 'Ingénierie Mécanique'),
(8, 'Finance & Audit'),
(9, 'Communication'),
(10, 'Vente & Commerce'),
(11, 'Cloud Computing'),
(12, 'Intelligence Artificielle'),
(13, 'Juridique'),
(14, 'Design UX/UI'),
(15, 'Maintenance Industrielle'),
(16, 'Gestion de Projet'),
(17, 'Transition Énergétique'),
(18, 'Recherche & Développement'),
(19, 'Qualité (QA)'),
(20, 'Systèmes & Réseaux');

-- --------------------------------------------------------

--
-- Structure de la table `offres`
--

DROP TABLE IF EXISTS `offres`;
CREATE TABLE IF NOT EXISTS `offres` (
  `id` int NOT NULL AUTO_INCREMENT,
  `admin_id` int NOT NULL,
  `entreprise_id` int NOT NULL,
  `metier_id` int DEFAULT NULL,
  `titre` varchar(150) NOT NULL,
  `lieu` varchar(100) NOT NULL,
  `date_publication` date NOT NULL,
  `type_contrat` enum('Stage','Alternance') NOT NULL,
  `duree` varchar(100) DEFAULT NULL,
  `temps_travail` varchar(50) DEFAULT 'Temps plein',
  `teletravail_possible` tinyint(1) DEFAULT '0',
  `teletravail_frequence` varchar(100) DEFAULT NULL,
  `langues` varchar(255) DEFAULT NULL,
  `experience` varchar(100) DEFAULT NULL,
  `niveau_etude` text,
  `missions` text,
  `profil_recherche` text,
  `competences_cles` text,
  `is_active` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `fk_offre_entreprise` (`entreprise_id`),
  KEY `fk_offre_metier` (`metier_id`),
  KEY `fk_offre_admin` (`admin_id`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `offres`
--

INSERT INTO `offres` (`id`, `admin_id`, `entreprise_id`, `metier_id`, `titre`, `lieu`, `date_publication`, `type_contrat`, `duree`, `temps_travail`, `teletravail_possible`, `teletravail_frequence`, `langues`, `experience`, `niveau_etude`, `missions`, `profil_recherche`, `competences_cles`, `is_active`) VALUES
(21, 1, 1, 2, 'Analyste SOC L1', 'Nancy', '2026-04-01', 'Alternance', '3 ans', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(22, 1, 3, 5, 'Ingénieur Cloud AWS', 'Paris', '2026-04-02', 'Stage', '6 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(23, 1, 2, 2, 'Consultant GRC', 'Lyon', '2026-04-03', 'Alternance', '2 ans', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+4', NULL, NULL, NULL, 1),
(24, 1, 4, 5, 'Administrateur Réseaux', 'Metz', '2026-04-04', 'Stage', '4 mois', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+2', NULL, NULL, NULL, 1),
(25, 1, 1, 2, 'Pentester Junior', 'Lille', '2026-04-05', 'Alternance', '1 an', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(26, 1, 5, 5, 'Technicien Support VIP', 'Paris', '2026-04-06', 'Stage', '6 mois', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+2', NULL, NULL, NULL, 1),
(27, 1, 3, 2, 'Architecte Sécurité', 'Strasbourg', '2026-04-07', 'Alternance', '3 ans', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(28, 1, 2, 1, 'DevOps Engineer', 'Nantes', '2026-04-08', 'Stage', '6 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+4', NULL, NULL, NULL, 1),
(29, 1, 1, 2, 'Analyste Forensics', 'Nancy', '2026-04-09', 'Alternance', '2 ans', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(30, 1, 4, 5, 'Admin Système Linux', 'Toulouse', '2026-04-10', 'Stage', '3 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(31, 1, 2, 2, 'Responsable SSI', 'Bordeaux', '2026-04-11', 'Alternance', '1 an', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(32, 1, 3, 5, 'Ingénieur Virtualisation', 'Lyon', '2026-04-12', 'Stage', '6 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+4', NULL, NULL, NULL, 1),
(33, 1, 5, 2, 'Consultant Cybersécurité', 'Paris', '2026-04-13', 'Alternance', '3 ans', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(34, 1, 1, 5, 'Gestionnaire de Parc IT', 'Nancy', '2026-04-14', 'Stage', '2 mois', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+2', NULL, NULL, NULL, 1),
(35, 1, 4, 5, 'Ingénieur Réseaux Cisco', 'Nice', '2026-04-15', 'Alternance', '2 ans', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+4', NULL, NULL, NULL, 1),
(36, 1, 2, 2, 'Data Protection Officer', 'Rennes', '2026-04-16', 'Stage', '6 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(37, 2, 2, 3, 'Chargé de Recrutement IT', 'Paris', '2026-04-01', 'Stage', '6 mois', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(38, 2, 1, 3, 'Assistant RH Groupe', 'Lyon', '2026-04-02', 'Alternance', '2 ans', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+2', NULL, NULL, NULL, 1),
(39, 2, 3, 4, 'Community Manager', 'Nancy', '2026-04-03', 'Stage', '4 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(40, 2, 5, 4, 'Chef de Projet Marketing', 'Bordeaux', '2026-04-04', 'Alternance', '1 an', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(41, 2, 4, 3, 'Talent Acquisition', 'Lille', '2026-04-05', 'Stage', '6 mois', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+4', NULL, NULL, NULL, 1),
(42, 2, 2, 4, 'Responsable Marque Employeur', 'Marseille', '2026-04-06', 'Alternance', '2 ans', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(43, 2, 1, 3, 'Chargé de Formation', 'Nantes', '2026-04-07', 'Stage', '3 mois', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(44, 2, 3, 4, 'Social Media Specialist', 'Paris', '2026-04-08', 'Alternance', '1 an', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(45, 2, 5, 3, 'Gestionnaire de Paie', 'Nancy', '2026-04-09', 'Stage', '6 mois', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+2', NULL, NULL, NULL, 1),
(46, 2, 2, 4, 'Copywriter SEO', 'Lyon', '2026-04-10', 'Alternance', '2 ans', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+4', NULL, NULL, NULL, 1),
(47, 2, 4, 3, 'Assistant Recrutement', 'Strasbourg', '2026-04-11', 'Stage', '4 mois', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(48, 2, 1, 4, 'Analyste Marketing Data', 'Toulouse', '2026-04-12', 'Alternance', '1 an', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(49, 2, 3, 4, 'Chargé de Communication', 'Rennes', '2026-04-13', 'Stage', '5 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(50, 2, 5, 3, 'Office Manager', 'Montpellier', '2026-04-14', 'Alternance', '2 ans', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+2', NULL, NULL, NULL, 1),
(51, 2, 2, 4, 'Digital Planner', 'Nice', '2026-04-15', 'Stage', '6 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+4', NULL, NULL, NULL, 1),
(52, 2, 4, 3, 'Consultant RH', 'Paris', '2026-04-16', 'Alternance', '1 an', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(53, 2, 1, 4, 'Growth Hacker', 'Bordeaux', '2026-04-17', 'Stage', '3 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(54, 3, 3, 1, 'Développeur React Native', 'Nancy', '2026-04-18', 'Stage', '6 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+4', NULL, NULL, NULL, 1),
(55, 3, 1, 4, 'Ingénieur IA (LLM)', 'Paris', '2026-04-19', 'Alternance', '3 ans', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(56, 3, 2, 1, 'Développeur Backend FastAPI', 'Lyon', '2026-04-20', 'Stage', '6 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(57, 3, 5, 4, 'Data Scientist Junior', 'Nantes', '2026-04-21', 'Alternance', '1 an', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(58, 3, 4, 1, 'Développeur Fullstack PHP', 'Lille', '2026-04-22', 'Stage', '4 mois', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(59, 3, 1, 4, 'Ingénieur ML Ops', 'Toulouse', '2026-04-23', 'Alternance', '2 ans', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(60, 3, 3, 1, 'Développeur Mobile Flutter', 'Nancy', '2026-04-24', 'Stage', '6 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+4', NULL, NULL, NULL, 1),
(61, 3, 2, 1, 'Architecte Logiciel', 'Paris', '2026-04-25', 'Alternance', '1 an', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(62, 3, 5, 1, 'Développeur Frontend Vue.js', 'Bordeaux', '2026-04-26', 'Stage', '6 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(63, 3, 1, 4, 'Ingénieur Big Data', 'Rennes', '2026-04-27', 'Alternance', '2 ans', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(64, 3, 3, 1, 'Développeur Python/Django', 'Lyon', '2026-04-27', 'Stage', '4 mois', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(65, 3, 4, 4, 'Data Analyst', 'Marseille', '2026-04-28', 'Alternance', '1 an', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+4', NULL, NULL, NULL, 1),
(66, 3, 2, 1, 'Développeur Java Spring', 'Nancy', '2026-04-28', 'Stage', '6 mois', 'Temps plein', 0, NULL, NULL, NULL, 'Bac+4', NULL, NULL, NULL, 1),
(67, 3, 5, 4, 'Ingénieur NLP', 'Paris', '2026-04-28', 'Alternance', '2 ans', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(68, 3, 1, 1, 'Développeur Android', 'Lille', '2026-04-28', 'Stage', '6 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(69, 3, 3, 4, 'Data Architect', 'Paris', '2026-04-28', 'Alternance', '3 ans', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+5', NULL, NULL, NULL, 1),
(70, 3, 1, 1, 'Développeur Web Laravel', 'Nancy', '2026-04-28', 'Stage', '4 mois', 'Temps plein', 1, NULL, NULL, NULL, 'Bac+3', NULL, NULL, NULL, 1),
(71, 1, 1, 2, 'Assistant Rssi', 'nancy', '2026-04-29', 'Stage', NULL, 'Temps plein', 0, NULL, 'Anglais b2', 'débutant', 'bac+2/bac+3', 'Participer à la sécurisation de l’informatique interne : postes de travail, infrastructure, gestion des accès, charte Admin,\r\nEtude et mise en place d’outils de cybersécurité,\r\nParticiper à la sécurisation de nos infrastructures Cloud : gestion des accès, segmentation réseau (micro et macro), charte Admin,\r\nAider à la réalisation des projets d’infrastructures clients,\r\nSensibiliser et accompagner les utilisateurs à la cybersécurité.', 'Vous êtes en formation en bac+2 informatique et vous êtes à la recherche d’une alternance pour deux a trois ans à partir de septembre 2026,\r\nIdéalement vous avez déjà une expérience stage / alternance en entreprise,\r\nVous avez un goût prononcé pour l’informatique et vous avez le sens du service,\r\nVous avez envie de travailler en équipe !', 'Active Directory, IAM, GPO, Firewall, VPN, Segmentation, AWS, Azure, ISO 27001, RGPD, SIEM, Vulnerabilité, Rédaction, Sensibilisation, Micro-segmentation, Cloud, Linux, Windows,python, ccna, google cybersécurité, wiresharck, cisco,', 1);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `candidatures`
--
ALTER TABLE `candidatures`
  ADD CONSTRAINT `fk_offre_candidature` FOREIGN KEY (`offre_id`) REFERENCES `offres` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `offres`
--
ALTER TABLE `offres`
  ADD CONSTRAINT `fk_offre_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_offre_entreprise` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_offre_metier` FOREIGN KEY (`metier_id`) REFERENCES `metiers` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
