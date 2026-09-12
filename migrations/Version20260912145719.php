<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Ajoute les rôles complémentaires sur les comptes admin et fait du compte
 * de démo "admin" le premier super-admin (seul habilité à créer des comptes).
 */
final class Version20260912145719 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de admins.roles + ROLE_SUPER_ADMIN sur le compte "admin"';
    }

    public function isTransactional(): bool
    {
        return false;
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE admins ADD roles JSON DEFAULT NULL');
        $this->addSql("UPDATE admins SET roles = JSON_ARRAY('ROLE_SUPER_ADMIN') WHERE username = 'admin'");
        $this->addSql('UPDATE admins SET roles = JSON_ARRAY() WHERE roles IS NULL');
        $this->addSql('ALTER TABLE admins MODIFY roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE admins DROP roles');
    }
}
