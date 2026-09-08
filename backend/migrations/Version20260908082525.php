<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260908082525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute les chirurgies planifiées et leur préparation de matériel.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chirurgie_planifiee (id INT AUTO_INCREMENT NOT NULL, date_programmee DATE NOT NULL, salle VARCHAR(50) NOT NULL, ordre INT DEFAULT NULL, valide TINYINT DEFAULT 0 NOT NULL, valide_le DATETIME DEFAULT NULL, cree_le DATETIME NOT NULL, modifie_le DATETIME NOT NULL, cree_par VARCHAR(180) DEFAULT NULL, modifie_par VARCHAR(180) DEFAULT NULL, chirurgien_id INT NOT NULL, chirurgie_modele_id INT NOT NULL, valide_par_id INT DEFAULT NULL, INDEX IDX_B90390C36DB64F5D (chirurgien_id), INDEX IDX_B90390C373FD2B44 (chirurgie_modele_id), INDEX IDX_B90390C36AF12ED9 (valide_par_id), INDEX idx_chirurgie_date (date_programmee), INDEX idx_chirurgie_programme (date_programmee, salle, chirurgien_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE preparation_materiel (id INT AUTO_INCREMENT NOT NULL, coche TINYINT DEFAULT 0 NOT NULL, absent TINYINT DEFAULT 0 NOT NULL, coche_le DATETIME DEFAULT NULL, chirurgie_planifiee_id INT NOT NULL, materiel_id INT NOT NULL, coche_par_id INT DEFAULT NULL, INDEX IDX_9066EE2B1DE03630 (chirurgie_planifiee_id), INDEX IDX_9066EE2B16880AAF (materiel_id), INDEX IDX_9066EE2B808E962E (coche_par_id), UNIQUE INDEX uniq_preparation_chirurgie_materiel (chirurgie_planifiee_id, materiel_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE chirurgie_planifiee ADD CONSTRAINT FK_B90390C36DB64F5D FOREIGN KEY (chirurgien_id) REFERENCES chirurgien (id)');
        $this->addSql('ALTER TABLE chirurgie_planifiee ADD CONSTRAINT FK_B90390C373FD2B44 FOREIGN KEY (chirurgie_modele_id) REFERENCES chirurgie_modele (id)');
        $this->addSql('ALTER TABLE chirurgie_planifiee ADD CONSTRAINT FK_B90390C36AF12ED9 FOREIGN KEY (valide_par_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE preparation_materiel ADD CONSTRAINT FK_9066EE2B1DE03630 FOREIGN KEY (chirurgie_planifiee_id) REFERENCES chirurgie_planifiee (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE preparation_materiel ADD CONSTRAINT FK_9066EE2B16880AAF FOREIGN KEY (materiel_id) REFERENCES materiel (id)');
        $this->addSql('ALTER TABLE preparation_materiel ADD CONSTRAINT FK_9066EE2B808E962E FOREIGN KEY (coche_par_id) REFERENCES utilisateur (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE chirurgie_planifiee DROP FOREIGN KEY FK_B90390C36DB64F5D');
        $this->addSql('ALTER TABLE chirurgie_planifiee DROP FOREIGN KEY FK_B90390C373FD2B44');
        $this->addSql('ALTER TABLE chirurgie_planifiee DROP FOREIGN KEY FK_B90390C36AF12ED9');
        $this->addSql('ALTER TABLE preparation_materiel DROP FOREIGN KEY FK_9066EE2B1DE03630');
        $this->addSql('ALTER TABLE preparation_materiel DROP FOREIGN KEY FK_9066EE2B16880AAF');
        $this->addSql('ALTER TABLE preparation_materiel DROP FOREIGN KEY FK_9066EE2B808E962E');
        $this->addSql('DROP TABLE chirurgie_planifiee');
        $this->addSql('DROP TABLE preparation_materiel');
    }
}
