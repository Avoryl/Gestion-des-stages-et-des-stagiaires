-- Suppression et recréation de la base de données
DROP DATABASE IF EXISTS `stagiaire`;
CREATE DATABASE `stagiaire` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `stagiaire`;

-- 1. Table des départements
CREATE TABLE `departements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `sigle` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. Table des offres de stages
CREATE TABLE `stages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `duree` varchar(50) NOT NULL,
  `departement_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_stage_dept` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Table des gestionnaires 
CREATE TABLE `gestionnaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `mot_de_passe` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table admins vide (pour éviter les erreurs dans login.php)
CREATE TABLE `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL UNIQUE,
  `mot_de_passe` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Table des stagiaires (Structure complète sans doublons)
CREATE TABLE `stagiaires` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `date_naissance` date DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `formation` varchar(150) DEFAULT NULL,
  `specialite` varchar(150) DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `cv` varchar(255) DEFAULT NULL,
  `lm` varchar(255) DEFAULT NULL,
  `attestation_diplome` varchar(255) DEFAULT NULL,
  `stage_id` int(11) DEFAULT NULL,
  `duree_choisie` varchar(50) DEFAULT NULL,
  `type_stage` enum('professionnel','academique') NOT NULL,
  `modalite` enum('solo','binome') DEFAULT 'solo',
  `ecole_nom` varchar(255) DEFAULT NULL,
  `ecole_directeur` varchar(255) DEFAULT NULL,
  `ecole_adresse` varchar(255) DEFAULT NULL,
  `ecole_telephone` varchar(50) DEFAULT NULL,
  `ecole_email` varchar(150) DEFAULT NULL,
  `motif` text DEFAULT NULL,
  `binome_nom` varchar(100) DEFAULT NULL,
  `binome_prenom` varchar(100) DEFAULT NULL,
  `binome_email` varchar(150) DEFAULT NULL,
  `binome_date_naissance` date DEFAULT NULL,
  `binome_telephone` varchar(20) DEFAULT NULL,
  `binome_adresse` varchar(255) DEFAULT NULL,
  `binome_formation` varchar(150) DEFAULT NULL,
  `binome_specialite` varchar(150) DEFAULT NULL,
  `binome_cv` varchar(255) DEFAULT NULL,
  `binome_lm` varchar(255) DEFAULT NULL,
  `status` enum('en_attente','valide','refuse','termine') DEFAULT 'en_attente',
  `date_validation` datetime DEFAULT NULL,
  `date_debut` date DEFAULT NULL,
  `date_fin` date DEFAULT NULL,
  `date_refus` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_stagiaire_stage` FOREIGN KEY (`stage_id`) REFERENCES `stages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --- INSERTION DES DONNEES ---

-- Départements
INSERT INTO `departements` (`nom`, `sigle`) VALUES 
('Direction de la Planification, de l\'Administration et des Finances', 'DPAF'),
('Direction des Systèmes d\'Information', 'DSI'),
('Ressources Humaines', 'RH');

-- Un stage par département
INSERT INTO `stages` (`titre`, `description`, `duree`, `departement_id`) VALUES
('Support Technique IT', 'Maintenance et assistance utilisateurs.', '3 mois', 2),
('Gestion Administrative', 'Aide à la gestion des dossiers du personnel.', '2 mois', 3),
('Comptabilité Publique', 'Suivi des budgets et engagements.', '3 mois', 1);

-- Gestionnaire unique (Pass: admin123)
INSERT INTO `gestionnaires` (`nom`, `prenom`, `email`, `mot_de_passe`) VALUES
('Principal', 'Gestionnaire', 'gestionnaire@stage.com', '$2y$10$BSNVz./CmBnPZMyS2uErheT7kKjG456UXVGzNEC7EIpD.0F99GNt.');

-- 7 Stagiaires (3 attente, 2 validés, 2 refusés)
INSERT INTO `stagiaires` (`nom`, `prenom`, `email`, `type_stage`, `stage_id`, `status`, `modalite`, `binome_nom`, `binome_prenom`, `binome_email`, `date_validation`, `date_debut`, `date_fin`, `date_refus`, `formation`, `specialite`, `duree_choisie`, `motif`) VALUES
-- En attente
('DUVAL', 'Marc', 'marc.duval@email.com', 'academique', 1, 'en_attente', 'solo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Université X', 'Informatique', '3 mois', 'Mémoire'),
('MARTIN', 'Sophie', 'sophie.m@email.com', 'professionnel', 2, 'en_attente', 'solo', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Institut Y', 'RH', '2 mois', 'Renforcement'),
('BENOIT', 'Claire', 'claire.benoit@email.com', 'academique', 1, 'en_attente', 'binome', 'GIRARD', 'Thomas', 'thomas.girard@email.com', NULL, NULL, NULL, NULL, 'Université Lyon', 'Informatique', '3 mois', 'Mémoire'),
-- Acceptés (Validés)
('KOFFI', 'Jean', 'jean.k@email.com', 'academique', 1, 'valide', 'solo', NULL, NULL, NULL, NOW(), '2024-05-01', '2024-07-31', NULL, 'ESGIS', 'Réseaux', '3 mois', 'Rapport'),
('SOGLO', 'Carine', 'carine.s@email.com', 'professionnel', 3, 'valide', 'solo', NULL, NULL, NULL, NOW(), '2024-05-15', '2024-08-15', NULL, 'ENAM', 'Finances', '3 mois', 'Renforcement'),
-- Refusés
('TOSSOU', 'Paul', 'paul.t@email.com', 'academique', 2, 'refuse', 'solo', NULL, NULL, NULL, NULL, NULL, NULL, NOW(), 'Lycée Z', 'Secretariat', '2 mois', 'Stage pratique'),
('AGOSSOU', 'Marie', 'marie.a@email.com', 'professionnel', 3, 'refuse', 'solo', NULL, NULL, NULL, NULL, NULL, NULL, NOW(), 'Faseg', 'Audit', '3 mois', 'Recherche');