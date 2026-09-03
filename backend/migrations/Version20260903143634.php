<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260903143634 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the chirurgien reference table and its specialite relation.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chirurgien (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, specialite_id INT NOT NULL, INDEX IDX_1384D5E2195E0F0 (specialite_id), INDEX idx_chirurgien_nom (nom), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE chirurgien ADD CONSTRAINT FK_1384D5E2195E0F0 FOREIGN KEY (specialite_id) REFERENCES specialite (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chirurgien DROP FOREIGN KEY FK_1384D5E2195E0F0');
        $this->addSql('DROP TABLE chirurgien');
    }
}
