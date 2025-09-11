-- Script d'initialisation de la base de données
USE parrainage_db;

-- Table pour les inscriptions
CREATE TABLE IF NOT EXISTS inscriptions (
                                            id INT AUTO_INCREMENT PRIMARY KEY,
                                            nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    classe ENUM('BUT1INFO', 'BUT2INFO', 'BUT3INFO','BUT1GEA', 'BUT2GEA', 'BUT3GEA','BTS1AC', 'BTS2AC') NOT NULL,
    motivation TEXT,
    discord TEXT,
    insta TEXT,
    preference ENUM('PARRAIN','FILLEUL'),
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_classe (classe),
    INDEX idx_date (date_inscription)
    );

-- Table pour les administrateurs (simple pour l'authentification)
CREATE TABLE IF NOT EXISTS admins (
                                      id INT AUTO_INCREMENT PRIMARY KEY,
                                      username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

-- Insertion d'un administrateur par défaut
-- Nom d'utilisateur: admin
-- Mot de passe: admin123
INSERT INTO admins (username, password_hash) VALUES
    ('admin', '$2y$10$PyabEnUghoXC1MRweQZ7R./mvsxUEabAX7zzlfz2kf.NfI2HJ2fGC');
-- Insertion de quelques données de test
INSERT INTO inscriptions (nom, prenom, email, classe, motivation) VALUES
                                                                      ('Dupont', 'Jean', 'jean.dupont@example.com', 'BUT1INFO', 'Je souhaite être parrainé pour réussir ma première année.'),
                                                                      ('Martin', 'Sophie', 'sophie.martin@example.com', 'BUT2INFO', 'J\'aimerais devenir marraine pour aider les nouveaux étudiants.'),
('Durand', 'Pierre', 'pierre.durand@example.com', 'BUT3INFO', 'En tant qu\'étudiant de BUT3, je veux partager mon expérience.');