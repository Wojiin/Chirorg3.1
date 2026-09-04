<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260903143844 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the materiel reference table and its specialite relation.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE materiel (id INT AUTO_INCREMENT NOT NULL, intitule VARCHAR(150) NOT NULL, adresse VARCHAR(150) DEFAULT NULL, type_materiel VARCHAR(100) DEFAULT NULL, specialite_id INT NOT NULL, INDEX IDX_18D2B0912195E0F0 (specialite_id), INDEX idx_materiel_intitule (intitule), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE materiel ADD CONSTRAINT FK_18D2B0912195E0F0 FOREIGN KEY (specialite_id) REFERENCES specialite (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE materiel DROP FOREIGN KEY FK_18D2B0912195E0F0');
        $this->addSql('DROP TABLE materiel');
    }
}
