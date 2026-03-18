-- Création de la base de données
CREATE DATABASE IF NOT EXISTS secel_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE secel_db;

-- Table des spécialités (pour les techniciens)
CREATE TABLE IF NOT EXISTS specialites (
    id_specialite INT AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- Table unique des utilisateurs (Admin, Tech, Client)
CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    login VARCHAR(50) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    role ENUM('admin', 'technicien', 'client') NOT NULL,
    id_specialite INT,
    disponible BOOLEAN DEFAULT TRUE,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_specialite) REFERENCES specialites(id_specialite) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Table des besoins (émis par les clients)
CREATE TABLE IF NOT EXISTS besoins (
    id_besoin INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    description TEXT NOT NULL,
    date_besoin DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des interventions
CREATE TABLE IF NOT EXISTS interventions (
    id_intervention INT AUTO_INCREMENT PRIMARY KEY,
    id_client INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    statut ENUM('attente', 'en_cours', 'termine', 'annule') DEFAULT 'attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des affectations (Ligne réalisation)
CREATE TABLE IF NOT EXISTS affectations (
    id_affectation INT AUTO_INCREMENT PRIMARY KEY,
    id_technicien INT NOT NULL,
    id_intervention INT NOT NULL,
    date_debut DATETIME,
    date_fin DATETIME,
    FOREIGN KEY (id_technicien) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE,
    FOREIGN KEY (id_intervention) REFERENCES interventions(id_intervention) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des rapports
CREATE TABLE IF NOT EXISTS rapports (
    id_rapport INT AUTO_INCREMENT PRIMARY KEY,
    id_intervention INT NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    date_rapport DATETIME DEFAULT CURRENT_TIMESTAMP,
    contenu TEXT NOT NULL,
    FOREIGN KEY (id_intervention) REFERENCES interventions(id_intervention) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des factures
CREATE TABLE IF NOT EXISTS factures (
    id_facture INT AUTO_INCREMENT PRIMARY KEY,
    id_intervention INT NOT NULL,
    libelle VARCHAR(200) NOT NULL,
    montant DECIMAL(10, 2) NOT NULL,
    statut ENUM('non_paye', 'paye', 'annule') DEFAULT 'non_paye',
    date_facture DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_intervention) REFERENCES interventions(id_intervention) ON DELETE CASCADE
) ENGINE=InnoDB;
