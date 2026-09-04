<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260903144323 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the fiche_technique reference table and its chirurgie_modele relation.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fiche_technique (id INT AUTO_INCREMENT NOT NULL, titre VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, lien_image VARCHAR(255) DEFAULT NULL, ordre INT NOT NULL, chirurgie_modele_id INT NOT NULL, INDEX IDX_505525A973FD2B44 (chirurgie_modele_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE fiche_technique ADD CONSTRAINT FK_505525A973FD2B44 FOREIGN KEY (chirurgie_modele_id) REFERENCES chirurgie_modele (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fiche_technique DROP FOREIGN KEY FK_505525A973FD2B44');
        $this->addSql('DROP TABLE fiche_technique');
    }
}
