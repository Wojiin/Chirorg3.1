<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260903144721 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the liste_materiel reference tables and their relations.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE liste_materiel (id INT AUTO_INCREMENT NOT NULL, intitule VARCHAR(150) NOT NULL, chirurgien_id INT NOT NULL, chirurgie_modele_id INT NOT NULL, INDEX IDX_40FEB6B6DB64F5D (chirurgien_id), INDEX IDX_40FEB6B73FD2B44 (chirurgie_modele_id), UNIQUE INDEX uniq_liste_chirurgien_modele (chirurgien_id, chirurgie_modele_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE liste_materiel_materiel (liste_materiel_id INT NOT NULL, materiel_id INT NOT NULL, INDEX IDX_5B47E140FF6D643 (liste_materiel_id), INDEX IDX_5B47E14016880AAF (materiel_id), PRIMARY KEY (liste_materiel_id, materiel_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE liste_materiel ADD CONSTRAINT FK_40FEB6B6DB64F5D FOREIGN KEY (chirurgien_id) REFERENCES chirurgien (id)');
        $this->addSql('ALTER TABLE liste_materiel ADD CONSTRAINT FK_40FEB6B73FD2B44 FOREIGN KEY (chirurgie_modele_id) REFERENCES chirurgie_modele (id)');
        $this->addSql('ALTER TABLE liste_materiel_materiel ADD CONSTRAINT FK_5B47E140FF6D643 FOREIGN KEY (liste_materiel_id) REFERENCES liste_materiel (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE liste_materiel_materiel ADD CONSTRAINT FK_5B47E14016880AAF FOREIGN KEY (materiel_id) REFERENCES materiel (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE liste_materiel DROP FOREIGN KEY FK_40FEB6B6DB64F5D');
        $this->addSql('ALTER TABLE liste_materiel DROP FOREIGN KEY FK_40FEB6B73FD2B44');
        $this->addSql('ALTER TABLE liste_materiel_materiel DROP FOREIGN KEY FK_5B47E140FF6D643');
        $this->addSql('ALTER TABLE liste_materiel_materiel DROP FOREIGN KEY FK_5B47E14016880AAF');
        $this->addSql('DROP TABLE liste_materiel');
        $this->addSql('DROP TABLE liste_materiel_materiel');
    }
}
