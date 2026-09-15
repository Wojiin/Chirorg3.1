<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260915150005 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajoute le référentiel administrable des salles et reprend les valeurs déjà utilisées.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE salle (id INT AUTO_INCREMENT NOT NULL, intitule VARCHAR(50) NOT NULL, UNIQUE INDEX uniq_salle_intitule (intitule), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql("INSERT IGNORE INTO salle (intitule) SELECT DISTINCT salle FROM chirurgie_planifiee WHERE TRIM(salle) <> ''");
        $this->addSql("INSERT IGNORE INTO salle (intitule) VALUES ('Salle A'), ('Salle B'), ('Salle C')");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE salle');
    }
}
