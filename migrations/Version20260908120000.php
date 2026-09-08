<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Schéma initial : tables inscriptions + admins, avec les données de démonstration
 * (reprises de l'ancien database/init.sql).
 */
final class Version20260908120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables inscriptions et admins + données de démo';
    }

    public function isTransactional(): bool
    {
        // MySQL applique un commit implicite sur le DDL : pas de transaction englobante.
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE inscriptions (
                id INT AUTO_INCREMENT NOT NULL,
                nom VARCHAR(100) NOT NULL,
                prenom VARCHAR(100) NOT NULL,
                email VARCHAR(255) NOT NULL,
                classe VARCHAR(255) NOT NULL,
                motivation LONGTEXT DEFAULT NULL,
                discord VARCHAR(255) DEFAULT NULL,
                insta VARCHAR(255) DEFAULT NULL,
                preference VARCHAR(255) DEFAULT NULL,
                date_inscription DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                UNIQUE INDEX uniq_inscription_email (email),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $this->addSql(<<<'SQL'
            CREATE TABLE admins (
                id INT AUTO_INCREMENT NOT NULL,
                username VARCHAR(50) NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                UNIQUE INDEX uniq_admin_username (username),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        // Compte admin par défaut — identifiants : admin / admin123
        $this->addSql(<<<'SQL'
            INSERT INTO admins (username, password_hash, created_at) VALUES
            ('admin', '$2y$10$PyabEnUghoXC1MRweQZ7R./mvsxUEabAX7zzlfz2kf.NfI2HJ2fGC', NOW())
        SQL);

        $this->addSql(<<<'SQL'
            INSERT INTO inscriptions (nom, prenom, email, classe, motivation, preference, date_inscription) VALUES
            ('Dupont', 'Jean', 'jean.dupont@example.com', 'BUT1INFO', 'Je souhaite être parrainé pour réussir ma première année.', 'FILLEUL', NOW()),
            ('Martin', 'Sophie', 'sophie.martin@example.com', 'BUT2INFO', 'J''aimerais devenir marraine pour aider les nouveaux étudiants.', 'PARRAIN', NOW()),
            ('Durand', 'Pierre', 'pierre.durand@example.com', 'BUT3INFO', 'En tant qu''étudiant de BUT3, je veux partager mon expérience.', 'PARRAIN', NOW())
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE inscriptions');
        $this->addSql('DROP TABLE admins');
    }
}
