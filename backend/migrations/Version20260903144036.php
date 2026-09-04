<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260903144036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the chirurgie_modele reference table and its specialite relation.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chirurgie_modele (id INT AUTO_INCREMENT NOT NULL, intitule VARCHAR(150) NOT NULL, specialite_id INT NOT NULL, INDEX IDX_BCAC64B82195E0F0 (specialite_id), UNIQUE INDEX uniq_chirurgie_modele_intitule_specialite (intitule, specialite_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE chirurgie_modele ADD CONSTRAINT FK_BCAC64B82195E0F0 FOREIGN KEY (specialite_id) REFERENCES specialite (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chirurgie_modele DROP FOREIGN KEY FK_BCAC64B82195E0F0');
        $this->addSql('DROP TABLE chirurgie_modele');
    }
}
